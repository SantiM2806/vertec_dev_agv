<?php
/**
 * Conexión con LiteAPI (hoteles y actividades). La llave vive en avm-config.php ('liteapi_key')
 * y nunca llega al navegador. Cada respuesta se guarda en caché en el servidor para no gastar
 * consultas: listas por 24 horas y fichas por 7 días.
 *
 * Las funciones devuelven datos ya limpios y en español, con solo lo que muestra el sitio.
 * Precios en línea según la búsqueda del cliente; un asesor confirma y reserva.
 */
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === '_liteapi.php') { http_response_code(404); exit; }

/** Coordenadas por código de destino (mismas que usa la página): [lat, lng, radio en metros]. */
const AVM_HOTEL_GEO = [
    'puj' => [18.6826, -68.418, 20000], 'pcm' => [20.6296, -87.0739, 25000], 'cun' => [21.118, -86.759, 15000], 'aua' => [12.57, -70.045, 12000],
    'cua' => [12.1091, -68.9316, 20000], 'pan-1' => [8.9824, -79.5199, 10000], 'ctg' => [10.4236, -75.548, 8000], 'sai-123' => [12.5847, -81.7006, 8000],
    'smc' => [11.2408, -74.199, 15000], 'mde' => [6.2088, -75.567, 6000], 'bog' => [4.665, -74.054, 8000], 'mia' => [25.7907, -80.13, 10000],
    'mco' => [28.418, -81.581, 15000], 'lim' => [-12.1211, -77.0297, 6000], 'cuz' => [-13.517, -71.9785, 4000], 'mad' => [40.4168, -3.7038, 5000], 'bue' => [-34.6037, -58.3816, 6000],
];
const AVM_TCCODE = ['punta-cana' => 'puj', 'riviera-maya' => 'pcm', 'aruba' => 'aua', 'panama' => 'pan-1', 'curazao' => 'cua', 'cancun' => 'cun'];

function avm_lite_get(string $path, array $params, int $ttl): array {
    $key = (string) avm_cfg('liteapi_key', '');
    if ($key === '') return ['ok' => false, 'error' => 'sin_configurar', 'status' => 503];
    $base = rtrim((string) avm_cfg('liteapi_base', 'https://api.liteapi.travel/v3.0'), '/');
    $url = $base . $path . ($params ? '?' . http_build_query($params) : '');
    $dir = dirname(__DIR__, 2) . '/avm-cache';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    // Cada cuenta de LiteAPI tiene su caché (las comisiones cambian de una a otra)
    $file = $dir . '/lite-' . hash('sha256', $key . '|' . $url) . '.json';
    if (is_file($file) && time() - filemtime($file) < $ttl) {
        $j = json_decode((string) file_get_contents($file), true);
        if (is_array($j)) return ['ok' => true, 'data' => $j, 'cache' => true];
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 12, CURLOPT_HTTPHEADER => ['X-API-Key: ' . $key, 'Accept: application/json']]);
    $body = curl_exec($ch); $status = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    $j = json_decode((string) $body, true);
    if ($body === false || $status !== 200 || !is_array($j)) {
        // Si falla pero hay una copia vieja, se usa la copia
        if (is_file($file) && is_array($old = json_decode((string) file_get_contents($file), true))) return ['ok' => true, 'data' => $old, 'cache' => true];
        return ['ok' => false, 'error' => $status === 401 || $status === 403 ? 'sin_acceso' : 'proveedor', 'status' => $status ?: 502];
    }
    @file_put_contents($file, json_encode($j, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    return ['ok' => true, 'data' => $j, 'cache' => false];
}

/** Consulta POST con caché (tarifas: 3 horas). */
function avm_lite_post(string $path, array $body, int $ttl): array {
    $key = (string) avm_cfg('liteapi_key', '');
    if ($key === '') return ['ok' => false, 'error' => 'sin_configurar', 'status' => 503];
    $base = rtrim((string) avm_cfg('liteapi_base', 'https://api.liteapi.travel/v3.0'), '/');
    $json = json_encode($body, JSON_UNESCAPED_SLASHES);
    $dir = dirname(__DIR__, 2) . '/avm-cache';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    $file = $dir . '/lite-post-' . hash('sha256', $key . '|' . $path . '|' . $json) . '.json';
    if (is_file($file) && time() - filemtime($file) < $ttl && is_array($j = json_decode((string) file_get_contents($file), true))) return ['ok' => true, 'data' => $j];
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $json,
        CURLOPT_HTTPHEADER => ['X-API-Key: ' . $key, 'Accept: application/json', 'Content-Type: application/json']]);
    $resp = curl_exec($ch); $status = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    $j = json_decode((string) $resp, true);
    if ($resp === false || $status !== 200 || !is_array($j)) return ['ok' => false, 'error' => $status === 401 || $status === 403 ? 'sin_acceso' : 'proveedor', 'status' => $status ?: 502];
    @file_put_contents($file, json_encode($j, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    return ['ok' => true, 'data' => $j];
}

/**
 * Precio al cliente según la configuración del backoffice (system/tarifas):
 * (neto × (1 + markup) + fee fijo) ÷ (1 − (fee general % + fee bancario % + comisión del operador %)).
 * Es la misma fórmula del simulador del backoffice (precioCliente en index.html).
 */
function avm_tarifas_cfg(): array {
    static $c = [];
    $t = avm_tid();
    if (!isset($c[$t])) { try { $c[$t] = avm_get('system/tarifas') ?? []; } catch (Throwable $e) { $c[$t] = []; } }
    return $c[$t];
}
function avm_precio(float $neto, string $prod, bool $conFijo = true): float {
    return $neto <= 0 ? $neto : avm_liquidacion($neto, $prod, $conFijo)['total'];
}
/**
 * Desglose del precio al cliente (solo para administradores):
 * tarifa del proveedor + markup + fee fijo, y sobre el total el fee general, el bancario y la comisión del operador.
 */
function avm_liquidacion(float $neto, string $prod, bool $conFijo = true): array {
    $c = avm_tarifas_cfg();
    $n = fn(string $k) => max(0.0, min(300.0, (float) ($c[$k] ?? 0)));
    $fee = $n('fee'); $banco = $n('banco'); $op = $n('operador');
    $pct = min(60.0, $fee + $banco + $op) / 100;
    $mk = $n('markup_' . $prod); $fijo = $conFijo ? $n('fijo') : 0.0;
    $bruto = ($neto * (1 + $mk / 100) + $fijo) / (1 - $pct);
    $total = !empty($c['redondear']) ? ceil($bruto) : round($bruto, 2);
    $esc = $pct > 0 ? min(1, ($fee + $banco + $op) / 100 / $pct) : 1; // si la suma pasa del 60 %, se reparte en proporción
    $r = fn(float $x) => round($x, 2);
    $l = ['neto' => $r($neto), 'markupPct' => $mk, 'markup' => $r($neto * $mk / 100), 'fijo' => $r($fijo),
        'feePct' => $fee, 'fee' => $r($bruto * $fee / 100 * $esc), 'bancoPct' => $banco, 'banco' => $r($bruto * $banco / 100 * $esc),
        'operadorPct' => $op, 'operador' => $r($bruto * $op / 100 * $esc), 'total' => $total];
    $resto = $r($total - $l['neto'] - $l['markup'] - $l['fijo'] - $l['fee'] - $l['banco'] - $l['operador']);
    $l['redondeo'] = 0.0;
    if (!empty($c['redondear'])) $l['redondeo'] = $resto;
    else { // centavos sueltos del redondeo a 2 decimales: van al concepto porcentual más grande
        $k = $op >= $banco && $op >= $fee ? 'operador' : ($banco >= $fee ? 'banco' : 'fee');
        if ($l[$k] > 0) $l[$k] = $r($l[$k] + $resto); else $l['markup'] = $r($l['markup'] + $resto);
    }
    $l['margen'] = $r($l['markup'] + $l['fijo'] + $l['fee'] + $l['redondeo']);
    return $l;
}
/** Suma la comisión de LiteAPI, los impuestos a pagar en el hotel y el precio público sugerido de una tarifa. */
function avm_lite_px(array $px, array $rate): array {
    foreach ((array) ($rate['commission'] ?? []) as $c) $px['com'] += (float) ($c['amount'] ?? 0);
    foreach ((array) ($rate['retailRate']['taxesAndFees'] ?? []) as $t) if (empty($t['included'])) $px['taxOut'] += (float) ($t['amount'] ?? 0);
    $px['ssp'] += (float) ($rate['retailRate']['suggestedSellingPrice'][0]['amount'] ?? 0);
    return $px;
}
/** Liquidación de un hotel: neto del proveedor, comisión de LiteAPI y lo que suma AVM. */
function avm_liq_hotel(float $tarifa, array $px): array {
    $l = avm_liquidacion($tarifa, 'hoteles');
    $com = round(min($px['com'], $tarifa), 2);
    return ['netoProv' => round($tarifa - $com, 2), 'comLite' => $com] + $l
        + ['ganancia' => round($l['margen'] + $com, 2), 'taxOut' => round($px['taxOut'], 2), 'ssp' => round($px['ssp'], 2)];
}
/** Quita las liquidaciones de una respuesta para quien no es administrador. */
function avm_sin_liq(array $a): array {
    unset($a['liq']);
    foreach ($a as $k => $v) if (is_array($v)) $a[$k] = avm_sin_liq($v);
    return $a;
}

const AVM_REGIMEN = ['RO' => 'Solo alojamiento', 'BB' => 'Desayuno incluido', 'HB' => 'Media pensión', 'FB' => 'Pensión completa', 'AI' => 'Todo incluido', 'AL' => 'Todo incluido'];

/**
 * Tarifas en línea de uno o varios hoteles para unas fechas y personas.
 * Devuelve por hotel: precio total más bajo, noches y hasta 8 opciones (habitación, régimen, reembolsable, total).
 */
function avm_lite_tarifas(array $ids, string $in, string $out, int $rooms, int $adults, array $kids, int $max = 8): array {
    $ids = array_values(array_unique(array_filter($ids, fn($x) => preg_match('/^[A-Za-z0-9_-]{2,40}$/', $x))));
    if (!$ids || count($ids) > 20) return ['ok' => false, 'error' => 'ids', 'status' => 400];
    $a = strtotime($in); $b = strtotime($out);
    if (!$a || !$b || $a < strtotime('today') || $b <= $a || ($b - $a) / 86400 > 30) return ['ok' => false, 'error' => 'fechas', 'status' => 400];
    $rooms = max(1, min(4, $rooms)); $adults = max($rooms, min(16, $adults)); $kids = array_slice(array_map(fn($k) => max(0, min(17, (int) $k)), $kids), 0, 6);
    $occ = [];
    for ($i = 0; $i < $rooms; $i++) $occ[] = ['adults' => intdiv($adults, $rooms) + ($i < $adults % $rooms ? 1 : 0), 'children' => $i === 0 ? array_values($kids) : []];
    $r = avm_lite_post('/hotels/rates', ['hotelIds' => $ids, 'occupancies' => $occ, 'currency' => 'USD', 'guestNationality' => 'CO',
        'checkin' => date('Y-m-d', $a), 'checkout' => date('Y-m-d', $b), 'timeout' => 8, 'maxRatesPerHotel' => 20, 'roomMapping' => true], 3 * 3600);
    if (!$r['ok']) return $r;
    $noches = (int) round(($b - $a) / 86400);
    $res = [];
    foreach ((array) ($r['data']['data'] ?? []) as $h) {
        $ops = [];
        foreach ((array) ($h['roomTypes'] ?? []) as $rt) {
            $tot = 0.0; $nombre = ''; $reg = ''; $reemb = true; $cur = 'USD'; $hab = (string) ($rt['mappedRoomId'] ?? ''); $px = ['com' => 0.0, 'taxOut' => 0.0, 'ssp' => 0.0];
            foreach ((array) ($rt['rates'] ?? []) as $rate) {
                $tot += (float) ($rate['retailRate']['total'][0]['amount'] ?? 0); $px = avm_lite_px($px, $rate);
                $cur = (string) ($rate['retailRate']['total'][0]['currency'] ?? $cur);
                $nombre = $nombre ?: (string) ($rate['name'] ?? '');
                $hab = $hab ?: (string) ($rate['mappedRoomId'] ?? '');
                $reg = $reg ?: (AVM_REGIMEN[$rate['boardType'] ?? ''] ?? (string) ($rate['boardName'] ?? ''));
                if (($rate['cancellationPolicies']['refundableTag'] ?? '') !== 'RFN') $reemb = false;
            }
            if ($tot <= 0 || $nombre === '') continue;
            $liq = avm_liq_hotel($tot, $px);
            $ops[] = ['room' => $nombre, 'roomId' => $hab, 'board' => $reg, 'refundable' => $reemb, 'total' => $liq['total'], 'currency' => $cur, 'liq' => $liq];
        }
        if (!$ops) continue;
        usort($ops, fn($x, $y) => $x['total'] <=> $y['total']);
        $unicas = [];
        foreach ($ops as $o) { $k = $o['room'] . '|' . $o['board'] . '|' . (int) $o['refundable']; if (!isset($unicas[$k])) $unicas[$k] = $o; }
        $ops = array_slice(array_values($unicas), 0, $max);
        $res[(string) $h['hotelId']] = ['from' => $ops[0]['total'], 'currency' => $ops[0]['currency'], 'board' => $ops[0]['board'], 'nights' => $noches, 'options' => $ops];
    }
    return ['ok' => true, 'rates' => $res, 'nights' => $noches];
}

/** Texto seguro: quita etiquetas y deja párrafos separados por línea en blanco. */
function avm_lite_texto(?string $html, int $max = 6000): string {
    $t = (string) $html;
    $t = preg_replace('#<\s*(br|/p|/li|/h\d|/div)\s*/?>#i', "\n", $t);
    $t = html_entity_decode(strip_tags($t), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $t = preg_replace("/[ \t]+/", ' ', $t);
    $t = preg_replace("/\n\s*\n+/", "\n\n", trim($t));
    return mb_substr($t, 0, $max);
}
function avm_lite_url(?string $u): string { return preg_match('#^https://#', (string) $u) ? (string) $u : ''; }

/** Lista de hoteles alrededor de un punto. */
function avm_lite_hoteles(float $lat, float $lng, int $radius, int $limit): array {
    $r = avm_lite_get('/data/hotels', ['latitude' => $lat, 'longitude' => $lng, 'radius' => $radius, 'limit' => $limit * 2, 'language' => 'es', 'minRating' => 7], 86400);
    if (!$r['ok']) return $r;
    $out = [];
    foreach ((array) ($r['data']['data'] ?? []) as $h) {
        $photo = avm_lite_url($h['thumbnail'] ?? '') ?: avm_lite_url($h['main_photo'] ?? '');
        if (empty($h['id']) || empty($h['name']) || !$photo) continue;
        $out[] = ['id' => (string) $h['id'], 'name' => (string) $h['name'], 'stars' => (float) ($h['stars'] ?? 0), 'rating' => (float) ($h['rating'] ?? 0),
            'reviews' => (int) ($h['reviewCount'] ?? 0), 'address' => (string) ($h['address'] ?? ''), 'photo' => $photo];
    }
    usort($out, fn($a, $b) => [$b['rating'], $b['reviews']] <=> [$a['rating'], $a['reviews']]);
    return ['ok' => true, 'hotels' => array_slice($out, 0, $limit)];
}

/** Ficha completa de un hotel. */
function avm_lite_hotel(string $id): array {
    if (!preg_match('/^[A-Za-z0-9_-]{2,40}$/', $id)) return ['ok' => false, 'error' => 'id', 'status' => 400];
    $r = avm_lite_get('/data/hotel', ['hotelId' => $id, 'language' => 'es'], 7 * 86400);
    if (!$r['ok']) return $r;
    $d = (array) ($r['data']['data'] ?? []);
    if (empty($d['name'])) return ['ok' => false, 'error' => 'no_existe', 'status' => 404];
    $imgs = [];
    $lista = (array) ($d['hotelImages'] ?? []);
    usort($lista, fn($a, $b) => [empty($a['defaultImage']), $a['order'] ?? 99] <=> [empty($b['defaultImage']), $b['order'] ?? 99]); // primero la principal
    foreach ($lista as $im) {
        $u = avm_lite_url($im['urlHd'] ?? '') ?: avm_lite_url($im['url'] ?? '');
        if ($u) $imgs[] = ['url' => $u, 'thumb' => avm_lite_url($im['url'] ?? '') ?: $u, 'caption' => (string) ($im['caption'] ?? '')];
        if (count($imgs) >= 24) break;
    }
    if (!$imgs && ($u = avm_lite_url($d['main_photo'] ?? ''))) $imgs[] = ['url' => $u, 'thumb' => $u, 'caption' => ''];
    $fac = [];
    foreach ((array) ($d['facilities'] ?? []) as $f) if (!empty($f['name'])) $fac[] = (string) $f['name'];
    if (!$fac) foreach ((array) ($d['hotelFacilities'] ?? []) as $f) if (is_string($f) && $f !== '') $fac[] = $f;
    $rooms = [];
    foreach ((array) ($d['rooms'] ?? []) as $rm) {
        if (empty($rm['roomName'])) continue;
        $fotos = [];
        foreach ((array) ($rm['photos'] ?? []) as $ph) {
            $u = avm_lite_url($ph['hd_url'] ?? '') ?: avm_lite_url($ph['url'] ?? '');
            if ($u) $fotos[] = ['url' => $u, 'thumb' => avm_lite_url($ph['url'] ?? '') ?: $u, 'caption' => (string) ($ph['imageDescription'] ?? '')];
            if (count($fotos) >= 12) break;
        }
        $serv = [];
        foreach ((array) ($rm['roomAmenities'] ?? []) as $a) if (!empty($a['name'])) $serv[] = (string) $a['name'];
        $camas = [];
        foreach ((array) ($rm['bedTypes'] ?? []) as $b) if (!empty($b['bedType'])) $camas[] = trim(($b['quantity'] ?? 1) . ' ' . $b['bedType']);
        $rooms[] = ['name' => (string) $rm['roomName'], 'size' => !empty($rm['roomSizeSquare']) ? $rm['roomSizeSquare'] . ' ' . ($rm['roomSizeUnit'] ?? 'm²') : '',
            'id' => (string) ($rm['id'] ?? ''), 'max' => (int) ($rm['maxOccupancy'] ?? 0), 'beds' => implode(', ', $camas), 'photo' => $fotos[0]['url'] ?? '', 'photos' => $fotos,
            'amenities' => array_slice(array_values(array_unique($serv)), 0, 16), 'description' => avm_lite_texto($rm['description'] ?? '', 600)];
        if (count($rooms) >= 30) break;
    }
    $poi = [];
    foreach ((array) ($d['poi'] ?? []) as $p) {
        if (empty($p['name'])) continue;
        $poi[] = ['name' => (string) $p['name'], 'km' => isset($p['distanceKm']) ? round((float) $p['distanceKm'], 1) : null];
        if (count($poi) >= 8) break;
    }
    $s = (array) ($d['sentiment_analysis'] ?? []);
    $cats = [];
    foreach ((array) ($s['categories'] ?? []) as $c) if (!empty($c['name']) && isset($c['rating'])) $cats[] = ['name' => (string) $c['name'], 'rating' => (float) $c['rating']];
    $t = (array) ($d['checkinCheckoutTimes'] ?? []);
    return ['ok' => true, 'hotel' => [
        'id' => (string) $d['id'], 'name' => (string) $d['name'], 'stars' => (float) ($d['starRating'] ?? 0), 'rating' => (float) ($d['rating'] ?? 0),
        'reviews' => (int) ($d['reviewCount'] ?? 0), 'address' => (string) ($d['address'] ?? ''), 'city' => (string) ($d['city'] ?? ''),
        'country' => strtoupper((string) ($d['country'] ?? '')), 'chain' => (string) ($d['chain'] ?? ''), 'type' => (string) ($d['hotelType'] ?? ''),
        'lat' => (float) ($d['location']['latitude'] ?? 0), 'lng' => (float) ($d['location']['longitude'] ?? 0),
        'description' => avm_lite_texto($d['hotelDescription'] ?? ''), 'important' => avm_lite_texto($d['hotelImportantInformation'] ?? '', 2500),
        'checkin' => (string) ($t['checkin_start'] ?? $t['checkin'] ?? ''), 'checkout' => (string) ($t['checkout'] ?? ''),
        'images' => $imgs, 'facilities' => array_slice(array_values(array_unique($fac)), 0, 40), 'rooms' => $rooms, 'poi' => $poi,
        'pros' => array_slice(array_values(array_filter(array_map('strval', (array) ($s['pros'] ?? [])))), 0, 6), 'categories' => array_slice($cats, 0, 6),
        'airport' => (string) ($d['airportCode'] ?? ''),
    ]];
}

/** Actividades (tours) alrededor de un punto. Requiere acceso a Experiences en LiteAPI. */
function avm_lite_actividades(float $lat, float $lng, int $radiusKm, int $limit): array {
    $r = avm_lite_get('/experiences/tours', ['language' => 'es', 'currency' => 'USD', 'location' => ['latitude' => $lat, 'longitude' => $lng, 'radiusKm' => $radiusKm],
        'pagination' => ['limit' => min(40, $limit * 2), 'offset' => 0]], 86400);
    if (!$r['ok']) return $r;
    $out = [];
    foreach ((array) ($r['data']['data']['tours'] ?? []) as $t) {
        $foto = avm_lite_url($t['pictures'][0]['url'] ?? '');
        if (empty($t['id']) || empty($t['title']) || !$foto) continue;
        $out[] = ['id' => (string) $t['id'], 'title' => (string) $t['title'], 'photo' => $foto, 'rating' => (float) ($t['overallRating'] ?? 0),
            'reviews' => (int) ($t['numberOfRatings'] ?? 0), 'duration' => avm_lite_duracion((array) ($t['durations'] ?? [])),
            'category' => (string) ($t['categories'][0]['name'] ?? ''), 'pickup' => !empty($t['hasPickUp']),
            'price' => avm_precio((float) ($t['price']['amount'] ?? 0), 'actividades', false), 'currency' => (string) ($t['price']['currency'] ?? ''), 'priceNote' => (string) ($t['priceDescription'] ?? '')];
    }
    usort($out, fn($a, $b) => [$b['rating'] >= 4.5, $b['reviews']] <=> [$a['rating'] >= 4.5, $a['reviews']]);
    return ['ok' => true, 'tours' => array_slice($out, 0, $limit)];
}
function avm_lite_duracion(array $ds): string {
    $u = ['minutes' => ['minuto', 'minutos'], 'hours' => ['hora', 'horas'], 'days' => ['día', 'días']];
    $p = [];
    foreach ($ds as $d) { $v = (float) ($d['value'] ?? 0); $n = $u[$d['unit'] ?? ''] ?? null; if ($v && $n) $p[] = rtrim(rtrim(number_format($v, 1, ',', ''), '0'), ',') . ' ' . ($v == 1 ? $n[0] : $n[1]); }
    return implode(' – ', array_slice(array_unique($p), 0, 2));
}

/** Ficha completa de una actividad. */
function avm_lite_actividad(string $id): array {
    if (!preg_match('/^[0-9A-Za-z_-]{1,40}$/', $id)) return ['ok' => false, 'error' => 'id', 'status' => 400];
    $r = avm_lite_get('/experiences/tours/' . rawurlencode($id), ['language' => 'es', 'currency' => 'USD'], 7 * 86400);
    if (!$r['ok']) return $r;
    $t = (array) ($r['data']['data'] ?? []);
    if (empty($t['title'])) return ['ok' => false, 'error' => 'no_existe', 'status' => 404];
    $fotos = [];
    foreach ((array) ($t['pictures'] ?? []) as $p) { if ($u = avm_lite_url($p['url'] ?? '')) $fotos[] = ['url' => $u, 'thumb' => $u, 'caption' => '']; if (count($fotos) >= 16) break; }
    $itin = [];
    foreach ((array) ($t['itineraries'] ?? []) as $dia) foreach ((array) ($dia['items'] ?? []) as $it) {
        if (empty($it['title'])) continue;
        $itin[] = ['title' => (string) $it['title'], 'text' => avm_lite_texto($it['description'] ?? '', 400), 'time' => (string) ($it['startTime'] ?? ''), 'duration' => avm_lite_duracion([(array) ($it['duration'] ?? [])])];
        if (count($itin) >= 12) break 2;
    }
    $lst = fn($v) => array_values(array_filter(array_map(fn($x) => avm_lite_texto((string) $x, 300), is_array($v) ? $v : ($v ? [$v] : []))));
    $loc = (array) ($t['locations'][0] ?? []);
    return ['ok' => true, 'tour' => [
        'id' => (string) $t['id'], 'title' => (string) $t['title'], 'description' => avm_lite_texto($t['description'] ?? ''),
        'highlights' => $lst($t['highlights'] ?? []), 'included' => avm_lite_texto($t['included'] ?? '', 1500), 'excluded' => avm_lite_texto($t['excluded'] ?? '', 1500),
        'meeting' => avm_lite_texto($t['placeDetails'] ?? '', 800), 'info' => avm_lite_texto($t['additionalInfo'] ?? '', 1200), 'bring' => $lst($t['itemsToBring'] ?? []),
        'notFor' => $lst($t['notSuitableFor'] ?? []), 'rating' => (float) ($t['overallRating'] ?? 0), 'reviews' => (int) ($t['numberOfRatings'] ?? 0),
        'duration' => avm_lite_duracion((array) ($t['durations'] ?? [])), 'pickup' => !empty($t['hasPickUp']), 'images' => $fotos, 'itinerary' => $itin,
        'cancel' => avm_lite_texto($t['cancellationPolicy']['description'] ?? '', 400), 'category' => (string) ($t['categories'][0]['name'] ?? ''),
        'city' => (string) ($loc['city'] ?? $loc['name'] ?? ''), 'country' => (string) ($loc['country'] ?? ''),
        'price' => avm_precio((float) ($t['price']['amount'] ?? 0), 'actividades', false), 'currency' => (string) ($t['price']['currency'] ?? ''), 'priceNote' => (string) ($t['priceDescription'] ?? ''),
        'lat' => (float) ($t['coordinates']['lat'] ?? 0), 'lng' => (float) ($t['coordinates']['long'] ?? 0),
    ]];
}

/* =================== Búsqueda en vivo (buscador del sitio) =================== */

/** Autocompletado de destinos (lugares de LiteAPI / Google Places). */
function avm_lite_lugares(string $q): array {
    $q = trim(mb_substr($q, 0, 60));
    if (mb_strlen($q) < 2) return ['ok' => true, 'places' => []];
    $r = avm_lite_get('/data/places', ['textQuery' => $q, 'language' => 'es'], 7 * 86400);
    if (!$r['ok']) return $r;
    $out = [];
    foreach ((array) ($r['data']['data'] ?? []) as $pl) {
        if (empty($pl['placeId']) || empty($pl['displayName'])) continue;
        $out[] = ['id' => (string) $pl['placeId'], 'name' => (string) $pl['displayName'], 'address' => (string) ($pl['formattedAddress'] ?? ''), 'types' => array_slice((array) ($pl['types'] ?? []), 0, 3)];
        if (count($out) >= 8) break;
    }
    return ['ok' => true, 'places' => $out];
}
/** Detalle de un lugar (para obtener coordenadas). */
function avm_lite_lugar(string $id): ?array {
    if (!preg_match('/^[A-Za-z0-9_-]{5,300}$/', $id)) return null;
    $r = avm_lite_get('/data/places/' . rawurlencode($id), ['language' => 'es'], 30 * 86400);
    $d = $r['ok'] ? (array) ($r['data']['data'] ?? []) : [];
    if (!isset($d['location']['latitude'])) return null;
    return ['name' => (string) ($d['displayName'] ?? ''), 'lat' => (float) $d['location']['latitude'], 'lng' => (float) $d['location']['longitude']];
}
/** Si llega texto en vez de un lugar elegido, usa el primer resultado. */
function avm_lite_resolver_lugar(string $id, string $q): string {
    if ($id !== '') return $id;
    $r = avm_lite_lugares($q);
    return (string) ($r['places'][0]['id'] ?? '');
}

/** Autocompletado de aeropuertos (vuelos). */
function avm_lite_aeropuertos(string $q): array {
    $q = trim(mb_substr($q, 0, 40));
    if (mb_strlen($q) < 2) return ['ok' => true, 'airports' => []];
    $r = avm_lite_get('/data/flights/airports', ['q' => $q], 30 * 86400);
    if (!$r['ok']) return $r;
    $out = [];
    foreach ((array) ($r['data']['data'] ?? []) as $grp) foreach ((array) ($grp['airports'] ?? []) as $a) {
        if (empty($a['iata'])) continue;
        $out[] = ['iata' => (string) $a['iata'], 'name' => (string) ($a['name'] ?? ''), 'city' => (string) ($a['city'] ?? ''), 'country' => (string) ($a['country'] ?? '')];
        if (count($out) >= 8) break 2;
    }
    return ['ok' => true, 'airports' => $out];
}

function avm_lite_ocupacion(int $rooms, int $adults, array $kids): array {
    $rooms = max(1, min(4, $rooms)); $adults = max($rooms, min(16, $adults));
    $kids = array_values(array_slice(array_map(fn($k) => max(0, min(17, (int) $k)), $kids), 0, 6));
    $occ = [];
    for ($i = 0; $i < $rooms; $i++) $occ[] = ['adults' => intdiv($adults, $rooms) + ($i < $adults % $rooms ? 1 : 0), 'children' => $i === 0 ? $kids : []];
    return $occ;
}
function avm_lite_fechas_ok(string $in, string $out, int $max = 30): bool {
    $a = strtotime($in); $b = strtotime($out);
    return $a && $b && $a >= strtotime('today') && $b > $a && ($b - $a) / 86400 <= $max;
}

/** Tipos de alojamiento de LiteAPI (id → nombre en español). */
function avm_lite_tipos(): array {
    static $t = null;
    if ($t !== null) return $t;
    $r = avm_lite_get('/data/hotelTypes', [], 30 * 86400);
    $es = ['apartment' => 'Apartamento', 'aparthotel' => 'Apartahotel', 'apart-hotel' => 'Apartahotel', 'resort' => 'Resort', 'hostel' => 'Hostal', 'motel' => 'Motel',
        'bed and breakfast' => 'Alojamiento y desayuno', 'b&b' => 'Alojamiento y desayuno', 'guest house' => 'Casa de huéspedes', 'guesthouse' => 'Casa de huéspedes',
        'villa' => 'Villa', 'holiday home' => 'Casa vacacional', 'vacation home' => 'Casa vacacional', 'condo' => 'Apartamento en condominio', 'inn' => 'Posada',
        'lodge' => 'Lodge', 'cottage' => 'Cabaña', 'chalet' => 'Chalé', 'campsite' => 'Camping', 'boat' => 'Barco', 'capsule' => 'Hotel cápsula', 'hotel' => 'Hotel'];
    $t = [];
    foreach ((array) ($r['ok'] ? ($r['data']['data'] ?? []) : []) as $x) {
        $n = (string) ($x['name'] ?? ''); $l = strtolower($n); $nom = 'Otro';
        foreach ($es as $k => $v) if (str_contains($l, $k)) { $nom = $v; break; }
        if (isset($x['id'])) $t[(int) $x['id']] = $nom;
    }
    return $t;
}
/** Datos estáticos de varios hoteles (tipo, estrellas, coordenadas). */
function avm_lite_datos_hoteles(array $ids): array {
    $out = [];
    foreach (array_chunk($ids, 50) as $grupo) {
        $r = avm_lite_get('/data/hotels', ['hotelIds' => implode(',', $grupo), 'limit' => 50], 7 * 86400);
        foreach ((array) ($r['ok'] ? ($r['data']['data'] ?? []) : []) as $h) if (!empty($h['id'])) $out[(string) $h['id']] = $h;
    }
    return $out;
}
function avm_km(float $a, float $b, float $c, float $d): float {
    $r = M_PI / 180; $x = sin(($c - $a) * $r / 2) ** 2 + cos($a * $r) * cos($c * $r) * sin(($d - $b) * $r / 2) ** 2;
    return 6371 * 2 * atan2(sqrt($x), sqrt(1 - $x));
}
/** Si el cliente eligió un hotel en el autocompletado, devuelve su id de LiteAPI. */
function avm_lite_hotel_de_lugar(string $placeId, string $nombre, array $busq = []): string {
    if (!preg_match('/^[A-Za-z0-9_-]{5,300}$/', $placeId)) return '';
    // Primero entre los hoteles CON tarifa para esas fechas (el catálogo puede tener varias fichas del mismo hotel)
    if (!empty($busq['in']) && !empty($busq['out'])) {
        $t = avm_lite_buscar_hoteles($placeId, $busq['in'], $busq['out'], (int) ($busq['rooms'] ?? 1), (int) ($busq['adults'] ?? 2), (array) ($busq['kids'] ?? []));
        $id = $t['ok'] ? avm_lite_mejor_nombre((array) $t['hotels'], $nombre) : '';
        if ($id !== '') return $id;
    }
    $r = avm_lite_get('/data/hotels', ['placeId' => $placeId, 'limit' => 20], 7 * 86400);
    return avm_lite_mejor_nombre((array) ($r['ok'] ? ($r['data']['data'] ?? []) : []), $nombre);
}
/** Hotel de la lista cuyo nombre más se parece al buscado (o '' si ninguno se parece). */
function avm_lite_mejor_nombre(array $lista, string $nombre): string {
    if (!$lista) return '';
    $n = avm_norm($nombre); $mejor = ''; $pts = -1;
    foreach ($lista as $h) {
        $hn = avm_norm((string) ($h['name'] ?? ''));
        if ($hn === '') continue;
        similar_text($n, $hn, $pct);
        if (str_contains($hn, $n) || str_contains($n, $hn)) $pct += 50;
        if ($pct > $pts) { $pts = $pct; $mejor = (string) $h['id']; }
    }
    return $pts >= 60 ? $mejor : '';
}

/** Hoteles con precio para un destino, fechas y ocupación, con lo necesario para filtrar. */
function avm_lite_buscar_hoteles(string $placeId, string $in, string $out, int $rooms, int $adults, array $kids): array {
    if ($placeId === '') return ['ok' => false, 'error' => 'destino', 'status' => 400];
    if (!avm_lite_fechas_ok($in, $out)) return ['ok' => false, 'error' => 'fechas', 'status' => 400];
    $r = avm_lite_post('/hotels/rates', ['placeId' => $placeId, 'occupancies' => avm_lite_ocupacion($rooms, $adults, $kids), 'currency' => 'USD', 'guestNationality' => 'CO',
        'checkin' => date('Y-m-d', strtotime($in)), 'checkout' => date('Y-m-d', strtotime($out)), 'timeout' => 12, 'maxRatesPerHotel' => 20, 'limit' => 100, 'includeHotelData' => true], 1800);
    if (!$r['ok']) return $r;
    $info = [];
    foreach ((array) ($r['data']['hotels'] ?? []) as $h) if (!empty($h['id'])) $info[(string) $h['id']] = $h;
    $noches = max(1, (int) round((strtotime($out) - strtotime($in)) / 86400));
    $res = [];
    foreach ((array) ($r['data']['data'] ?? []) as $h) {
        $id = (string) ($h['hotelId'] ?? ''); $hi = $info[$id] ?? [];
        if ($id === '' || empty($hi['name'])) continue;
        // Una opción por combinación de régimen y cancelación: la más barata
        $ops = [];
        foreach ((array) ($h['roomTypes'] ?? []) as $rt) {
            $tot = 0.0; $nombre = ''; $cod = ''; $reemb = true; $px = ['com' => 0.0, 'taxOut' => 0.0, 'ssp' => 0.0];
            foreach ((array) ($rt['rates'] ?? []) as $rate) {
                $tot += (float) ($rate['retailRate']['total'][0]['amount'] ?? 0); $px = avm_lite_px($px, $rate);
                $nombre = $nombre ?: (string) ($rate['name'] ?? '');
                $cod = $cod ?: strtoupper((string) ($rate['boardType'] ?? ''));
                if (($rate['cancellationPolicies']['refundableTag'] ?? '') !== 'RFN') $reemb = false;
            }
            if ($tot <= 0 || $nombre === '') continue;
            $cod = $cod === 'AL' ? 'AI' : ($cod === 'BI' ? 'BB' : $cod);
            $k = $cod . '|' . (int) $reemb;
            if (!isset($ops[$k]) || $tot < $ops[$k]['neto']) $ops[$k] = ['neto' => $tot, 'px' => $px, 'room' => $nombre, 'boardCode' => $cod, 'board' => AVM_REGIMEN[$cod] ?? $cod, 'refundable' => $reemb];
        }
        if (!$ops) continue;
        $ops = array_values($ops);
        foreach ($ops as &$o) { $o['liq'] = avm_liq_hotel($o['neto'], $o['px']); $o['total'] = $o['liq']['total']; unset($o['neto'], $o['px']); }
        unset($o);
        usort($ops, fn($a, $b) => $a['total'] <=> $b['total']);
        $m = $ops[0];
        $res[$id] = ['id' => $id, 'name' => (string) $hi['name'], 'photo' => avm_lite_url($hi['thumbnail'] ?? '') ?: avm_lite_url($hi['main_photo'] ?? ''),
            'stars' => (float) ($hi['stars'] ?? 0), 'rating' => (float) ($hi['rating'] ?? 0), 'reviews' => (int) ($hi['review_count'] ?? $hi['reviewCount'] ?? 0),
            'address' => (string) ($hi['address'] ?? ''), 'city' => (string) ($hi['city_name'] ?? ''), 'type' => '', 'km' => null,
            'total' => $m['total'], 'perNight' => round($m['total'] / $noches, 2), 'room' => $m['room'], 'board' => $m['board'], 'boardCode' => $m['boardCode'], 'refundable' => $m['refundable'], 'liq' => $m['liq'], 'opts' => $ops];
    }
    // Tipo de alojamiento, estrellas y distancia al centro del destino
    if ($res) {
        $tipos = avm_lite_tipos(); $datos = avm_lite_datos_hoteles(array_keys($res)); $centro = avm_lite_lugar($placeId);
        foreach ($res as $id => &$x) {
            $d = $datos[$id] ?? [];
            if (isset($d['hotelTypeId'])) $x['type'] = $tipos[(int) $d['hotelTypeId']] ?? 'Otro';
            if (!$x['stars'] && !empty($d['stars'])) $x['stars'] = (float) $d['stars'];
            if (!$x['reviews'] && !empty($d['reviewCount'])) $x['reviews'] = (int) $d['reviewCount'];
            if ($centro && isset($d['latitude'], $d['longitude'])) $x['km'] = round(avm_km($centro['lat'], $centro['lng'], (float) $d['latitude'], (float) $d['longitude']), 1);
        }
        unset($x);
    }
    $res = array_values($res);
    usort($res, fn($a, $b) => $a['total'] <=> $b['total']);
    return ['ok' => true, 'hotels' => $res, 'nights' => $noches, 'currency' => 'USD'];
}

/** Vuelos con precio para una ruta, fechas y pasajeros. */
function avm_lite_buscar_vuelos(string $from, string $to, string $d1, string $d2, int $adults, array $kids, string $cabin): array {
    $from = strtoupper($from); $to = strtoupper($to);
    if (!preg_match('/^[A-Z]{3}$/', $from) || !preg_match('/^[A-Z]{3}$/', $to) || $from === $to) return ['ok' => false, 'error' => 'ruta', 'status' => 400];
    $a = strtotime($d1);
    if (!$a || $a < strtotime('today')) return ['ok' => false, 'error' => 'fechas', 'status' => 400];
    $legs = [['origin' => $from, 'destination' => $to, 'date' => date('Y-m-d', $a), 'direction' => 'OUTBOUND']];
    if ($d2 !== '') {
        $b = strtotime($d2);
        if (!$b || $b < $a) return ['ok' => false, 'error' => 'fechas', 'status' => 400];
        $legs[] = ['origin' => $to, 'destination' => $from, 'date' => date('Y-m-d', $b), 'direction' => 'INBOUND'];
    }
    $kids = array_values(array_slice(array_map(fn($k) => max(2, min(11, (int) $k)), $kids), 0, 6));
    $body = ['legs' => $legs, 'adults' => max(1, min(9, $adults)), 'children' => count($kids), 'infants' => 0, 'currency' => 'USD', 'country' => 'CO',
        'cabinClass' => in_array($cabin, ['ECONOMY', 'PREMIUM_ECONOMY', 'BUSINESS', 'FIRST'], true) ? $cabin : 'ECONOMY', 'sort' => ['sortBy' => 'price', 'sortOrder' => 'asc']];
    if ($kids) $body['childrenAges'] = $kids;
    $r = avm_lite_post('/flights/rates', $body, 600);
    if (!$r['ok']) return $r;
    $out = [];
    foreach ((array) ($r['data']['data'] ?? []) as $grp) foreach ((array) ($grp['journeys'] ?? []) as $j) {
        $of = (array) ($j['offers'][0] ?? []);
        $tot = (float) ($of['pricing']['display']['total'] ?? 0);
        if ($tot <= 0) continue;
        $tramos = [];
        foreach (['OUTBOUND', 'INBOUND'] as $dir) {
            $segs = array_values(array_filter((array) ($j['segments'] ?? []), fn($s) => ($s['direction'] ?? 'OUTBOUND') === $dir));
            if (!$segs) continue;
            $f = $segs[0]; $l = $segs[count($segs) - 1];
            $dur = 0; foreach ((array) ($j['legDurations'] ?? []) as $ld) if (($ld['direction'] ?? '') === $dir) $dur = (int) ($ld['duration']['minutes'] ?? 0);
            $tramos[] = ['dir' => $dir, 'from' => (string) ($f['originCode'] ?? ''), 'to' => (string) ($l['destinationCode'] ?? ''), 'dep' => (string) ($f['departureTime'] ?? ''), 'arr' => (string) ($l['arrivalTime'] ?? ''),
                'stops' => count($segs) - 1, 'minutes' => $dur, 'via' => array_map(fn($s) => (string) ($s['destinationCode'] ?? ''), array_slice($segs, 0, -1)),
                'airline' => (string) ($f['carrier']['marketingName'] ?? ''), 'logo' => avm_lite_url($f['carrier']['marketingLogo'] ?? ''),
                'flights' => array_map(fn($s) => ($s['carrier']['marketingCode'] ?? '') . ($s['flight']['marketingNumber'] ?? ''), $segs)];
        }
        if (!$tramos) continue;
        $fin = avm_precio($tot, 'vuelos');
        $out[] = ['key' => (string) ($j['journeyKey'] ?? ''), 'total' => $fin, 'currency' => (string) ($of['pricing']['display']['currency'] ?? 'USD'),
            'perAdult' => round((float) ($of['pricing']['display']['perPassenger']['adult']['total'] ?? 0) * $fin / $tot, 2), 'legs' => $tramos,
            'carryOn' => !empty($of['baggage']['hasCarryOnBag']), 'checked' => !empty($of['baggage']['hasCheckedBag']),
            'refundable' => !empty($of['terms']['refundable']), 'changeable' => !empty($of['terms']['changeable']), 'fare' => (string) ($of['fare']['family'] ?? ''), 'seats' => (int) ($of['fare']['seatsRemaining'] ?? 0)];
        if (count($out) >= 40) break 2;
    }
    usort($out, fn($a, $b) => $a['total'] <=> $b['total']);
    return ['ok' => true, 'flights' => $out];
}

/** Actividades con precio para un destino y fechas. */
function avm_lite_buscar_actividades(string $placeId, string $d1, string $d2, int $people): array {
    $pl = avm_lite_lugar($placeId);
    if (!$pl) return ['ok' => false, 'error' => 'destino', 'status' => 400];
    $params = ['language' => 'es', 'currency' => 'USD', 'location' => ['latitude' => $pl['lat'], 'longitude' => $pl['lng'], 'radiusKm' => 40], 'pagination' => ['limit' => 30, 'offset' => 0]];
    if ($d1 !== '' && strtotime($d1)) $params['dateRange'] = ['from' => date('Y-m-d', strtotime($d1)), 'to' => date('Y-m-d', strtotime($d2 ?: $d1))];
    $r = avm_lite_get('/experiences/tours', $params, 3600);
    if (!$r['ok']) return $r;
    $out = [];
    foreach ((array) ($r['data']['data']['tours'] ?? []) as $t) {
        $foto = avm_lite_url($t['pictures'][0]['url'] ?? '');
        if (empty($t['id']) || empty($t['title']) || !$foto) continue;
        $precio = (float) ($t['price']['amount'] ?? 0);
        $out[] = ['id' => (string) $t['id'], 'title' => (string) $t['title'], 'photo' => $foto, 'rating' => (float) ($t['overallRating'] ?? 0), 'reviews' => (int) ($t['numberOfRatings'] ?? 0),
            'duration' => avm_lite_duracion((array) ($t['durations'] ?? [])), 'category' => (string) ($t['categories'][0]['name'] ?? ''), 'pickup' => !empty($t['hasPickUp']),
            'price' => avm_precio($precio, 'actividades', false), 'total' => avm_precio($precio * max(1, $people), 'actividades'), 'currency' => (string) ($t['price']['currency'] ?? 'USD'), 'priceNote' => (string) ($t['priceDescription'] ?? 'por persona')];
    }
    usort($out, fn($a, $b) => [$b['rating'] >= 4.5, $b['reviews']] <=> [$a['rating'] >= 4.5, $a['reviews']]);
    return ['ok' => true, 'tours' => $out, 'place' => $pl['name']];
}

