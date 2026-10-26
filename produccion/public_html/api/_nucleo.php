<?php
/**
 * Núcleo de la plataforma (PHP + MySQL), multi-agencia: ver _tenant.php.
 *
 * Guarda el contenido como documentos JSON en una sola tabla (`avm_docs`), con rutas
 * site/content, destinations/<id>, leads/<visitante>, prospects/<clave>…, siempre dentro de una agencia.
 *
 * Configuración: avm-config.php UN NIVEL POR ENCIMA de public_html (nunca dentro).
 */

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === '_nucleo.php') { http_response_code(404); exit; }

date_default_timezone_set('America/Bogota');
require_once __DIR__ . '/_tenant.php';
require_once __DIR__ . '/_correo.php';
require_once __DIR__ . '/_campanas.php';
require_once __DIR__ . '/_liteapi.php';
require_once __DIR__ . '/_propios.php';

const AVM_ADMIN_COLLS = ['leads', 'prospects', 'lists', 'automations', 'templates', 'outbox', 'campaigns', 'firmas', 'system', 'liquidaciones', 'productos', 'tarifarios'];
const AVM_SEG = '/^[A-Za-z0-9_\-.~:@+]{1,200}$/';

function avm_config(): array {
    static $cfg = null;
    if ($cfg !== null) return $cfg;
    $path = dirname(__DIR__, 2) . '/avm-config.php';
    if (!is_file($path)) { avm_fail(503, 'sin_configurar'); }
    $cfg = require $path;
    return $cfg;
}

function avm_db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $c = avm_config();
    $pdo = new PDO("mysql:host={$c['db_host']};dbname={$c['db_name']};charset=utf8mb4", $c['db_user'], $c['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function avm_json($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function avm_fail(int $status, string $code): void { avm_json(['error' => $code], $status); }

function avm_now_ms(): int { return (int) floor(microtime(true) * 1000); }
function avm_iso(): string { return gmdate('Y-m-d\TH:i:s.v\Z'); }

/* ---------- Sesión de administrador y visitante ---------- */
function avm_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_name('avm_s');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => avm_https(), 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
function avm_https(): bool { return (($_SERVER['HTTPS'] ?? '') !== '' && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'); }
function avm_is_admin(): bool {
    avm_session();
    $t = avm_tenant_opt();
    return !empty($_SESSION['admin_id']) && $t && (int) ($_SESSION['admin_tenant'] ?? -1) === $t['id'];
}
/** Dueño de la plataforma (panel plataforma.php). */
function avm_is_super(): bool {
    avm_session();
    if (empty($_SESSION['super_id'])) return false;
    static $ok = null;
    if ($ok === null) { $st = avm_db()->prepare("SELECT COUNT(*) FROM avm_users WHERE id = ? AND tenant = 0 AND role = 'super'"); $st->execute([$_SESSION['super_id']]); $ok = (bool) $st->fetchColumn(); }
    return $ok;
}

/** Identificador anónimo del visitante (cookie). Solo sirve para que vea y agregue sus propias solicitudes. */
function avm_visitor(): string {
    $v = $_COOKIE['avm_v'] ?? '';
    if (!preg_match('/^[a-f0-9]{32}$/', $v)) {
        $v = bin2hex(random_bytes(16));
        setcookie('avm_v', $v, ['expires' => time() + 31536000, 'path' => '/', 'secure' => avm_https(), 'httponly' => true, 'samesite' => 'Lax']);
    }
    return 'v' . $v;
}

/** Las escrituras deben venir del propio sitio (cabecera propia + mismo origen). */
function avm_check_origin(): void {
    if (($_SERVER['HTTP_X_AVM'] ?? '') !== '1') avm_fail(403, 'origen');
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origin !== '') {
        $o = parse_url($origin);
        $host = strtolower(($o['host'] ?? '') . (isset($o['port']) ? ':' . $o['port'] : ''));
        if ($host !== strtolower($_SERVER['HTTP_HOST'] ?? '')) avm_fail(403, 'origen');
    }
}

/* ---------- Rutas y permisos ---------- */
function avm_split(string $path): array {
    $p = explode('/', $path);
    if (count($p) !== 2 && count($p) !== 4) avm_fail(400, 'ruta');
    foreach ($p as $s) if (!preg_match(AVM_SEG, $s) || $s === '.' || $s === '..') avm_fail(400, 'ruta');
    return [implode('/', array_slice($p, 0, -1)), end($p)];
}
function avm_can_read(string $path, bool $admin, string $vid): bool {
    if ($admin) return true;
    $root = explode('/', $path)[0];
    if (!in_array($root, AVM_ADMIN_COLLS, true)) return true;
    return $path === 'leads/' . $vid;
}

/* ---------- Documentos ---------- */
function avm_get(string $path): ?array {
    $st = avm_db()->prepare('SELECT data FROM avm_docs WHERE tenant = ? AND path = ? AND deleted = 0');
    $st->execute([avm_tid(), $path]);
    $row = $st->fetch();
    return $row ? json_decode($row['data'], true) : null;
}
function avm_put(string $path, array $data): int {
    [$coll, $id] = avm_split($path);
    $ts = avm_now_ms();
    $json = json_encode((object) $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $st = avm_db()->prepare('INSERT INTO avm_docs (tenant, path, coll, id, data, updated, deleted) VALUES (?, ?, ?, ?, ?, ?, 0)
        ON DUPLICATE KEY UPDATE data = VALUES(data), updated = VALUES(updated), deleted = 0');
    $st->execute([avm_tid(), $path, $coll, $id, $json, $ts]);
    return $ts;
}
function avm_del(string $path): int {
    $ts = avm_now_ms();
    avm_db()->prepare('UPDATE avm_docs SET deleted = 1, data = "{}", updated = ? WHERE tenant = ? AND path = ?')->execute([$ts, avm_tid(), $path]);
    return $ts;
}
function avm_coll(string $coll): array {
    $st = avm_db()->prepare('SELECT id, data FROM avm_docs WHERE tenant = ? AND coll = ? AND deleted = 0');
    $st->execute([avm_tid(), $coll]);
    $out = [];
    foreach ($st as $r) $out[$r['id']] = json_decode($r['data'], true);
    return $out;
}

/* ---------- Automatizaciones (mismas reglas que el backoffice) ----------
   Cada solicitud nueva: crea o actualiza el prospecto, lo agrega a las listas de las
   automatizaciones que cumple y deja los correos en la bandeja de salida (outbox). */
function avm_norm(string $s): string {
    $s = mb_strtolower($s, 'UTF-8');
    if (class_exists('Normalizer')) return preg_replace('/\p{Mn}+/u', '', Normalizer::normalize($s, Normalizer::FORM_D));
    return strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n', 'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u', 'ç' => 'c']);
}
function avm_uniq(array $a): array { return array_values(array_unique(array_filter($a, fn($x) => $x !== '' && $x !== null))); }
function avm_prospect_key(array $l): string {
    $m = strtolower(trim((string) ($l['correo'] ?? '')));
    if (preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $m)) return 'm-' . substr(preg_replace('/[^a-z0-9_.~:@+-]/', '-', $m), 0, 190);
    $t = preg_replace('/\D/', '', (string) ($l['celular'] ?? ''));
    return strlen($t) >= 10 ? 't-' . $t : '';
}
function avm_dest_of(array $l): string {
    $v = $l['viaje'] ?? [];
    return (string) ($v['destino'] ?? $v['to'] ?? $v['nombre'] ?? $v['region'] ?? '');
}
function avm_fill(string $s, array $v): string {
    return preg_replace_callback('/\{\{\s*(\w+)\s*\}\}/', fn($m) => (string) ($v[$m[1]] ?? ''), $s);
}
function avm_matches(array $a, array $l, array $p): bool {
    $c = $a['cond'] ?? [];
    if (!empty($c['tipos']) && !in_array($l['tipo'] ?? 'viajero', $c['tipos'], true)) return false;
    if (!empty($c['destinos'])) {
        $d = avm_norm(avm_dest_of($l)); $ok = false;
        foreach ($c['destinos'] as $x) if ($x !== '' && str_contains($d, avm_norm($x))) { $ok = true; break; }
        if (!$ok) return false;
    }
    if (!empty($c['soloMarketing']) && empty($p['marketing'])) return false;
    return true;
}
function avm_process_lead(array $l, array $autos, array $lists, array $tpls, array $contact): array {
    $key = avm_prospect_key($l); if ($key === '') return [];
    $old = avm_get('prospects/' . $key) ?? [];
    $p = [
        'nombre' => ($l['nombre'] ?? '') ?: ($old['nombre'] ?? ''), 'correo' => ($l['correo'] ?? '') ?: ($old['correo'] ?? ''),
        'celular' => ($l['celular'] ?? '') ?: ($old['celular'] ?? ''), 'ciudad' => ($l['ciudad'] ?? '') ?: ($old['ciudad'] ?? ''),
        'tipos' => avm_uniq(array_merge($old['tipos'] ?? [], [$l['tipo'] ?? 'viajero'])),
        'intereses' => avm_uniq(array_merge($old['intereses'] ?? [], [avm_dest_of($l)])),
        'listas' => $old['listas'] ?? [], 'marketing' => !empty($old['marketing']) || !empty($l['promociones']),
        'consentimiento' => !empty($old['consentimiento']) || !empty($l['autorizaDatos']),
        'solicitudes' => ($old['solicitudes'] ?? 0) + 1, 'primera' => $old['primera'] ?? ($l['creado'] ?? avm_iso()), 'ultima' => $l['creado'] ?? avm_iso(),
        'notas' => $old['notas'] ?? '', 'ultimoNumero' => $l['numero'] ?? '',
    ];
    if (!empty($l['agencia'])) $p['agencia'] = $l['agencia'];
    $wa = preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})$/', '$1 $2 $3 $4', (string) ($contact['waMain'] ?? ''));
    $vars = [
        'nombre' => explode(' ', $p['nombre'] ?: ($l['nombre'] ?? ''))[0] ?: 'viajero', 'nombre_completo' => $p['nombre'],
        'numero' => $l['numero'] ?? '', 'destino' => avm_dest_of($l) ?: 'tu próximo viaje', 'agencia' => $l['agencia']['razonSocial'] ?? '',
        'asesor_whatsapp' => '+' . $wa, 'correo_avm' => $contact['email'] ?? '', 'correo_agencia' => $contact['email'] ?? '', 'marca' => avm_marca()['name'],
    ];
    $fi = avm_ficha($l);
    $vars += ['producto' => mb_strtolower($fi['productoCorto'], 'UTF-8'), 'titulo' => $fi['titulo'], 'resumen' => $fi['resumen']];
    uasort($autos, fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));
    $applied = [];
    foreach ($autos as $a) {
        if (empty($a['active']) || !avm_matches($a, $l, $p)) continue;
        $applied[] = $a['name'] ?? '';
        $L = $lists[$a['lista'] ?? ''] ?? null;
        if ($L && (empty($L['marketing']) || $p['marketing'])) $p['listas'] = avm_uniq(array_merge($p['listas'], [$a['lista']]));
        $T = $tpls[$a['plantilla'] ?? ''] ?? null;
        if ($T && $p['correo'] && (($T['tipo'] ?? '') !== 'marketing' || $p['marketing'])) {
            avm_put('outbox/' . bin2hex(random_bytes(10)), [
                'para' => $p['correo'], 'nombre' => $p['nombre'], 'asunto' => avm_fill($T['asunto'] ?? '', $vars), 'cuerpo' => avm_fill($T['cuerpo'] ?? '', $vars),
                'plantilla' => $T['name'] ?? '', 'tipo' => $T['tipo'] ?? '', 'automatizacion' => $a['name'] ?? '', 'solicitud' => $l['numero'] ?? '', 'ficha' => avm_ficha($l),
                'prospecto' => $key, 'estado' => 'pendiente', 'creado' => avm_iso(),
            ]);
        }
    }
    avm_put('prospects/' . $key, $p);
    return $applied;
}
/** Procesa las solicitudes sin sincronizar. Con $only, solo el documento de ese visitante. */
function avm_run_engine(?string $only = null): int {
    $pdo = avm_db();
    if (!$pdo->query("SELECT GET_LOCK('avm_engine', 5)")->fetchColumn()) return 0;
    try {
        $autos = avm_coll('automations'); $lists = avm_coll('lists'); $tpls = avm_coll('templates');
        $contact = (avm_get('site/content') ?? [])['contact'] ?? [];
        $docs = $only ? [substr($only, 6) => avm_get($only)] : avm_coll('leads');
        $n = 0;
        foreach ($docs as $id => $doc) {
            if (!$doc || empty($doc['items'])) continue;
            $changed = false;
            foreach ($doc['items'] as &$it) {
                if (!empty($it['sync'])) continue;
                $it['auto'] = avm_process_lead($it, $autos, $lists, $tpls, $contact);
                try { $liq = avm_liquidar_lead($it); } catch (Throwable $e) { $liq = null; error_log('AVM liquidación: ' . $e->getMessage()); }
                avm_notify_team($it, $contact, $liq);
                $it['sync'] = avm_iso(); $changed = true; $n++;
            }
            unset($it);
            if ($changed) avm_put('leads/' . $id, $doc);
        }
        return $n;
    } finally { $pdo->query("SELECT RELEASE_LOCK('avm_engine')"); }
}

/* ---------- Aviso al equipo comercial ----------
   Por cada solicitud nueva, un correo a cada dirección de "Correos que reciben aviso" (backoffice → Contacto). */
const AVM_TIPOS = ['viajero' => 'Cotización', 'busqueda' => 'Búsqueda de precios', 'boletin' => 'Boletín', 'agencia' => 'Agencia',
    'tour' => 'Tour', 'circuito' => 'Circuito', 'grupo' => 'Salida grupal', 'traslado' => 'Traslado'];
/**
 * Desglose interno de una solicitud con precio en línea: se vuelve a consultar la tarifa (normalmente sale de la caché)
 * y se guarda en liquidaciones/<número>, que solo leen los administradores.
 */
function avm_liquidar_lead(array $l): ?array {
    $v = (array) ($l['viaje'] ?? []);
    if (empty($l['numero']) || empty($v['hotelId']) || empty($v['d1']) || empty($v['d2']) || empty($v['habitacion'])) return null;
    $id = (string) $v['hotelId'];
    $kids = array_fill(0, max(0, min(6, (int) ($v['kids'] ?? 0))), 8);
    $r = str_starts_with($id, 'p-')
        ? ['ok' => true, 'rates' => avm_prop_tarifas([$id], (string) $v['d1'], (string) $v['d2'], (int) ($v['rooms'] ?? 1), (int) ($v['adults'] ?? 2), $kids, 40), 'nights' => max(1, (int) round((strtotime((string) $v['d2']) - strtotime((string) $v['d1'])) / 86400))]
        : avm_lite_tarifas([$id], (string) $v['d1'], (string) $v['d2'], (int) ($v['rooms'] ?? 1), (int) ($v['adults'] ?? 2), $kids, 40);
    $want = (float) ($v['totalUsd'] ?? 0); $best = null; $pts = INF;
    foreach ($r['ok'] ? ($r['rates'][$id]['options'] ?? []) : [] as $o) {
        if (empty($o['liq'])) continue;
        $p = ($o['room'] === $v['habitacion'] ? 0 : 1e6) + (($o['board'] ?? '') === ($v['regimen'] ?? '') ? 0 : 1e5) + abs($o['total'] - $want);
        if ($p < $pts) { $pts = $p; $best = $o; }
    }
    if (!$best || $pts >= 1e5) return null;
    $n = (int) ($r['nights'] ?? 0);
    $doc = ['numero' => (string) $l['numero'], 'creado' => avm_iso(), 'trm' => (float) ((avm_get('site/content') ?? [])['txt']['trm'] ?? 0),
        'items' => [['producto' => 'Hotel', 'nombre' => implode(' · ', array_filter([(string) ($v['hotel'] ?? ''), $best['room'], $best['board'], $n ? avm_plural($n, 'noche', 'noches') : ''])),
            'moneda' => $best['currency'] ?? 'USD', 'precioVisto' => $want, 'liq' => $best['liq']]],
        'adicionales' => (string) ($v['adicionales'] ?? '')];
    avm_put('liquidaciones/' . preg_replace('/[^A-Za-z0-9_-]/', '-', (string) $l['numero']), $doc);
    return $doc;
}
function avm_notify_team(array $l, array $contact, ?array $liq = null): void {
    $to = array_filter((array) ($contact['notify'] ?? []), fn($m) => filter_var($m, FILTER_VALIDATE_EMAIL));
    if (!$to || ($l['tipo'] ?? '') === 'boletin') return;
    $tipo = AVM_TIPOS[$l['tipo'] ?? 'viajero'] ?? 'Solicitud';
    $dest = avm_dest_of($l);
    $viaje = [];
    foreach ((array) ($l['viaje'] ?? []) as $k => $v) if ($v !== '' && $v !== '0' && !is_array($v)) $viaje[] = "  $k: $v";
    $tel = preg_replace('/\D/', '', (string) ($l['celular'] ?? ''));
    if (strlen($tel) === 10 && $tel[0] === '3') $tel = '57' . $tel;
    $site = avm_tenant_url();
    $cuerpo = "Nueva solicitud " . ($l['numero'] ?? '') . " · $tipo" . ($dest ? " · $dest" : '') . "\n\n"
        . "Nombre: " . (($l['nombre'] ?? '') ?: 'sin nombre') . "\nCelular: " . ($l['celular'] ?? '') . "\nCorreo: " . ($l['correo'] ?? '') . "\n"
        . ($viaje ? "\nViaje:\n" . implode("\n", $viaje) . "\n" : '')
        . (!empty($l['comentarios']) ? "\nComentarios: " . $l['comentarios'] . "\n" : '')
        . ($tel ? "\nEscribirle por WhatsApp: https://wa.me/$tel\n" : '')
        . "Ver en el backoffice: $site/#admin\n";
    foreach ($to as $m) {
        avm_put('outbox/' . bin2hex(random_bytes(10)), [
            'para' => $m, 'nombre' => 'Equipo ' . avm_marca()['name'], 'asunto' => 'Nueva solicitud: ' . ($fi = avm_ficha($l))['productoCorto'] . ($fi['destino'] ? ' en ' . $fi['destino'] : '') . ' · ' . ($l['numero'] ?? ''),
            'cuerpo' => $cuerpo, 'plantilla' => 'Aviso al equipo', 'tipo' => 'interno', 'automatizacion' => 'Aviso al equipo',
            'solicitud' => $l['numero'] ?? '', 'prospecto' => avm_prospect_key($l), 'ficha' => avm_ficha($l), 'estado' => 'pendiente', 'creado' => avm_iso(),
            ...($liq ? ['liq' => $liq] : []),
        ]);
    }
}

/* ---------- TRM del día (datos abiertos del Banco de la República) ---------- */
function avm_update_trm(): string {
    // La fuente publica por adelantado la TRM del día siguiente, y la del fin de semana rige de sábado a lunes:
    // se toma la que está vigente hoy (hora de Colombia) y se muestra con la fecha de hoy.
    $hoy = date('Y-m-d');
    $sys = avm_get('system/trm') ?? [];
    $doc = avm_get('site/content') ?? [];
    $txt = $doc['txt'] ?? [];
    $alDia = ($txt['trmDate'] ?? '') === $hoy && empty($txt['trmSample']);
    // Con la TRM de hoy ya guardada se revisa cada 6 horas; si falta, cada 30 minutos.
    if (($sys['checked'] ?? 0) > time() - ($alDia ? 6 * 3600 : 1800)) return 'TRM al día';
    avm_put('system/trm', ['checked' => time()]);
    if (!empty($txt['trmManual'])) return 'TRM manual (no se actualiza)';
    [$row, $err] = avm_trm_fuente($hoy);
    if (!$row || !is_numeric($row['valor'] ?? null)) return 'TRM: sin respuesta de la fuente' . ($err ? " ($err)" : '');
    $txt['trm'] = round((float) $row['valor'], 2);
    $txt['trmDate'] = $hoy;
    $txt['trmDesde'] = substr((string) $row['vigenciadesde'], 0, 10);
    $txt['trmHasta'] = substr((string) ($row['vigenciahasta'] ?? $row['vigenciadesde']), 0, 10);
    $txt['trmSample'] = false;
    $doc['txt'] = $txt;
    avm_put('site/content', $doc);
    return "TRM {$txt['trm']} vigente hoy {$hoy}";
}

/** TRM vigente hoy en datos.gov.co; la respuesta se guarda 30 minutos para no consultarla por cada agencia. */
function avm_trm_fuente(string $hoy): array {
    $dir = dirname(__DIR__, 2) . '/avm-cache';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    $file = $dir . '/trm-' . $hoy . '.json';
    if (is_file($file) && time() - filemtime($file) < 1800 && ($j = json_decode((string) file_get_contents($file), true))) return [$j, ''];
    $q = http_build_query(['$where' => "vigenciadesde <= '{$hoy}T00:00:00' AND vigenciahasta >= '{$hoy}T00:00:00'", '$order' => 'vigenciadesde DESC', '$limit' => 1]);
    $ch = curl_init('https://www.datos.gov.co/resource/32sa-8pi3.json?' . $q);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_HTTPHEADER => ['Accept: application/json']]);
    $resp = curl_exec($ch); $err = curl_error($ch); curl_close($ch);
    $row = json_decode((string) $resp, true)[0] ?? null;
    if ($row) @file_put_contents($file, json_encode($row));
    return [$row, $err];
}

/* ---------- Copia de seguridad diaria por agencia (fuera de la carpeta pública, se guardan 14 días) ---------- */
function avm_backup(): string {
    $dir = dirname(__DIR__, 2) . '/avm-copias/' . avm_tenant()['slug'];
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    $file = $dir . '/avm-' . date('Y-m-d') . '.json.gz';
    if (is_file($file)) return 'copia del día ya existe';
    $out = [];
    $st = avm_db()->prepare('SELECT path, data FROM avm_docs WHERE tenant = ? AND deleted = 0'); $st->execute([avm_tid()]);
    foreach ($st as $r) $out[$r['path']] = json_decode($r['data'], true);
    file_put_contents($file, gzencode(json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 9), LOCK_EX);
    @chmod($file, 0600);
    foreach (glob($dir . '/avm-*.json.gz') ?: [] as $f) if (filemtime($f) < time() - 14 * 86400) @unlink($f);
    return 'copia creada: ' . basename($file) . ' (' . count($out) . ' documentos)';
}

/* ---------- Correos con Resend ---------- */
function avm_send_outbox(int $max = 20): array {
    $c = ['resend_key' => (string) avm_cfg('resend_key', ''), 'mail_from' => avm_mail_from(), 'mail_reply_to' => (string) avm_cfg('mail_reply_to', ''), 'mail_daily_limit' => avm_cfg('mail_daily_limit', 100)];
    if (empty($c['resend_key']) || empty($c['mail_from'])) return ['enviados' => 0, 'errores' => 0, 'nota' => 'Resend sin configurar'];
    $contact = (avm_get('site/content') ?? [])['contact'] ?? [];
    $ok = 0; $err = 0;
    // Límite diario del plan de Resend (100 en el gratuito). Las campañas dejan 20 de margen para las cotizaciones.
    $limite = (int) ($c['mail_daily_limit'] ?? 100);
    $hoy = avm_db()->prepare("SELECT COUNT(*) FROM avm_docs WHERE tenant = ? AND coll = 'outbox' AND deleted = 0 AND JSON_UNQUOTE(JSON_EXTRACT(data, '$.estado')) = 'enviado' AND LEFT(JSON_UNQUOTE(JSON_EXTRACT(data, '$.enviado')), 10) = ?");
    $hoy->execute([avm_tid(), gmdate('Y-m-d')]);
    $enviadosHoy = (int) $hoy->fetchColumn();
    $campanas = avm_coll('campaigns');
    $st = avm_db()->prepare("SELECT path, data FROM avm_docs WHERE tenant = ? AND coll = 'outbox' AND deleted = 0 AND JSON_UNQUOTE(JSON_EXTRACT(data, '$.estado')) = 'pendiente'
        ORDER BY (JSON_UNQUOTE(JSON_EXTRACT(data, '$.tipo')) = 'marketing'), updated LIMIT " . (int) ($max * 4));
    $st->execute([avm_tid()]);
    $n = 0;
    foreach ($st->fetchAll() as $row) {
        if ($n >= $max || $enviadosHoy >= $limite) break;
        $m = json_decode($row['data'], true);
        if (($m['tipo'] ?? '') === 'marketing') {
            if ($enviadosHoy >= $limite - 20) continue;
            $cs = $campanas[$m['campana'] ?? ''] ?? null;
            if ($cs && in_array($cs['estado'] ?? '', ['pausada', 'cancelada', 'borrador'], true)) continue;
        }
        if ($n++ > 0) usleep(600000); // Resend acepta 2 envíos por segundo
        $html = avm_email_html($m, $contact);
        $payload = ['from' => $c['mail_from'], 'to' => [$m['para']], 'subject' => $m['asunto'] ?? '', 'html' => $html, 'text' => $m['cuerpo'] ?? ''];
        if (($m['tipo'] ?? '') === 'marketing' && ($baja = avm_baja_url((string) ($m['prospecto'] ?? '')))) {
            $payload['headers'] = ['List-Unsubscribe' => '<' . $baja . '>', 'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click'];
            $payload['text'] .= "\n\nPara no recibir más promociones: " . $baja;
        }
        if (!empty($c['mail_reply_to'])) $payload['reply_to'] = $c['mail_reply_to'];
        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $c['resend_key'], 'Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE)]);
        $body = curl_exec($ch); $status = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        if ($status >= 200 && $status < 300) {
            $m['estado'] = 'enviado'; $m['enviado'] = avm_iso(); $enviadosHoy++; $m['resendId'] = json_decode((string) $body, true)['id'] ?? ''; $ok++;
        } else {
            $m['intentos'] = ($m['intentos'] ?? 0) + 1; $m['ultimoError'] = substr((string) $body, 0, 300);
            if ($m['intentos'] >= 5) $m['estado'] = 'error';
            $err++;
        }
        avm_put($row['path'], $m);
    }
    return ['enviados' => $ok, 'errores' => $err];
}
