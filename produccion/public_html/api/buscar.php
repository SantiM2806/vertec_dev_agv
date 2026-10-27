<?php
/**
 * Buscador del sitio con precios en vivo de LiteAPI.
 * GET ?tipo=lugares&q=cancun                 → destinos para autocompletar
 * GET ?tipo=hotel_lugar&place=…&q=Riu Republica → id del hotel elegido en el autocompletado
 * GET ?tipo=aeropuertos&q=bog                → aeropuertos para autocompletar
 * GET ?tipo=hoteles&place=…&q=…&in=…&out=…&rooms=1&adults=2&kids=5,8
 * GET ?tipo=vuelos&from=BOG&to=PUJ&d1=…&d2=…&adults=2&kids=5&cabin=ECONOMY
 * GET ?tipo=actividades&place=…&q=…&d1=…&d2=…&people=2
 * GET ?tipo=extras&q=Punta Cana&d1=…&pax=2&regreso=1 → actividades y traslados propios con precio (paso de adicionales)
 * GET ?tipo=circuitos                         → circuitos propios publicados
 * Junta los productos propios de la agencia (backoffice → Tarifarios) con los de LiteAPI: si LiteAPI no está
 * configurado o falla, igual salen los propios. La llave de LiteAPI vive en el servidor.
 */
require __DIR__ . '/_nucleo.php';
avm_tenant(); // agencia de esta dirección (404 si no existe)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') avm_fail(405, 'metodo');
$g = fn($k, $d = '') => trim((string) ($_GET[$k] ?? $d));
$kids = array_values(array_filter(explode(',', $g('kids')), fn($x) => $x !== ''));
switch ($g('tipo')) {
    case 'lugares':
        // Primero los destinos y alojamientos propios que coinciden; luego los lugares de LiteAPI
        $propios = []; $vistos = [];
        foreach (avm_prop_todos() as $pid => $p) {
            if (!avm_prop_coincide($p, $g('q'))) continue;
            $dest = trim((string) ($p['destino'] ?? ''));
            if ($dest !== '' && !isset($vistos[avm_norm($dest)])) { $vistos[avm_norm($dest)] = 1; $propios[] = ['id' => '', 'name' => $dest, 'address' => (string) ($p['pais'] ?? ''), 'types' => ['propio']]; }
            if ($p['tipo'] === 'alojamiento') $propios[] = ['id' => 'p-' . $pid, 'name' => (string) $p['nombre'], 'address' => trim(($p['destino'] ?? '') . ', ' . ($p['pais'] ?? ''), ', '), 'types' => ['lodging', 'propio']];
        }
        $r = avm_prov_activo('liteapi') ? avm_lite_lugares($g('q')) : ['ok' => false];
        $r = ['ok' => true, 'places' => array_slice(array_merge($propios, $r['ok'] ? $r['places'] : []), 0, 10)];
        $k = 'places'; $ttl = 3600; break;
    case 'hotel_lugar': $r = ['ok' => true, 'hotelId' => avm_lite_hotel_de_lugar($g('place'), $g('q'), ['in' => $g('in'), 'out' => $g('out'), 'rooms' => $g('rooms', '1'), 'adults' => $g('adults', '2'), 'kids' => $kids])]; $k = 'hotelId'; $ttl = 600; break;
    case 'aeropuertos': $r = avm_lite_aeropuertos($g('q')); $k = 'airports'; $ttl = 86400; break;
    case 'hoteles':
        // Tarifarios propios + todos los proveedores activos de la agencia (Integraciones)
        $r = avm_prov_buscar_hoteles($g('place'), $g('q'), $g('in'), $g('out'), (int) $g('rooms', '1'), (int) $g('adults', '2'), $kids);
        $k = ['hotels', 'nights', 'currency']; $ttl = 600; break;
    case 'vuelos':
        $r = avm_lite_buscar_vuelos($g('from'), $g('to'), $g('d1'), $g('d2'), (int) $g('adults', '1'), $kids, $g('cabin', 'ECONOMY'));
        $k = 'flights'; $ttl = 300; break;
    case 'actividades':
        $r = avm_prov_buscar_actividades($g('place'), $g('q'), $g('d1'), $g('d2'), (int) $g('people', '2'));
        $k = ['tours', 'place']; $ttl = 900; break;
    case 'extras':
        $pax = max(1, min(20, (int) $g('pax', '2')));
        $r = ['ok' => true, 'actividades' => $g('q') !== '' ? avm_prop_buscar_actividades($g('q'), $g('d1'), $pax) : [],
            'traslados' => avm_prop_traslados($g('q'), $g('d1'), $pax, $g('regreso', '1') === '1'), 'trasladosSolo' => avm_prop_traslados($g('q'), $g('d1'), $pax, false)];
        $k = ['actividades', 'traslados', 'trasladosSolo']; $ttl = 600; break;
    case 'circuitos':
        $r = ['ok' => true, 'circuitos' => avm_prop_circuitos()]; $k = 'circuitos'; $ttl = 600; break;
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
