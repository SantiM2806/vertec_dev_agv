<?php
// Guarda el avance del tablero de seguimiento en estado.json.
// Solo acepta cambios con la clave de edición; el hash de la clave vive en clave.php (no se sube al repositorio).
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function salir($code, $datos) { http_response_code($code); echo json_encode($datos, JSON_UNESCAPED_UNICODE); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') salir(405, ['ok' => false, 'error' => 'Método no permitido']);

$crudo = file_get_contents('php://input', false, null, 0, 300000);
$entrada = json_decode($crudo, true);
if (!is_array($entrada)) salir(400, ['ok' => false, 'error' => 'Datos inválidos']);
$clave = isset($entrada['clave']) ? (string) $entrada['clave'] : '';

$archivoClave = __DIR__ . '/clave.php';

// Solo verificar la clave (al entrar al modo edición), sin guardar nada
if (!empty($entrada['verificar'])) {
    if (!is_file($archivoClave)) {
        if (is_file(__DIR__ . '/estado.json')) salir(500, ['ok' => false, 'error' => 'Falta clave.php en el servidor']);
        if (mb_strlen($clave) < 8) salir(400, ['ok' => false, 'error' => 'Primera vez: la clave debe tener al menos 8 caracteres']);
        salir(200, ['ok' => true, 'nueva' => true]);
    }
    $h = require $archivoClave;
    if (is_string($h) && $clave !== '' && password_verify($clave, $h)) salir(200, ['ok' => true]);
    sleep(2); salir(403, ['ok' => false, 'error' => 'Clave incorrecta']);
}

if (!is_file($archivoClave)) {
    // Primer uso: la primera clave que se guarda queda como clave de edición.
    // Solo se permite mientras no exista estado.json (justo después de subir la carpeta).
    if (is_file(__DIR__ . '/estado.json')) salir(500, ['ok' => false, 'error' => 'Falta clave.php en el servidor']);
    if (mb_strlen($clave) < 8) salir(400, ['ok' => false, 'error' => 'La clave debe tener al menos 8 caracteres']);
    $nuevo = "<?php return " . var_export(password_hash($clave, PASSWORD_DEFAULT), true) . ";\n";
    if (file_put_contents($archivoClave, $nuevo, LOCK_EX) === false) salir(500, ['ok' => false, 'error' => 'No se pudo crear clave.php: revisa los permisos de la carpeta']);
}
$hash = require $archivoClave;
if (!is_string($hash) || $hash === '') salir(500, ['ok' => false, 'error' => 'clave.php no es válido']);

if ($clave === '' || !password_verify($clave, $hash)) { sleep(2); salir(403, ['ok' => false, 'error' => 'Clave incorrecta']); }

$e = isset($entrada['estado']) && is_array($entrada['estado']) ? $entrada['estado'] : null;
if (!$e) salir(400, ['ok' => false, 'error' => 'Falta el estado']);

// Solo se guardan las llaves esperadas, con valores de texto cortos
$limpio = ['tareas' => [], 'avm' => [], 'notas' => [], 'actualizado' => gmdate('c')];
foreach (['tareas' => ['curso', 'hecho'], 'avm' => ['listo']] as $grupo => $validos) {
    foreach ((array) ($e[$grupo] ?? []) as $id => $v) {
        if (preg_match('/^[A-Za-z0-9_-]{1,40}$/', (string) $id) && in_array($v, $validos, true)) $limpio[$grupo][$id] = $v;
    }
}
foreach ((array) ($e['notas'] ?? []) as $id => $v) {
    if (preg_match('/^[A-Za-z0-9_-]{1,40}$/', (string) $id) && is_string($v) && $v !== '') $limpio['notas'][$id] = mb_substr($v, 0, 1000);
}
foreach (['tareas', 'avm', 'notas'] as $g) if (!$limpio[$g]) $limpio[$g] = new stdClass();

$destino = __DIR__ . '/estado.json';
$tmp = $destino . '.tmp';
$json = json_encode($limpio, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
if (file_put_contents($tmp, $json, LOCK_EX) === false || !rename($tmp, $destino)) salir(500, ['ok' => false, 'error' => 'No se pudo escribir estado.json']);
salir(200, ['ok' => true, 'actualizado' => $limpio['actualizado']]);
