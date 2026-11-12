<?php
// Firma electrónica de la propuesta.
// GET  → estado de las firmas (sin IP ni navegador) y si cada parte necesita código.
// POST → firma de una parte: {parte, nombre, documento, imagen (PNG en base64), acepto, codigo}.
// Cada firma queda en firmas.json con fecha, IP, navegador y la huella SHA-256 de index.html (la versión exacta firmada).
// Una parte que ya firmó no se puede reemplazar desde la página.
// Opcional: firma-codigos.php con un código por parte; tras 5 códigos errados se bloquea 15 minutos.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
date_default_timezone_set('America/Bogota');

function salir($code, $datos) { http_response_code($code); echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); exit; }
function leer($archivo) { if (!is_file($archivo)) return []; $j = json_decode((string) file_get_contents($archivo), true); return is_array($j) ? $j : []; }
function publico($f) { return $f ? ['nombre' => $f['nombre'], 'documento' => $f['documento'], 'fecha' => $f['fecha'], 'imagen' => $f['imagen'], 'huella' => $f['huella'], 'metodo' => $f['metodo'] ?? ''] : null; }

$PARTES = ['avm', 'desarrollador'];
$dir = __DIR__;
$archivo = "$dir/firmas.json";
$huella = is_file("$dir/index.html") ? hash_file('sha256', "$dir/index.html") : '';
$codigos = is_file("$dir/firma-codigos.php") ? (require "$dir/firma-codigos.php") : [];
if (!is_array($codigos)) $codigos = [];
$pideCodigo = fn($p) => isset($codigos[$p]) && trim((string) $codigos[$p]) !== '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $f = leer($archivo); $out = [];
    foreach ($PARTES as $p) $out[$p] = publico($f[$p] ?? null);
    salir(200, ['ok' => true, 'huella_actual' => $huella, 'firmas' => $out, 'codigo' => array_combine($PARTES, array_map($pideCodigo, $PARTES))]);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') salir(405, ['ok' => false, 'error' => 'Método no permitido']);

$in = json_decode((string) file_get_contents('php://input', false, null, 0, 600000), true);
if (!is_array($in)) salir(400, ['ok' => false, 'error' => 'Datos inválidos']);

$parte = (string) ($in['parte'] ?? '');
if (!in_array($parte, $PARTES, true)) salir(400, ['ok' => false, 'error' => 'Parte inválida']);
$nombre = trim(preg_replace('/\s+/u', ' ', (string) ($in['nombre'] ?? '')));
$documento = trim((string) ($in['documento'] ?? ''));
if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 120) salir(400, ['ok' => false, 'error' => 'Escribe el nombre completo']);
if (!preg_match('/^[0-9A-Za-z.\- ]{3,40}$/', $documento)) salir(400, ['ok' => false, 'error' => 'Escribe un número de C.C. o NIT válido']);
if (($in['acepto'] ?? false) !== true) salir(400, ['ok' => false, 'error' => 'Debes aceptar los términos de la propuesta']);
$metodo = ($in['metodo'] ?? '') === 'nombre' ? 'nombre' : 'dibujo';

$imagen = (string) ($in['imagen'] ?? '');
if (strpos($imagen, 'data:image/png;base64,') !== 0 || strlen($imagen) > 500000) salir(400, ['ok' => false, 'error' => 'La firma no es válida']);
$png = base64_decode(substr($imagen, 22), true);
if ($png === false || substr($png, 0, 8) !== "\x89PNG\r\n\x1a\n") salir(400, ['ok' => false, 'error' => 'La firma no es válida']);

// Código de firma (opcional), con bloqueo por intentos
if ($pideCodigo($parte)) {
    $ai = "$dir/firma-intentos.json"; $ahora = time();
    $intentos = array_values(array_filter(leer($ai), fn($t) => is_int($t) && $t > $ahora - 900));
    if (count($intentos) >= 5) salir(429, ['ok' => false, 'error' => 'Demasiados intentos. Espera 15 minutos.']);
    if (!hash_equals(trim((string) $codigos[$parte]), trim((string) ($in['codigo'] ?? '')))) {
        $intentos[] = $ahora; file_put_contents($ai, json_encode($intentos), LOCK_EX); sleep(1);
        salir(403, ['ok' => false, 'error' => 'Código de firma incorrecto']);
    }
}

// Guardar sin pisar una firma existente
$fp = fopen($archivo, 'c+');
if (!$fp) salir(500, ['ok' => false, 'error' => 'No se pudo guardar la firma: revisa los permisos de la carpeta']);
flock($fp, LOCK_EX);
$actual = json_decode((string) stream_get_contents($fp), true);
if (!is_array($actual)) $actual = [];
if (!empty($actual[$parte])) { flock($fp, LOCK_UN); fclose($fp); salir(409, ['ok' => false, 'error' => 'Esta parte ya firmó la propuesta']); }
$actual[$parte] = [
    'nombre' => $nombre, 'documento' => $documento, 'fecha' => date('c'), 'imagen' => $imagen, 'metodo' => $metodo,
    'huella' => $huella, 'ip' => $_SERVER['REMOTE_ADDR'] ?? '', 'navegador' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 300),
];
ftruncate($fp, 0); rewind($fp);
fwrite($fp, json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
fflush($fp); flock($fp, LOCK_UN); fclose($fp);
salir(200, ['ok' => true, 'firma' => publico($actual[$parte])]);
