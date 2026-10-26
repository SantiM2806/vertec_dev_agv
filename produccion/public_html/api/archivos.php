<?php
/**
 * Tarifarios en PDF de la agencia (solo administradores). Los archivos quedan fuera de la carpeta pública:
 * produccion/avm-archivos/<agencia>/<id>.pdf, y sus datos en tarifarios/<id>.
 * POST multipart: archivo (PDF), categoria (alojamiento | actividad | transporte | circuito)  → sube
 * POST JSON { op: "borrar", id }                                                              → borra
 * GET ?id=<id>                                                                                → abre el PDF
 */
require __DIR__ . '/_nucleo.php';
avm_tenant();
if (!avm_is_admin()) avm_fail(401, 'sesion');
session_write_close();
const AVM_PDF_MAX = 25 * 1024 * 1024;
$dir = dirname(__DIR__, 2) . '/avm-archivos/' . avm_tenant()['slug'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = (string) ($_GET['id'] ?? '');
    $doc = preg_match('/^[a-f0-9]{20}$/', $id) ? avm_get('tarifarios/' . $id) : null;
    $file = $dir . '/' . $id . '.pdf';
    if (!$doc || !is_file($file)) avm_fail(404, 'no_existe');
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . str_replace(['"', "\r", "\n"], '', (string) ($doc['nombre'] ?? 'tarifario.pdf')) . '"');
    header('Content-Length: ' . filesize($file));
    header('Cache-Control: private, no-store');
    header('X-Content-Type-Options: nosniff');
    readfile($file);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') avm_fail(405, 'metodo');
avm_check_origin();

if (str_starts_with((string) ($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json')) {
    $req = json_decode((string) file_get_contents('php://input', false, null, 0, 4096), true);
    $id = (string) ($req['id'] ?? '');
    if (($req['op'] ?? '') !== 'borrar' || !preg_match('/^[a-f0-9]{20}$/', $id)) avm_fail(400, 'datos');
    @unlink($dir . '/' . $id . '.pdf');
    avm_del('tarifarios/' . $id);
    avm_json(['ok' => true]);
}

$f = $_FILES['archivo'] ?? null;
if (!$f || ($f['error'] ?? 1) !== UPLOAD_ERR_OK) avm_json(['ok' => false, 'error' => ($f['error'] ?? 0) === UPLOAD_ERR_INI_SIZE || ($f['error'] ?? 0) === UPLOAD_ERR_FORM_SIZE ? 'El archivo pesa más de lo que permite el servidor.' : 'No llegó el archivo.'], 400);
if ($f['size'] > AVM_PDF_MAX) avm_json(['ok' => false, 'error' => 'El PDF pesa más de 25 MB.'], 400);
$h = fopen($f['tmp_name'], 'rb'); $magia = fread($h, 5); fclose($h);
if ($magia !== '%PDF-') avm_json(['ok' => false, 'error' => 'Solo se admiten archivos PDF.'], 400);
$cat = (string) ($_POST['categoria'] ?? '');
if (!in_array($cat, AVM_PROP_TIPOS, true)) avm_fail(400, 'categoria');
if (!is_dir($dir)) @mkdir($dir, 0750, true);
$id = bin2hex(random_bytes(10));
if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $id . '.pdf')) avm_json(['ok' => false, 'error' => 'No se pudo guardar el archivo en el servidor.'], 500);
$nombre = preg_replace('/[^\pL\pN ._()-]+/u', '_', basename((string) $f['name'])) ?: 'tarifario.pdf';
$doc = ['nombre' => mb_substr($nombre, 0, 120), 'categoria' => $cat, 'tamano' => (int) $f['size'], 'subido' => avm_iso()];
avm_put('tarifarios/' . $id, $doc);
avm_json(['ok' => true, 'id' => $id] + $doc);
