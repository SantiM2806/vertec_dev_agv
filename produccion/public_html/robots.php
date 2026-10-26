<?php
// robots.txt del sitio (/robots.txt). Toma la dirección pública de avm-config.php.
require __DIR__ . '/api/_nucleo.php';
if (!avm_tenant_opt()) { header("Content-Type: text/plain; charset=utf-8"); exit("User-agent: *
Allow: /
"); }
header('Content-Type: text/plain; charset=utf-8');
echo "User-agent: *\nAllow: /\nDisallow: /api/\nDisallow: /ingresar.php
Disallow: /plataforma.php\nDisallow: /instalar.php\nDisallow: /baja.php\nDisallow: /index.html\n\nSitemap: " . avm_site_url() . "/sitemap.xml\n";
