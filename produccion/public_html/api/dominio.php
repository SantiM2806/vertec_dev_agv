<?php
/**
 * Pregunta de Caddy antes de pedir un certificado HTTPS para una dirección (on_demand_tls → ask).
 * GET ?domain=agencia.tuplataforma.com → 200 si es la plataforma o una agencia existente; 404 si no.
 * Así nadie puede hacer que el servidor pida certificados para direcciones que no son de la plataforma.
 */
require __DIR__ . '/_nucleo.php';
header('Cache-Control: no-store');
$h = strtolower(trim((string) ($_GET['domain'] ?? ''), '.'));
if (!preg_match('/^[a-z0-9.-]{3,253}$/', $h)) { http_response_code(400); exit; }
$base = strtolower(trim((string) (avm_config()['base_domain'] ?? ''), '.'));
$ok = $base !== '' && ($h === $base || $h === 'www.' . $base);
if (!$ok && $base !== '' && str_ends_with($h, '.' . $base)) $ok = (bool) avm_tenant_fila('slug', substr($h, 0, -strlen($base) - 1));
if (!$ok) $ok = (bool) (avm_tenant_fila('domain', $h) ?? avm_tenant_fila('domain', preg_replace('/^www\./', '', $h)));
http_response_code($ok ? 200 : 404);
echo $ok ? 'ok' : 'no';
