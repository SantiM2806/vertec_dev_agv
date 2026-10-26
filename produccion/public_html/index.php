<?php
/**
 * Entrada del sitio con SEO. Sirve index.html y le agrega, según la dirección, el título,
 * la descripción, la imagen para compartir, la URL canónica y los datos estructurados
 * (schema.org) de la página: inicio, /destino/<slug>, /hotel/<id>~<código>, /actividad/<id>~<código>,
 * /traslados y /privacidad. También pone la marca de la agencia (colores, tipografía y logo) para que
 * la página cargue ya con ella. Sin agencia (dirección de la plataforma) muestra la portada de la plataforma.
 */
require __DIR__ . '/api/_nucleo.php';
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache');
$e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');

$T = avm_tenant_opt();
if (!$T || ($T['estado'] ?? 'activa') !== 'activa') {
    $np = avm_plataforma_nombre();
    if ($T) http_response_code(503); elseif (avm_host() !== strtolower(trim((string) (avm_config()['base_domain'] ?? ''), '.')) && avm_host() !== 'www.' . strtolower(trim((string) (avm_config()['base_domain'] ?? ''), '.')) && !avm_es_local(avm_host())) http_response_code(404);
    $titulo = $T ? 'Sitio no disponible' : (http_response_code() === 404 ? 'Agencia no encontrada' : $np);
    $texto = $T ? 'Este sitio no está disponible en este momento. Intenta más tarde.'
        : (http_response_code() === 404 ? 'No encontramos una agencia en esta dirección.' : 'Plataforma de venta de viajes en marca blanca para agencias: buscador con precios en línea, cotizaciones, CRM y campañas, con tu marca y tus productos.');
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . $e($titulo) . '</title>'
        . '<style>body{margin:0;min-height:100vh;display:grid;place-items:center;font:16px/1.6 system-ui,sans-serif;background:#F3F4F1;color:#12161B;padding:16px}main{max-width:560px;display:grid;gap:14px}h1{font-size:32px;margin:0}a{color:inherit}.btn{display:inline-block;padding:12px 18px;border-radius:12px;background:#12161B;color:#fff;text-decoration:none;font-weight:600;justify-self:start}'
        . '@media (prefers-color-scheme:dark){body{background:#0E1114;color:#EEF0F2}.btn{background:#EEF0F2;color:#0E1114}}</style></head><body><main><h1>' . $e($titulo) . '</h1><p>' . $e($texto) . '</p>'
        . (!$T && http_response_code() !== 404 ? '<a class="btn" href="plataforma.php">Ingresar al panel</a>' : '') . '</main></body></html>';
    exit;
}
avm_tenant();
$B = avm_marca();

$html = (string) file_get_contents(__DIR__ . '/index.html');
$site = avm_site_url();
$basePath = rtrim((string) (parse_url($site, PHP_URL_PATH) ?? ''), '/') . '/';
$corto = function (string $t, int $n = 158): string { $t = trim(preg_replace('/\s+/', ' ', $t)); return mb_strlen($t) > $n ? rtrim(mb_substr($t, 0, $n - 1)) . '…' : $t; };

$content = avm_get('site/content') ?? [];
$contact = (array) ($content['contact'] ?? []);
$ruta = (string) ($_GET['ruta'] ?? '');
$id = (string) ($_GET['id'] ?? '');

$marca = (string) $B['name'];
$txt = (array) ($content['txt'] ?? []);
$plano = fn($s) => trim(str_replace('*', '', (string) $s));
$seo = [
    'title' => ($txt['seoTitle'] ?? '') ?: (($plano($txt['heroTitle'] ?? '') ?: 'Viajes y paquetes turísticos') . ' | ' . $marca),
    'desc' => $corto(($txt['seoDesc'] ?? '') ?: ($plano($txt['heroSub'] ?? '') ?: "Cotiza en línea tu viaje con $marca: hoteles, traslados, actividades y asistencia médica.")),
    'path' => '', 'image' => $site . '/img/correo/portada.jpg', 'type' => 'website', 'robots' => 'index,follow', 'jsonld' => [], 'h1' => $plano($txt['heroTitle'] ?? '') ?: $marca,
];
$agencia = array_filter(['@type' => 'TravelAgency', 'name' => $marca, 'legalName' => $B['legal'] ?: null, 'url' => $site . '/', 'logo' => avm_logo_url() ?: null,
    'image' => $site . '/img/correo/portada.jpg', 'email' => (string) ($contact['email'] ?? '') ?: null, 'areaServed' => 'CO']);
if (!empty($contact['sales'][0])) $agencia['telephone'] = '+57 ' . $contact['sales'][0];
foreach ((array) ($contact['sedes'] ?? []) as $s) $agencia['address'][] = ['@type' => 'PostalAddress', 'addressLocality' => (string) ($s[0] ?? ''), 'streetAddress' => (string) ($s[1] ?? ''), 'addressCountry' => 'CO'];
$migas = fn(array $items) => ['@type' => 'BreadcrumbList', 'itemListElement' => array_map(fn($it, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $it[0], 'item' => $site . '/' . $it[1]], $items, array_keys($items))];
$noEncontrado = false;

switch ($ruta) {
    case 'destino':
        $d = preg_match('/^[a-z0-9-]{1,80}$/', $id) ? avm_get('destinations/' . $id) : null;
        if (!$d || empty($d['active'])) { $noEncontrado = true; break; }
        $nombre = (string) ($d['name'] ?? $id);
        $seo['title'] = "Paquete a $nombre todo incluido, con traslados y asistencia | $marca";
        $seo['desc'] = $corto(($d['desc'] ?? '') ?: "Viaja a $nombre con hotel, traslados aeropuerto – hotel – aeropuerto y asistencia médica.");
        $seo['path'] = 'destino/' . $id; $seo['h1'] = "Paquete a $nombre";
        $seo['image'] = $site . '/img/correo/' . avm_foto_destino($nombre) . '.jpg';
        $seo['jsonld'][] = ['@type' => 'TouristTrip', 'name' => "Paquete a $nombre", 'description' => $seo['desc'], 'touristType' => 'Vacaciones',
            'image' => $seo['image'], 'itinerary' => ['@type' => 'Place', 'name' => $nombre, 'address' => ['@type' => 'PostalAddress', 'addressCountry' => (string) ($d['country'] ?? '')]],
            'provider' => ['@type' => 'TravelAgency', 'name' => $marca, 'url' => $site . '/']];
        $seo['jsonld'][] = $migas([['Inicio', ''], [$nombre, $seo['path']]]);
        break;
    case 'hotel':
        [$hid, $tc] = array_pad(explode('~', $id, 2), 2, '');
        $r = avm_lite_hotel($hid);
        if (!$r['ok']) { $noEncontrado = true; break; }
        $h = $r['hotel'];
        $seo['title'] = $h['name'] . ($h['city'] ? ', ' . $h['city'] : '') . ' | ' . $marca;
        $seo['desc'] = $corto($h['description'] ?: $h['name'] . ' en ' . $h['city'] . ". Cotiza con $marca tu viaje con hotel, traslados y asistencia.");
        $seo['path'] = 'hotel/' . $id; $seo['h1'] = $h['name']; $seo['type'] = 'website';
        if (!empty($h['images'][0]['url'])) $seo['image'] = $h['images'][0]['url'];
        $hotel = ['@type' => 'Hotel', 'name' => $h['name'], 'description' => $seo['desc'], 'image' => array_slice(array_column($h['images'], 'url'), 0, 5),
            'address' => ['@type' => 'PostalAddress', 'streetAddress' => $h['address'], 'addressLocality' => $h['city'], 'addressCountry' => $h['country']]];
        if ($h['lat'] && $h['lng']) $hotel['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => $h['lat'], 'longitude' => $h['lng']];
        if ($h['stars']) $hotel['starRating'] = ['@type' => 'Rating', 'ratingValue' => $h['stars']];
        if ($h['facilities']) $hotel['amenityFeature'] = array_map(fn($f) => ['@type' => 'LocationFeatureSpecification', 'name' => $f, 'value' => true], array_slice($h['facilities'], 0, 12));
        $seo['jsonld'][] = $hotel;
        $seo['jsonld'][] = $migas([['Inicio', ''], [$h['name'], $seo['path']]]);
        break;
    case 'actividad':
        [$aid, $tc] = array_pad(explode('~', $id, 2), 2, '');
        $r = avm_lite_actividad($aid);
        if (!$r['ok']) { $noEncontrado = true; break; }
        $t = $r['tour'];
        $seo['title'] = $t['title'] . ($t['city'] ? ' en ' . $t['city'] : '') . ' | ' . $marca;
        $seo['desc'] = $corto($t['description'] ?: $t['title']);
        $seo['path'] = 'actividad/' . $id; $seo['h1'] = $t['title'];
        if (!empty($t['images'][0]['url'])) $seo['image'] = $t['images'][0]['url'];
        $seo['jsonld'][] = ['@type' => 'TouristTrip', 'name' => $t['title'], 'description' => $seo['desc'], 'image' => array_slice(array_column($t['images'], 'url'), 0, 5),
            'provider' => ['@type' => 'TravelAgency', 'name' => $marca, 'url' => $site . '/']];
        $seo['jsonld'][] = $migas([['Inicio', ''], [$t['title'], $seo['path']]]);
        break;
    case 'traslados':
        $seo['title'] = 'Traslados aeropuerto – hotel | ' . $marca;
        $seo['desc'] = "Traslados entre el aeropuerto y tu hotel con $marca.";
        $seo['path'] = 'traslados'; $seo['h1'] = 'Traslados aeropuerto – hotel';
        break;
    case 'privacidad':
        $seo['title'] = 'Política de tratamiento de datos | ' . $marca;
        $seo['path'] = 'privacidad'; $seo['robots'] = 'noindex,follow'; $seo['h1'] = 'Política de tratamiento de datos';
        break;
}
if ($noEncontrado) { http_response_code(404); $seo['robots'] = 'noindex,follow'; $seo['title'] = 'Página no encontrada | ' . $marca; $seo['path'] = ''; }
if ($ruta === '' ) $seo['jsonld'][] = $agencia;

$canonical = $site . '/' . $seo['path'];
$ld = $seo['jsonld'] ? '<script type="application/ld+json">' . json_encode(['@context' => 'https://schema.org', '@graph' => array_values($seo['jsonld'])], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) . '</script>' : '';
$head = '<base href="' . $e($basePath) . '">' . "\n";
$extra = "\n" . '<link rel="canonical" href="' . $e($canonical) . '">'
    . "\n" . '<meta name="robots" content="' . $e($seo['robots']) . '">'
    . "\n" . '<meta property="og:type" content="' . $e($seo['type']) . '"><meta property="og:site_name" content="' . $e($marca) . '"><meta property="og:locale" content="es_CO">'
    . "\n" . '<meta property="og:title" content="' . $e($seo['title']) . '"><meta property="og:description" content="' . $e($seo['desc']) . '">'
    . "\n" . '<meta property="og:url" content="' . $e($canonical) . '"><meta property="og:image" content="' . $e($seo['image']) . '">'
    . "\n" . '<meta name="twitter:card" content="summary_large_image">' . "\n" . $ld . "\n";

// Título y descripción
$html = preg_replace('#<title>.*?</title>#s', '<title>' . $e($seo['title']) . '</title>', $html, 1);
$html = preg_replace('#<meta name="description" content="[^"]*">#', '<meta name="description" content="' . $e($seo['desc']) . '">', $html, 1);
// <base> va antes que cualquier dirección relativa; el resto, antes de cerrar <head> (o antes del primer <style>)
$html = preg_replace('#<head>#', '<head>' . "\n" . $head, $html, 1);
$html = preg_replace('#(<style>)#', $extra . '$1', $html, 1);
// Marca de la agencia después del CSS base (para que sus colores manden): tipografía, ícono y datos para la página
$cab = avm_marca_head($B);
$html = preg_replace_callback('#</style>#', fn($m) => $m[0] . "\n" . $cab, $html, 1);
// Texto para quien no ejecuta JavaScript
$html = preg_replace('#(<header class="top">)#', '<noscript><h1>' . $e($seo['h1']) . '</h1><p>' . $e($seo['desc']) . '</p></noscript>$1', $html, 1);
echo $html;
