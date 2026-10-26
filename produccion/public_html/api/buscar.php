<?php
/**
 * Buscador del sitio con precios en vivo de LiteAPI.
 * GET ?tipo=lugares&q=cancun                 → destinos para autocompletar
 * GET ?tipo=hotel_lugar&place=…&q=Riu Republica → id del hotel elegido en el autocompletado
 * GET ?tipo=aeropuertos&q=bog                → aeropuertos para autocompletar
 * GET ?tipo=hoteles&place=…&q=…&in=…&out=…&rooms=1&adults=2&kids=5,8
 * GET ?tipo=vuelos&from=BOG&to=PUJ&d1=…&d2=…&adults=2&kids=5&cabin=ECONOMY
 * GET ?tipo=actividades&place=…&q=…&d1=…&d2=…&people=2
 * La llave de LiteAPI vive en el servidor; cada respuesta se guarda un rato en caché.
 */
require __DIR__ . '/_nucleo.php';
avm_tenant(); // agencia de esta dirección (404 si no existe)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') avm_fail(405, 'metodo');
$g = fn($k, $d = '') => trim((string) ($_GET[$k] ?? $d));
$kids = array_values(array_filter(explode(',', $g('kids')), fn($x) => $x !== ''));
switch ($g('tipo')) {
    case 'lugares': $r = avm_lite_lugares($g('q')); $k = 'places'; $ttl = 86400; break;
    case 'hotel_lugar': $r = ['ok' => true, 'hotelId' => avm_lite_hotel_de_lugar($g('place'), $g('q'), ['in' => $g('in'), 'out' => $g('out'), 'rooms' => $g('rooms', '1'), 'adults' => $g('adults', '2'), 'kids' => $kids])]; $k = 'hotelId'; $ttl = 600; break;
    case 'aeropuertos': $r = avm_lite_aeropuertos($g('q')); $k = 'airports'; $ttl = 86400; break;
    case 'hoteles':
        $r = avm_lite_buscar_hoteles(avm_lite_resolver_lugar($g('place'), $g('q')), $g('in'), $g('out'), (int) $g('rooms', '1'), (int) $g('adults', '2'), $kids);
        $k = ['hotels', 'nights', 'currency']; $ttl = 600; break;
    case 'vuelos':
        $r = avm_lite_buscar_vuelos($g('from'), $g('to'), $g('d1'), $g('d2'), (int) $g('adults', '1'), $kids, $g('cabin', 'ECONOMY'));
        $k = 'flights'; $ttl = 300; break;
    case 'actividades':
        $r = avm_lite_buscar_actividades(avm_lite_resolver_lugar($g('place'), $g('q')), $g('d1'), $g('d2'), (int) $g('people', '2'));
        $k = ['tours', 'place']; $ttl = 900; break;
    default: avm_fail(400, 'tipo');
}
if (!$r['ok']) avm_json(['error' => $r['error']], (int) $r['status']);
// El desglose (neto y comisiones) solo viaja a administradores
$adm = avm_is_admin(); session_write_close();
if (!$adm) $r = avm_sin_liq($r);
header('Cache-Control: ' . ($adm ? 'private, no-store' : 'public, max-age=' . $ttl));
$out = [];
foreach ((array) $k as $x) $out[$x] = $r[$x];
avm_json($out);
