<?php
/**
 * Entrada del sitio con SEO. Sirve index.html y le agrega, según la dirección, el título,
 * la descripción, la imagen para compartir, la URL canónica y los datos estructurados
 * (schema.org) de la página: inicio, /destino/<slug>, /hotel/<id>~<código>, /actividad/<id>~<código>,
 * /agencias, /traslados y /privacidad. La página sigue funcionando igual en el navegador.
 */
require __DIR__ . '/api/_lib.php';
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache');

$html = (string) file_get_contents(__DIR__ . '/index.html');
$site = avm_site_url();
$basePath = rtrim((string) (parse_url($site, PHP_URL_PATH) ?? ''), '/') . '/';
$e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$corto = function (string $t, int $n = 158): string { $t = trim(preg_replace('/\s+/', ' ', $t)); return mb_strlen($t) > $n ? rtrim(mb_substr($t, 0, $n - 1)) . '…' : $t; };

$content = avm_get('site/content') ?? [];
$contact = (array) ($content['contact'] ?? []);
$ruta = (string) ($_GET['ruta'] ?? '');
$id = (string) ($_GET['id'] ?? '');

$marca = 'AVM Operadores Mayoristas';
$seo = [
    'title' => 'Paquetes todo incluido y viajes completos | ' . $marca,
    'desc' => 'Paquetes con hotel, traslados y asistencia médica a Punta Cana, Cancún, Aruba, Curazao, Panamá y más. Cotiza en línea con AVM, operador mayorista desde 2014.',
    'path' => '', 'image' => $site . '/img/correo/portada.jpg', 'type' => 'website', 'robots' => 'index,follow', 'jsonld' => [], 'h1' => 'Viajes completos, listos para disfrutar',
];
$agencia = ['@type' => 'TravelAgency', 'name' => $marca, 'alternateName' => 'A volar por el mundo', 'url' => $site . '/', 'logo' => $site . '/img/logo-color.png',
    'image' => $site . '/img/correo/portada.jpg', 'email' => (string) ($contact['email'] ?? ''), 'foundingDate' => '2014', 'areaServed' => 'CO'];
if (!empty($contact['sales'][0])) $agencia['telephone'] = '+57 ' . $contact['sales'][0];
foreach ((array) ($contact['sedes'] ?? []) as $s) $agencia['address'][] = ['@type' => 'PostalAddress', 'addressLocality' => (string) ($s[0] ?? ''), 'streetAddress' => (string) ($s[1] ?? ''), 'addressCountry' => 'CO'];
$migas = fn(array $items) => ['@type' => 'BreadcrumbList', 'itemListElement' => array_map(fn($it, $i) => ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $it[0], 'item' => $site . '/' . $it[1]], $items, array_keys($items))];
$noEncontrado = false;

switch ($ruta) {
    case 'destino':
        $d = preg_match('/^[a-z0-9-]{1,80}$/', $id) ? avm_get('destinations/' . $id) : null;
        if (!$d || empty($d['active'])) { $noEncontrado = true; break; }
        $nombre = (string) ($d['name'] ?? $id);
        $seo['title'] = "Paquete a $nombre todo incluido, con traslados y asistencia | AVM";
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
        $seo['title'] = $h['name'] . ($h['city'] ? ', ' . $h['city'] : '') . ' | Hoteles AVM';
        $seo['desc'] = $corto($h['description'] ?: $h['name'] . ' en ' . $h['city'] . '. Cotiza con AVM tu viaje con hotel, traslados y asistencia.');
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
        $seo['title'] = $t['title'] . ($t['city'] ? ' en ' . $t['city'] : '') . ' | Tours AVM';
        $seo['desc'] = $corto($t['description'] ?: $t['title']);
        $seo['path'] = 'actividad/' . $id; $seo['h1'] = $t['title'];
        if (!empty($t['images'][0]['url'])) $seo['image'] = $t['images'][0]['url'];
        $seo['jsonld'][] = ['@type' => 'TouristTrip', 'name' => $t['title'], 'description' => $seo['desc'], 'image' => array_slice(array_column($t['images'], 'url'), 0, 5),
            'provider' => ['@type' => 'TravelAgency', 'name' => $marca, 'url' => $site . '/']];
        $seo['jsonld'][] = $migas([['Inicio', ''], [$t['title'], $seo['path']]]);
        break;
    case 'agencias':
        $seo['title'] = 'Agencias de viajes: vende con ' . $marca;
        $seo['desc'] = 'Tarifas preferenciales, plataforma de reservas 24/7 y asesores que acompañan cada venta. Activa tu agencia con AVM.';
        $seo['path'] = 'agencias'; $seo['h1'] = 'Agencias de viajes';
        break;
    case 'traslados':
        $seo['title'] = 'Traslados aeropuerto – hotel | ' . $marca;
        $seo['desc'] = 'Traslados en servicio regular entre el aeropuerto y tu hotel en Punta Cana, Cancún, Riviera Maya, Aruba, Curazao y Panamá.';
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
// Texto para quien no ejecuta JavaScript
$html = preg_replace('#(<div class="agency-bar">)#', '<noscript><h1>' . $e($seo['h1']) . '</h1><p>' . $e($seo['desc']) . '</p></noscript>$1', $html, 1);
echo $html;
