<?php
/**
 * Mapa del sitio para Google (/sitemap.xml): inicio, páginas fijas, cada destino visible y los
 * hoteles y actividades que el sitio muestra en cada destino.
 */
require __DIR__ . '/api/_lib.php';
header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');
$site = avm_site_url();
$hoy = date('Y-m-d');
$urls = [['', '1.0', 'weekly'], ['agencias', '0.6', 'monthly'], ['traslados', '0.5', 'monthly']];
foreach (avm_coll('destinations') as $slug => $d) {
    if (empty($d['active'])) continue;
    $urls[] = ['destino/' . $slug, '0.9', 'weekly'];
    // Hoteles y actividades de cada destino (de la caché de 24 horas; como mucho una consulta por destino al día)
    $tc = strtolower((string) (($d['tcCode'] ?? '') ?: (AVM_TCCODE[$slug] ?? '')));
    $g = AVM_HOTEL_GEO[$tc] ?? null;
    if (!$g) continue;
    $r = avm_lite_hoteles($g[0], $g[1], $g[2], 12);
    foreach ($r['hotels'] ?? [] as $h) $urls[] = ['hotel/' . rawurlencode($h['id']) . '~' . $tc, '0.6', 'monthly'];
    $a = avm_lite_actividades($g[0], $g[1], max(10, (int) round($g[2] / 1000) + 15), 10);
    foreach ($a['tours'] ?? [] as $t) $urls[] = ['actividad/' . rawurlencode($t['id']) . '~' . $tc, '0.5', 'monthly'];
}
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as [$path, $prio, $freq]) {
    echo '  <url><loc>' . htmlspecialchars($site . '/' . $path, ENT_XML1) . "</loc><lastmod>$hoy</lastmod><changefreq>$freq</changefreq><priority>$prio</priority></url>\n";
}
echo "</urlset>\n";
