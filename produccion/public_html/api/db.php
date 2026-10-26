<?php
/**
 * Base de datos del sitio para la página (la usa avm-db.js).
 *
 * GET  ?op=sync&since=<ms>  → documentos cambiados desde <ms> que esta persona puede leer.
 * POST { op: set | update | delete | acquire, path, data }
 *
 * Permisos (los mismos del prototipo):
 *  - Todos leen el contenido público (textos, destinos, tours, salidas, traslados, fotos).
 *  - Un visitante solo lee y escribe su propio documento leads/<visitante>.
 *  - Solicitudes, prospectos, listas, automatizaciones, plantillas y correos: solo administradores.
 */
require __DIR__ . '/_lib.php';

$admin = avm_is_admin();
$vid = avm_visitor();
session_write_close();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $since = max(0, (int) ($_GET['since'] ?? 0));
    $now = avm_now_ms();
    $st = avm_db()->prepare('SELECT path, data, deleted FROM avm_docs WHERE updated > ?' . ($since === 0 ? ' AND deleted = 0' : ''));
    $st->execute([$since]);
    $docs = [];
    foreach ($st as $r) {
        if (!avm_can_read($r['path'], $admin, $vid)) continue;
        $docs[] = ['path' => $r['path'], 'data' => $r['deleted'] ? null : json_decode($r['data'], true)];
    }
    // Margen de 3 s para no perder escrituras que terminan mientras se lee.
    avm_json(['now' => max($since, $now - 3000), 'me' => ['id' => $vid, 'admin' => $admin], 'docs' => $docs]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') avm_fail(405, 'metodo');
avm_check_origin();

$raw = file_get_contents('php://input', false, null, 0, 4 * 1024 * 1024 + 1);
if (strlen($raw) > ($admin ? 4 * 1024 * 1024 : 200 * 1024)) avm_fail(413, 'muy_grande');
$req = json_decode($raw, true);
if (!is_array($req)) avm_fail(400, 'json');
$op = $req['op'] ?? '';
$path = (string) ($req['path'] ?? '');
avm_split($path);
$data = $req['data'] ?? null;

/* ---------- Visitantes: solo guardan sus propias solicitudes ---------- */
if (!$admin) {
    if ($op !== 'set' || $path !== 'leads/' . $vid) avm_fail(403, 'permiso');
    if (!is_array($data) || !isset($data['items']) || !is_array($data['items']) || count($data['items']) > 50) avm_fail(400, 'datos');
    foreach ($data['items'] as $it) if (!is_array($it)) avm_fail(400, 'datos');

    // Máximo 20 envíos por hora desde la misma conexión.
    $pdo = avm_db();
    $ip = substr(hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|avm'), 0, 40);
    $pdo->prepare('DELETE FROM avm_rate WHERE t < ?')->execute([time() - 3600]);
    $n = $pdo->prepare('SELECT COUNT(*) FROM avm_rate WHERE k = ? AND t > ?');
    $n->execute([$ip, time() - 3600]);
    if ((int) $n->fetchColumn() >= 20) avm_fail(429, 'demasiados');
    $pdo->prepare('INSERT INTO avm_rate (k, t) VALUES (?, ?)')->execute([$ip, time()]);

    $ts = avm_put($path, $data);
    // Automatizaciones al instante y envío de correos (si falla, el cron lo reintenta).
    try { avm_run_engine($path); avm_send_outbox(5); } catch (Throwable $e) { error_log('AVM motor: ' . $e->getMessage()); }
    avm_json(['ok' => true, 'ts' => $ts]);
}

/* ---------- Administradores ---------- */
switch ($op) {
    case 'set':
        if (!is_array($data)) avm_fail(400, 'datos');
        avm_json(['ok' => true, 'ts' => avm_put($path, $data)]);
    case 'update':
        if (!is_array($data)) avm_fail(400, 'datos');
        $cur = avm_get($path);
        if ($cur === null) avm_fail(404, 'not_found');
        avm_json(['ok' => true, 'ts' => avm_put($path, array_merge($cur, $data))]);
    case 'delete':
        avm_json(['ok' => true, 'ts' => avm_del($path)]);
    case 'acquire':
        $holder = substr((string) ($req['holder'] ?? ''), 0, 60);
        $ttl = max(1000, min(120000, (int) ($req['ttlMs'] ?? 30000)));
        $cur = avm_get($path) ?? [];
        $free = empty($cur['holder']) || $cur['holder'] === $holder || ($cur['until'] ?? 0) < avm_now_ms();
        if ($free) avm_put($path, ['holder' => $holder, 'until' => avm_now_ms() + $ttl]);
        avm_json(['acquired' => $free]);
    default:
        avm_fail(400, 'op');
}
