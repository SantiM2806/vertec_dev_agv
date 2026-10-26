<?php
/**
 * Catálogo de LiteAPI para la página: fichas de hotel y actividades.
 * GET ?tipo=hotel&id=lp1234
 * GET ?tipo=actividades&lat=…&lng=…&km=25&limit=8
 * GET ?tipo=actividad&id=903304
 * GET ?tipo=tarifas&ids=lp1,lp2&in=2026-12-10&out=2026-12-15&rooms=1&adults=2&kids=5,8
 */
require __DIR__ . '/_nucleo.php';
avm_tenant(); // agencia de esta dirección (404 si no existe)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') avm_fail(405, 'metodo');
$tipo = (string) ($_GET['tipo'] ?? '');
$id = (string) ($_GET['id'] ?? '');
switch ($tipo) {
    // Ids "p-…": productos propios de la agencia (Tarifarios)
    case 'hotel': $r = str_starts_with($id, 'p-') ? avm_prop_hotel($id) : avm_lite_hotel($id); $k = 'hotel'; break;
    case 'actividad': $r = str_starts_with($id, 'p-') ? avm_prop_actividad($id) : avm_lite_actividad($id); $k = 'tour'; break;
    case 'actividades':
        $lat = filter_input(INPUT_GET, 'lat', FILTER_VALIDATE_FLOAT); $lng = filter_input(INPUT_GET, 'lng', FILTER_VALIDATE_FLOAT);
        if ($lat === false || $lat === null || $lng === false || $lng === null || abs($lat) > 90 || abs($lng) > 180) avm_fail(400, 'coordenadas');
        $km = max(1, min(80, (int) (filter_input(INPUT_GET, 'km', FILTER_VALIDATE_INT) ?: 25)));
        $limit = max(1, min(20, (int) (filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?: 8)));
        $r = avm_lite_actividades($lat, $lng, $km, $limit); $k = 'tours'; break;
    case 'tarifas':
        $ids = explode(',', (string) ($_GET['ids'] ?? ''));
        $kids = array_filter(explode(',', (string) ($_GET['kids'] ?? '')), fn($x) => $x !== '');
        $propios = array_values(array_filter($ids, fn($x) => str_starts_with($x, 'p-')));
        $otros = array_values(array_diff($ids, $propios));
        $in = (string) ($_GET['in'] ?? ''); $out = (string) ($_GET['out'] ?? ''); $ro = (int) ($_GET['rooms'] ?? 1); $ad = (int) ($_GET['adults'] ?? 2);
        if ($propios && !avm_lite_fechas_ok($in, $out)) avm_fail(400, 'fechas');
        $r = $otros ? avm_lite_tarifas($otros, $in, $out, $ro, $ad, $kids) : ['ok' => true, 'rates' => [], 'nights' => max(1, (int) round((strtotime($out) - strtotime($in)) / 86400))];
        if ($propios) { $pr = avm_prop_tarifas($propios, $in, $out, $ro, $ad, $kids); $r = $r['ok'] ? ['ok' => true, 'rates' => $r['rates'] + $pr, 'nights' => $r['nights']] : ($pr ? ['ok' => true, 'rates' => $pr, 'nights' => max(1, (int) round((strtotime($out) - strtotime($in)) / 86400))] : $r); }
        // El desglose (neto y comisiones) solo viaja a administradores
        $adm = avm_is_admin(); session_write_close();
        header('Cache-Control: ' . ($adm ? 'private, no-store' : 'public, max-age=900'));
        if ($r['ok'] && !$adm) $r = avm_sin_liq($r);
        avm_json($r['ok'] ? ['rates' => $r['rates'], 'nights' => $r['nights']] : ['error' => $r['error']], $r['ok'] ? 200 : (int) $r['status']);
    default: avm_fail(400, 'tipo');
}
$adm = avm_is_admin(); session_write_close();
if ($r['ok'] && !$adm) $r = avm_sin_liq($r);
header('Cache-Control: ' . ($adm ? 'private, no-store' : 'public, max-age=' . (str_starts_with($id, 'p-') ? 300 : 3600)));
avm_json($r['ok'] ? [$k => $r[$k]] : ['error' => $r['error']], $r['ok'] ? 200 : (int) $r['status']);
