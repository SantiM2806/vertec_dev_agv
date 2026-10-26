<?php
/**
 * Fotos guardadas en la base de datos (images/<id>) como archivo de imagen, para las fichas de productos propios.
 * GET ?id=<id de la imagen>
 */
require __DIR__ . '/_nucleo.php';
avm_tenant();
session_write_close();
$id = (string) ($_GET['id'] ?? '');
if (!preg_match('/^[A-Za-z0-9_-]{2,60}$/', $id)) { http_response_code(400); exit; }
$data = (string) ((avm_get('images/' . $id) ?? [])['data'] ?? '');
if (!preg_match('#^data:(image/(png|jpeg|webp));base64,(.+)$#s', $data, $m) || ($bin = base64_decode($m[3], true)) === false) { http_response_code(404); exit; }
header('Content-Type: ' . $m[1]);
header('Cache-Control: public, max-age=31536000, immutable');
header('X-Content-Type-Options: nosniff');
echo $bin;
