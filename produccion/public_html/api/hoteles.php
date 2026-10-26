<?php
/**
 * Hoteles alrededor de un destino (LiteAPI), para el buscador y la página del destino.
 * GET ?lat=21.118&lng=-86.759&radius=15000&limit=12 → { hotels: [ { id, name, stars, rating, reviews, address, photo } ] }
 */
require __DIR__ . '/_nucleo.php';
avm_tenant(); // agencia de esta dirección (404 si no existe)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') avm_fail(405, 'metodo');
$lat = filter_input(INPUT_GET, 'lat', FILTER_VALIDATE_FLOAT);
$lng = filter_input(INPUT_GET, 'lng', FILTER_VALIDATE_FLOAT);
if ($lat === false || $lat === null || $lng === false || $lng === null || abs($lat) > 90 || abs($lng) > 180) avm_fail(400, 'coordenadas');
$radius = max(1000, min(50000, (int) (filter_input(INPUT_GET, 'radius', FILTER_VALIDATE_INT) ?: 10000)));
$limit = max(1, min(24, (int) (filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?: 12)));
$r = avm_lite_hoteles($lat, $lng, $radius, $limit);
header('Cache-Control: public, max-age=3600');
avm_json($r['ok'] ? ['hotels' => $r['hotels']] : ['hotels' => [], 'error' => $r['error']], $r['ok'] ? 200 : (int) $r['status']);
