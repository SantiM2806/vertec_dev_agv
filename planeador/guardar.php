<?php
// Guarda el avance del tablero de seguimiento en estado.json.
// Solo acepta cambios con el PIN de edición (6 dígitos). El hash del PIN vive en clave.php, que no va al repositorio.
// Primer uso: si no existe clave.php, el primer PIN que se use queda como PIN de edición.
// Para cambiar el PIN: borra clave.php en el servidor y entra con el PIN nuevo.
// Tras 5 intentos fallidos se bloquea la edición 15 minutos.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function salir($code, $datos) { http_response_code($code); echo json_encode($datos, JSON_UNESCAPED_UNICODE); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') salir(405, ['ok' => false, 'error' => 'Método no permitido']);

$crudo = file_get_contents('php://input', false, null, 0, 300000);
$entrada = json_decode($crudo, true);
if (!is_array($entrada)) salir(400, ['ok' => false, 'error' => 'Datos inválidos']);
$pin = isset($entrada['clave']) ? (string) $entrada['clave'] : '';

// ---------- Bloqueo por intentos fallidos ----------
$archivoIntentos = __DIR__ . '/intentos.json';
$intentos = is_file($archivoIntentos) ? (json_decode((string) file_get_contents($archivoIntentos), true) ?: []) : [];
$ahora = time();
$intentos = array_values(array_filter($intentos, fn($t) => is_int($t) && $t > $ahora - 900));
if (count($intentos) >= 5) salir(429, ['ok' => false, 'error' => 'Demasiados intentos. Espera 15 minutos.']);
function fallo($intentos, $archivo) {
    $intentos[] = time();
    file_put_contents($archivo, json_encode($intentos), LOCK_EX);
    sleep(1);
    $quedan = max(0, 5 - count($intentos));
    salir(403, ['ok' => false, 'error' => $quedan ? "PIN incorrecto. Te quedan $quedan intentos." : 'Demasiados intentos. Espera 15 minutos.']);
}

// ---------- PIN ----------
$archivoClave = __DIR__ . '/clave.php';
$nuevo = !is_file($archivoClave);
if ($nuevo && !preg_match('/^\d{6}$/', $pin)) salir(400, ['ok' => false, 'error' => 'El PIN debe tener 6 dígitos']);
if (!$nuevo) {
    $hash = require $archivoClave;
    if (!is_string($hash) || $hash === '') salir(500, ['ok' => false, 'error' => 'clave.php no es válido']);
    if ($pin === '' || !password_verify($pin, $hash)) fallo($intentos, $archivoIntentos);
    if (is_file($archivoIntentos)) @unlink($archivoIntentos);
}

// Solo verificar (al pasar a modo edición)
if (!empty($entrada['verificar'])) salir(200, $nuevo ? ['ok' => true, 'nueva' => true] : ['ok' => true]);

// Primer guardado: fija el PIN
if ($nuevo) {
    $php = "<?php return " . var_export(password_hash($pin, PASSWORD_DEFAULT), true) . ";\n";
    if (file_put_contents($archivoClave, $php, LOCK_EX) === false) salir(500, ['ok' => false, 'error' => 'No se pudo crear clave.php: revisa los permisos de la carpeta']);
}

// ---------- Guardar el estado ----------
$e = isset($entrada['estado']) && is_array($entrada['estado']) ? $entrada['estado'] : null;
if (!$e) salir(400, ['ok' => false, 'error' => 'Falta el estado']);

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
