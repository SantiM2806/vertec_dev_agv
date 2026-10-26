<?php
/**
 * Multi-agencia (marca blanca): una sola instalación sirve a muchas agencias.
 *
 * Cada agencia (tenant) tiene su fila en avm_tenants, y todos sus documentos y usuarios llevan su id.
 * La agencia se reconoce por la dirección con la que entra el visitante:
 *  - su dominio propio (columna domains, separados por coma), o
 *  - un subdominio de la plataforma: <slug>.<base_domain> (base_domain va en avm-config.php), o
 *  - en local: <slug>.localhost, ?agencia=<slug> (queda en una cookie) o dev_tenant de avm-config.php.
 * La dirección base de la plataforma (sin subdominio) no es una agencia: muestra la portada de la plataforma.
 *
 * Configuración de cada agencia (llaves de proveedores, remitente de correos…) en avm_tenants.config;
 * las llaves se guardan cifradas con app_secret.
 */

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === '_tenant.php') { http_response_code(404); exit; }

/** Valores que una agencia puede heredar de la plataforma si no tiene los suyos. */
const AVM_CFG_HEREDABLES = ['resend_key', 'mail_daily_limit', 'liteapi_base'];
/** Valores de la agencia que se guardan cifrados. */
const AVM_CFG_SECRETOS = ['liteapi_key', 'resend_key', 'tbo_key', 'tbo_pass', 'w2m_key', 'omnibees_key'];

function avm_host(): string {
    return preg_replace('/:\d+$/', '', strtolower((string) ($_SERVER['HTTP_HOST'] ?? '')));
}
function avm_es_local(string $h): bool {
    return in_array($h, ['localhost', '127.0.0.1', '::1', '[::1]'], true) || str_ends_with($h, '.localhost');
}
function avm_plataforma_nombre(): string { return (string) (avm_config()['platform_name'] ?? 'Vertec Travel'); }

/* ---------- Agencia actual ---------- */
function avm_tenant_fila(string $campo, string $valor): ?array {
    $sql = $campo === 'domain'
        ? 'SELECT * FROM avm_tenants WHERE FIND_IN_SET(?, domains) > 0 LIMIT 1'
        : 'SELECT * FROM avm_tenants WHERE ' . ($campo === 'id' ? 'id' : 'slug') . ' = ? LIMIT 1';
    try { $st = avm_db()->prepare($sql); $st->execute([$valor]); $r = $st->fetch(); }
    catch (PDOException $e) { return null; } // tablas aún sin crear
    if (!$r) return null;
    $r['id'] = (int) $r['id'];
    $r['config'] = json_decode((string) ($r['config'] ?? ''), true) ?: [];
    return $r;
}
/** Agencia de esta petición, o null si es la dirección de la plataforma o no existe. */
function avm_tenant_opt(): ?array {
    if (array_key_exists('AVM_T', $GLOBALS)) return $GLOBALS['AVM_T'];
    $t = null;
    if (PHP_SAPI !== 'cli') {
        $h = avm_host();
        $base = strtolower(trim((string) (avm_config()['base_domain'] ?? ''), '.'));
        if (avm_es_local($h)) {
            if (str_ends_with($h, '.localhost')) $t = avm_tenant_fila('slug', substr($h, 0, -10));
            else {
                $q = (string) ($_GET['agencia'] ?? '');
                if ($q !== '' && preg_match('/^[a-z0-9-]{2,60}$/', $q) && ($t = avm_tenant_fila('slug', $q)))
                    setcookie('vt_agencia', $q, ['path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
                $t = $t ?: avm_tenant_fila('slug', (string) ($_COOKIE['vt_agencia'] ?? (avm_config()['dev_tenant'] ?? '')));
            }
        } elseif ($base !== '' && str_ends_with($h, '.' . $base) && !str_starts_with($h, 'www.' . $base)) {
            $t = avm_tenant_fila('slug', substr($h, 0, -strlen($base) - 1));
        } elseif ($h !== $base && $h !== 'www.' . $base) {
            $t = avm_tenant_fila('domain', $h) ?? avm_tenant_fila('domain', preg_replace('/^www\./', '', $h));
        }
    }
    $GLOBALS['AVM_T'] = $t;
    return $t;
}
/** Agencia de esta petición; si no hay, responde 404 (o 403 si está suspendida). */
function avm_tenant(): array {
    $t = avm_tenant_opt();
    if (!$t) avm_fail(404, 'agencia');
    if (($t['estado'] ?? 'activa') !== 'activa' && empty($GLOBALS['AVM_T_FORZADA'])) avm_fail(403, 'suspendida');
    return $t;
}
function avm_tid(): int { return avm_tenant()['id']; }
/** Cambia de agencia (cron y panel de la plataforma). */
function avm_use_tenant(?array $t): void { $GLOBALS['AVM_T'] = $t; $GLOBALS['AVM_T_FORZADA'] = true; }
function avm_tenants(bool $soloActivas = true): array {
    $out = [];
    foreach (avm_db()->query('SELECT * FROM avm_tenants' . ($soloActivas ? " WHERE estado = 'activa'" : '') . ' ORDER BY name') as $r) {
        $r['id'] = (int) $r['id']; $r['config'] = json_decode((string) ($r['config'] ?? ''), true) ?: []; $out[] = $r;
    }
    return $out;
}

/* ---------- Configuración ---------- */
function avm_seal(string $plano): string {
    if ($plano === '') return '';
    $k = hash('sha256', avm_app_secret(), true); $iv = random_bytes(12);
    $ct = openssl_encrypt($plano, 'aes-256-gcm', $k, OPENSSL_RAW_DATA, $iv, $tag);
    return 'enc:' . base64_encode($iv . $tag . $ct);
}
function avm_open(string $s): string {
    if (!str_starts_with($s, 'enc:')) return $s;
    $b = base64_decode(substr($s, 4), true);
    if ($b === false || strlen($b) < 29) return '';
    $k = hash('sha256', avm_app_secret(), true);
    $p = openssl_decrypt(substr($b, 28), 'aes-256-gcm', $k, OPENSSL_RAW_DATA, substr($b, 0, 12), substr($b, 12, 16));
    return $p === false ? '' : $p;
}
function avm_app_secret(): string {
    $c = avm_config();
    return (string) ($c['app_secret'] ?? hash('sha256', ($c['db_pass'] ?? '') . '|' . ($c['db_name'] ?? '') . '|avm-baja'));
}
/** Valor de configuración: primero el de la agencia, luego (si es heredable) el de la plataforma. */
function avm_cfg(string $k, $def = null) {
    $t = $GLOBALS['AVM_T'] ?? null;
    $v = $t['config'][$k] ?? null;
    if (is_string($v) && in_array($k, AVM_CFG_SECRETOS, true)) $v = avm_open($v);
    if ($v !== null && $v !== '') return $v;
    // Llave de LiteAPI de la plataforma solo para agencias de demostración
    if ($k === 'liteapi_key' && !empty($t['config']['liteapi_demo'])) return avm_config()['liteapi_key'] ?? $def;
    if (!$t || in_array($k, AVM_CFG_HEREDABLES, true)) { $p = avm_config()[$k] ?? null; if ($p !== null && $p !== '') return $p; }
    return $def;
}
/** Guarda valores de configuración de la agencia actual (cifra las llaves; '' borra). */
function avm_cfg_guardar(array $vals): void {
    $t = avm_tenant();
    $c = $t['config'];
    foreach ($vals as $k => $v) {
        if (!preg_match('/^[a-z0-9_]{2,40}$/', (string) $k)) continue;
        if ($v === null || $v === '') { unset($c[$k]); continue; }
        $c[$k] = in_array($k, AVM_CFG_SECRETOS, true) ? avm_seal((string) $v) : $v;
    }
    avm_db()->prepare('UPDATE avm_tenants SET config = ? WHERE id = ?')->execute([json_encode($c, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $t['id']]);
    $t['config'] = $c; $GLOBALS['AVM_T'] = $t;
}
/** Remitente de los correos: el de la agencia si tiene su propio Resend; si no, el de la plataforma con el nombre de la agencia. */
function avm_mail_from(): string {
    $t = $GLOBALS['AVM_T'] ?? null;
    $propio = $t ? avm_open((string) ($t['config']['resend_key'] ?? '')) : '';
    if ($propio !== '' && !empty($t['config']['mail_from'])) return (string) $t['config']['mail_from'];
    $p = (string) (avm_config()['mail_from'] ?? '');
    if ($p === '' || !$t) return $p;
    $dir = preg_match('/<([^>]+)>/', $p, $m) ? $m[1] : $p;
    $nombre = str_replace(['"', '<', '>'], '', (string) (avm_marca()['name'] ?? $t['name']));
    return '"' . $nombre . '" <' . $dir . '>';
}
/** Dirección pública de la agencia actual, sin / al final. */
function avm_tenant_url(?array $t = null): string {
    $t = $t ?? ($GLOBALS['AVM_T'] ?? null);
    if (PHP_SAPI !== 'cli' && !empty($_SERVER['HTTP_HOST']) && $t === ($GLOBALS['AVM_T'] ?? null))
        return (avm_https() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
    if (!$t) return rtrim((string) (avm_config()['site_url'] ?? ''), '/');
    if (!empty($t['config']['site_url'])) return rtrim((string) $t['config']['site_url'], '/');
    $dom = trim(explode(',', (string) $t['domains'])[0] ?? '');
    if ($dom !== '') return 'https://' . $dom;
    $base = trim((string) (avm_config()['base_domain'] ?? ''), '.');
    if ($base !== '') return (avm_config()['base_http'] ?? 'https') . '://' . $t['slug'] . '.' . $base;
    // En local, sin base_domain: <slug>.localhost (los navegadores lo resuelven a este mismo equipo)
    if (PHP_SAPI !== 'cli' && avm_es_local(avm_host())) {
        $puerto = preg_match('/:(\d+)$/', (string) ($_SERVER['HTTP_HOST'] ?? ''), $m) ? ':' . $m[1] : '';
        return 'http://' . $t['slug'] . '.localhost' . $puerto;
    }
    return rtrim((string) (avm_config()['site_url'] ?? ''), '/');
}

/* ---------- Marca de la agencia (nombre, logos y colores) ---------- */
const AVM_MARCA_DEF = ['name' => 'Tu agencia', 'legal' => '', 'rnt' => '', 'tagline' => 'Agencia de viajes', 'accent' => '#BBCF26', 'dark' => '#12161B', 'paper' => '#F3F4F1', 'fonts' => 'moderna', 'logo' => '', 'logoDark' => '', 'icon' => ''];
function avm_marca(): array {
    static $cache = [];
    $tid = ($GLOBALS['AVM_T'] ?? null)['id'] ?? 0;
    if (!isset($cache[$tid])) {
        $b = $tid ? ((avm_get('site/content') ?? [])['brand'] ?? []) : [];
        $cache[$tid] = array_merge(AVM_MARCA_DEF, ['name' => ($GLOBALS['AVM_T'] ?? null)['name'] ?? AVM_MARCA_DEF['name']], array_filter((array) $b, fn($v) => $v !== '' && $v !== null));
    }
    return $cache[$tid];
}
/** Tipografías que puede elegir cada agencia: [títulos, texto, familias para Google Fonts]. */
const AVM_FUENTES = [
    'moderna' => ['Outfit', 'Hanken Grotesk', 'Outfit:wght@400;600;700&family=Hanken+Grotesk:wght@400;500;600;700'],
    'clasica' => ['Playfair Display', 'Source Sans 3', 'Playfair+Display:wght@500;600;700&family=Source+Sans+3:wght@400;500;600;700'],
    'amigable' => ['Nunito', 'Nunito Sans', 'Nunito:wght@500;700;800&family=Nunito+Sans:wght@400;600;700'],
    'elegante' => ['DM Serif Display', 'DM Sans', 'DM+Serif+Display&family=DM+Sans:wght@400;500;600;700'],
    'geometrica' => ['Poppins', 'Inter', 'Poppins:wght@500;600;700&family=Inter:wght@400;500;600;700'],
];
function avm_hex(string $c, string $def): string { return preg_match('/^#[0-9a-fA-F]{6}$/', $c) ? strtoupper($c) : $def; }
function avm_luz(string $hex): float {
    $f = fn($v) => ($v /= 255) <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
    return 0.2126 * $f(hexdec(substr($hex, 1, 2))) + 0.7152 * $f(hexdec(substr($hex, 3, 2))) + 0.0722 * $f(hexdec(substr($hex, 5, 2)));
}
/** Texto legible sobre un color: casi negro o blanco, el de mayor contraste. */
function avm_tinta(string $hex): string { $l = avm_luz($hex); return (1.05 / ($l + 0.05)) >= (($l + 0.05) / (avm_luz('#14181D') + 0.05)) ? '#FFFFFF' : '#14181D'; }
/** Variables CSS de la marca (las mismas reglas que applyBrand() en la página). */
function avm_marca_css(array $b): string {
    $acc = avm_hex((string) $b['accent'], '#BBCF26'); $dark = avm_hex((string) $b['dark'], '#12161B'); $paper = avm_hex((string) $b['paper'], '#F3F4F1');
    $f = AVM_FUENTES[$b['fonts']] ?? AVM_FUENTES['moderna'];
    $v = "--signal:$acc;--signal-ink:" . avm_tinta($acc) . ";--bar-bg:$dark;--f-display:\"{$f[0]}\",\"Segoe UI\",system-ui,sans-serif;--f-body:\"{$f[1]}\",\"Segoe UI\",system-ui,sans-serif;";
    $claro = ($dark !== '#12161B' && avm_luz($dark) < 0.06 ? "--ink:$dark;" : '') . (avm_luz($paper) > 0.82 ? "--paper:$paper;" : '');
    return ":root{{$v}}:root:not([data-theme=\"dark\"]){{$claro}}@media (prefers-color-scheme: dark){:root:not([data-theme=\"light\"]){--paper:#0E1114;--ink:#EEF0F2}}";
}
/** Lo que index.php agrega en <head>: tipografía, colores, ícono y la marca para la página. */
function avm_marca_head(array $b): string {
    $e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    $f = AVM_FUENTES[$b['fonts']] ?? AVM_FUENTES['moderna'];
    $publica = array_intersect_key($b, array_flip(['name', 'legal', 'rnt', 'tagline', 'accent', 'dark', 'paper', 'fonts', 'logo', 'logoDark', 'icon']));
    $icon = ($b['icon'] ?? '') || $b['logo'] ? 'api/marca.php?img=icon&v=' . substr(md5((string) (($b['icon'] ?? '') ?: $b['logo'])), 0, 8) : '';
    return ($b['fonts'] !== 'moderna' && isset(AVM_FUENTES[$b['fonts']]) ? '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=' . $e($f[2]) . '&display=swap">' . "\n" : '')
        . ($icon ? '<link rel="icon" href="' . $e($icon) . '">' . "\n" : '')
        . '<meta name="theme-color" content="' . $e(avm_hex((string) $b['dark'], '#12161B')) . '">' . "\n"
        . '<style id="brandCss">' . avm_marca_css($b) . "</style>\n"
        . '<script>window.AVM_BRAND=' . json_encode($publica, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) . ";</script>\n";
}
/** URL absoluta del logo (para correos y SEO). */
function avm_logo_url(bool $oscuro = false): string {
    $m = avm_marca(); $ref = (string) ($oscuro ? ($m['logoDark'] ?: $m['logo']) : $m['logo']);
    if ($ref === '') return '';
    return avm_tenant_url() . '/api/marca.php?img=' . ($oscuro ? 'logoDark' : 'logo') . '&v=' . substr(md5($ref), 0, 8);
}

/* ---------- Esquema de la base de datos (instalación y migración) ---------- */
function avm_columnas(PDO $pdo, string $tabla): array {
    try { return array_column($pdo->query("SHOW COLUMNS FROM `$tabla`")->fetchAll(), 'Field'); } catch (PDOException $e) { return []; }
}
/**
 * Crea las tablas o migra una instalación de una sola agencia: sus datos quedan en la agencia 1.
 * $primera = ['slug' => …, 'name' => …, 'domains' => …] para nombrar esa agencia al migrar.
 */
function avm_schema(PDO $pdo, array $primera = []): array {
    $log = [];
    $pdo->exec("CREATE TABLE IF NOT EXISTS avm_tenants (
        id INT AUTO_INCREMENT PRIMARY KEY, slug VARCHAR(60) NOT NULL UNIQUE, name VARCHAR(160) NOT NULL,
        domains VARCHAR(1000) NOT NULL DEFAULT '', estado VARCHAR(20) NOT NULL DEFAULT 'activa', plan VARCHAR(40) NOT NULL DEFAULT 'demo',
        config LONGTEXT NULL, created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $docs = avm_columnas($pdo, 'avm_docs');
    if (!$docs) {
        $pdo->exec("CREATE TABLE avm_docs (
            tenant INT NOT NULL, path VARCHAR(420) NOT NULL, coll VARCHAR(210) NOT NULL, id VARCHAR(200) NOT NULL,
            data LONGTEXT NOT NULL, updated BIGINT NOT NULL, deleted TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (tenant, path), KEY tcoll (tenant, coll), KEY tupd (tenant, updated)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $log[] = 'tabla de documentos creada';
    } elseif (!in_array('tenant', $docs, true)) {
        $pdo->exec('ALTER TABLE avm_docs ADD COLUMN tenant INT NOT NULL DEFAULT 1 FIRST, DROP PRIMARY KEY, ADD PRIMARY KEY (tenant, path), ADD KEY tcoll (tenant, coll), ADD KEY tupd (tenant, updated)');
        $log[] = 'documentos existentes movidos a la agencia 1';
    }
    $users = avm_columnas($pdo, 'avm_users');
    if (!$users) {
        $pdo->exec("CREATE TABLE avm_users (
            id INT AUTO_INCREMENT PRIMARY KEY, tenant INT NOT NULL, email VARCHAR(190) NOT NULL, pass VARCHAR(255) NOT NULL,
            role VARCHAR(20) NOT NULL DEFAULT 'admin', name VARCHAR(120) NOT NULL DEFAULT '',
            created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, last_login DATETIME NULL, UNIQUE KEY tmail (tenant, email)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $log[] = 'tabla de usuarios creada';
    } elseif (!in_array('tenant', $users, true)) {
        $pdo->exec("ALTER TABLE avm_users ADD COLUMN tenant INT NOT NULL DEFAULT 1 AFTER id, ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'admin', ADD COLUMN name VARCHAR(120) NOT NULL DEFAULT '', DROP INDEX email, ADD UNIQUE KEY tmail (tenant, email)");
        $log[] = 'usuarios existentes movidos a la agencia 1';
    }
    $pdo->exec('CREATE TABLE IF NOT EXISTS avm_rate (k VARCHAR(80) NOT NULL, t INT NOT NULL, KEY kt (k, t)) ENGINE=InnoDB');
    // Si había datos de una sola agencia y aún no existe la agencia 1, se crea con ellos
    $hayDocs = (int) $pdo->query('SELECT COUNT(*) FROM avm_docs WHERE tenant = 1')->fetchColumn();
    $hayT1 = (int) $pdo->query('SELECT COUNT(*) FROM avm_tenants WHERE id = 1')->fetchColumn();
    if ($hayDocs && !$hayT1) {
        // Las llaves y el remitente que tenía el sitio pasan a ser de esa agencia (cifrados)
        $c = avm_config(); $cfg = [];
        foreach (['liteapi_key', 'resend_key', 'mail_from', 'mail_reply_to', 'site_url'] as $k)
            if (!empty($c[$k])) $cfg[$k] = in_array($k, AVM_CFG_SECRETOS, true) ? avm_seal((string) $c[$k]) : $c[$k];
        $pdo->prepare("INSERT INTO avm_tenants (id, slug, name, domains, estado, plan, config) VALUES (1, ?, ?, ?, 'activa', 'cliente', ?)")
            ->execute([$primera['slug'] ?? 'agencia1', $primera['name'] ?? 'Agencia 1', $primera['domains'] ?? '', json_encode((object) $cfg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        $log[] = 'agencia 1 creada con los datos existentes' . ($cfg ? ' y sus llaves' : '');
    }
    return $log;
}

/** Carga el contenido inicial de una agencia nueva (no pisa documentos que ya existan). */
function avm_sembrar(int $tid): int {
    $seed = __DIR__ . '/../datos-iniciales.json';
    if (!is_file($seed)) return 0;
    $n = 0; $antes = $GLOBALS['AVM_T'] ?? null; $f = $GLOBALS['AVM_T_FORZADA'] ?? false;
    avm_use_tenant(avm_tenant_fila('id', (string) $tid));
    foreach (json_decode((string) file_get_contents($seed), true) ?: [] as $path => $doc)
        if (is_array($doc) && avm_get($path) === null) { avm_put($path, $doc); $n++; }
    $GLOBALS['AVM_T'] = $antes; $GLOBALS['AVM_T_FORZADA'] = $f;
    return $n;
}
