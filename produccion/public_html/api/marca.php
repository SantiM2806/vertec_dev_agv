<?php
/**
 * Logos de la agencia como archivo de imagen (para correos, el ingreso y el ícono de la pestaña).
 * GET ?img=logo | logoDark | icon
 * El backoffice guarda las imágenes comprimidas en la base de datos (images/<id>); aquí se entregan como archivo.
 */
require __DIR__ . '/_nucleo.php';
avm_tenant();
session_write_close();
$cual = (string) ($_GET['img'] ?? 'logo');
if (!in_array($cual, ['logo', 'logoDark', 'icon'], true)) avm_fail(400, 'img');
$m = avm_marca();
$ref = (string) ($cual === 'icon' ? ($m['icon'] ?: $m['logo']) : ($cual === 'logoDark' ? ($m['logoDark'] ?: $m['logo']) : $m['logo']));
$data = '';
if (str_starts_with($ref, 'db:')) $data = (string) ((avm_get('images/' . substr($ref, 3)) ?? [])['data'] ?? '');
elseif (str_starts_with($ref, 'data:image/')) $data = $ref;
if (!preg_match('#^data:(image/(png|jpeg|webp|svg\+xml));base64,(.+)$#s', $data, $mm)) { http_response_code(404); exit; }
$bin = base64_decode($mm[3], true);
if ($bin === false) { http_response_code(404); exit; }
header('Content-Type: ' . $mm[1]);
header('Cache-Control: public, max-age=' . (isset($_GET['v']) ? 31536000 : 600));
header('X-Content-Type-Options: nosniff');
if ($mm[2] === 'svg+xml') header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'");
echo $bin;
