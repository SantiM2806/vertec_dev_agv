<?php
// robots.txt del sitio (/robots.txt). Toma la dirección pública de avm-config.php.
require __DIR__ . '/api/_lib.php';
header('Content-Type: text/plain; charset=utf-8');
echo "User-agent: *\nAllow: /\nDisallow: /api/\nDisallow: /ingresar.php\nDisallow: /instalar.php\nDisallow: /baja.php\nDisallow: /index.html\n\nSitemap: " . avm_site_url() . "/sitemap.xml\n";
