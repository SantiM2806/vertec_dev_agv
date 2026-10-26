<?php
/**
 * Productos propios de la agencia (backoffice → Tarifarios): alojamientos, actividades, transportes y circuitos
 * con temporadas y precios NETOS. Se guardan en productos/<id> (solo administradores) y aquí se convierten
 * en precios al cliente con el markup y los fees de la agencia, con el mismo formato que LiteAPI para que
 * el buscador, las fichas y la cotización los traten igual. Sus ids públicos empiezan por "p-".
 *
 * Precios de alojamiento: por persona y por noche según la acomodación (sencilla, doble, triple) más el niño por noche.
 * Precios de actividades: por persona (adulto y niño). Transportes: por persona o por vehículo, por trayecto.
 * Circuitos: por persona para todo el circuito según la acomodación.
 * Moneda del tarifario: USD o COP (se pasa a USD con la TRM del sitio para mostrarse junto a LiteAPI).
 */

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === '_propios.php') { http_response_code(404); exit; }

const AVM_PROP_TIPOS = ['alojamiento', 'actividad', 'transporte', 'circuito'];
/** Producto de tarifas (markup) que corresponde a cada tipo. */
const AVM_PROP_MARKUP = ['alojamiento' => 'hoteles', 'actividad' => 'actividades', 'transporte' => 'traslados', 'circuito' => 'circuitos'];

function avm_prop_todos(): array {
    static $c = [];
    $t = avm_tid();
    if (!isset($c[$t])) $c[$t] = array_filter(avm_coll('productos'), fn($p) => is_array($p) && !empty($p['activo']) && in_array($p['tipo'] ?? '', AVM_PROP_TIPOS, true));
    return $c[$t];
}
function avm_prop_de(string $tipo): array { return array_filter(avm_prop_todos(), fn($p) => $p['tipo'] === $tipo); }
function avm_prop_get(string $pid, string $tipo): ?array {
    if (!preg_match('/^p-([A-Za-z0-9_-]{2,60})$/', $pid, $m)) return null;
    $p = avm_prop_todos()[$m[1]] ?? null;
    return $p && $p['tipo'] === $tipo ? $p + ['_id' => $m[1]] : null;
}
/** ¿El producto es del destino buscado? Compara sin tildes contra destino, ciudad, país y nombre. */
function avm_prop_coincide(array $p, string $q): bool {
    $q = trim(avm_norm($q)); if ($q === '') return false;
    $q = trim(preg_replace('/,.*$/', '', $q)); // "Cancún, Quintana Roo, México" → "cancun"
    foreach (['destino', 'ciudad', 'pais', 'nombre'] as $k) {
        $v = trim(avm_norm((string) ($p[$k] ?? '')));
        if ($v !== '' && (str_contains($v, $q) || str_contains($q, $v))) return true;
    }
    return false;
}
function avm_prop_trm(): float {
    static $c = [];
    $t = avm_tid();
    if (!isset($c[$t])) $c[$t] = (float) (((avm_get('site/content') ?? [])['txt']['trm'] ?? 0)) ?: 4000.0;
    return $c[$t];
}
/** Neto del tarifario en USD. */
function avm_prop_usd(float $v, array $p): float { return ($p['moneda'] ?? 'USD') === 'COP' ? round($v / avm_prop_trm(), 2) : $v; }
/** Índice de la temporada que contiene la fecha (AAAA-MM-DD), o -1. */
function avm_prop_temporada(array $p, string $fecha): int {
    foreach (array_values((array) ($p['temporadas'] ?? [])) as $i => $t)
        if (($t['desde'] ?? '') !== '' && ($t['hasta'] ?? '') !== '' && $fecha >= $t['desde'] && $fecha <= $t['hasta']) return $i;
    return -1;
}
function avm_prop_num($v): float { return max(0.0, (float) str_replace(',', '.', (string) $v)); }
function avm_prop_fotos(array $p, int $max = 16): array {
    $out = [];
    foreach ((array) ($p['fotos'] ?? []) as $ref) {
        if (!is_string($ref) || !str_starts_with($ref, 'db:')) continue;
        $u = 'api/imagen.php?id=' . rawurlencode(substr($ref, 3));
        $out[] = ['url' => $u, 'thumb' => $u, 'caption' => ''];
        if (count($out) >= $max) break;
    }
    return $out;
}
/** Liquidación de un neto propio (sin comisión de proveedor externo). */
function avm_prop_liq(float $netoUsd, string $tipo, bool $conFijo = true): array {
    $l = avm_liquidacion($netoUsd, AVM_PROP_MARKUP[$tipo] ?? 'hoteles', $conFijo);
    return ['netoProv' => round($netoUsd, 2), 'comLite' => 0.0] + $l + ['ganancia' => $l['margen'], 'taxOut' => 0.0, 'ssp' => 0.0, 'fuente' => 'propio'];
}

/* ---------- Alojamientos ---------- */
/** Personas por habitación (igual que en LiteAPI: adultos repartidos, niños en la primera). */
function avm_prop_reparto(int $rooms, int $adults, array $kids): array {
    $rooms = max(1, min(4, $rooms)); $adults = max($rooms, min(16, $adults)); $out = [];
    for ($i = 0; $i < $rooms; $i++) $out[] = ['a' => intdiv($adults, $rooms) + ($i < $adults % $rooms ? 1 : 0), 'n' => $i === 0 ? count($kids) : 0];
    return $out;
}
/** Opciones de habitación con precio total para unas fechas, o [] si alguna noche no tiene tarifa. */
function avm_prop_hotel_opciones(array $p, string $in, string $out, int $rooms, int $adults, array $kids): array {
    $a = strtotime($in); $b = strtotime($out);
    if (!$a || !$b || $b <= $a) return [];
    $noches = (int) round(($b - $a) / 86400);
    if ($noches < max(1, (int) ($p['minNoches'] ?? 1)) || $noches > 30) return [];
    $temps = [];
    for ($d = $a; $d < $b; $d += 86400) { $t = avm_prop_temporada($p, date('Y-m-d', $d)); if ($t < 0) return []; $temps[] = $t; }
    $reparto = avm_prop_reparto($rooms, $adults, $kids);
    $ops = [];
    foreach (array_values((array) ($p['habitaciones'] ?? [])) as $hi => $h) {
        if (trim((string) ($h['nombre'] ?? '')) === '') continue;
        $max = (int) ($h['max'] ?? 0); $neto = 0.0; $ok = true;
        foreach ($reparto as $o) {
            if ($max && $o['a'] + $o['n'] > $max) { $ok = false; break; }
            foreach ($temps as $t) {
                $pr = (array) (($h['precios'] ?? [])[$t] ?? []);
                $pp = $o['a'] >= 3 ? (avm_prop_num($pr['tpl'] ?? 0) ?: avm_prop_num($pr['dbl'] ?? 0)) : ($o['a'] === 2 ? avm_prop_num($pr['dbl'] ?? 0) : (avm_prop_num($pr['sgl'] ?? 0) ?: avm_prop_num($pr['dbl'] ?? 0)));
                if ($pp <= 0) { $ok = false; break 2; }
                $neto += $pp * $o['a'] + avm_prop_num($pr['nino'] ?? 0) * $o['n'];
            }
        }
        if (!$ok || $neto <= 0) continue;
        $cod = strtoupper((string) ($h['regimen'] ?? ($p['regimen'] ?? 'RO')));
        $liq = avm_prop_liq(avm_prop_usd($neto, $p), 'alojamiento');
        $ops[] = ['room' => (string) $h['nombre'], 'roomId' => (string) $hi, 'board' => AVM_REGIMEN[$cod] ?? $cod, 'boardCode' => $cod,
            'refundable' => !empty($p['reembolsable']), 'total' => $liq['total'], 'currency' => 'USD', 'liq' => $liq];
    }
    usort($ops, fn($x, $y) => $x['total'] <=> $y['total']);
    return $ops;
}
/** Alojamientos propios del destino, con el formato de los resultados de LiteAPI. */
function avm_prop_buscar_hoteles(string $q, string $in, string $out, int $rooms, int $adults, array $kids): array {
    $res = []; $noches = max(1, (int) round((strtotime($out) - strtotime($in)) / 86400));
    foreach (avm_prop_de('alojamiento') as $id => $p) {
        if (!avm_prop_coincide($p, $q)) continue;
        $ops = avm_prop_hotel_opciones($p, $in, $out, $rooms, $adults, $kids);
        if (!$ops) continue;
        $m = $ops[0]; $foto = avm_prop_fotos($p, 1)[0]['url'] ?? '';
        $res[] = ['id' => 'p-' . $id, 'name' => (string) $p['nombre'], 'photo' => $foto, 'stars' => (float) ($p['estrellas'] ?? 0), 'rating' => 0.0, 'reviews' => 0,
            'address' => trim(implode(', ', array_filter([(string) ($p['direccion'] ?? ''), (string) ($p['ciudad'] ?? '')]))), 'city' => (string) ($p['destino'] ?? ''), 'type' => (string) ($p['clase'] ?? 'Hotel'), 'km' => null,
            'total' => $m['total'], 'perNight' => round($m['total'] / $noches, 2), 'room' => $m['room'], 'board' => $m['board'], 'boardCode' => $m['boardCode'],
            'refundable' => $m['refundable'], 'liq' => $m['liq'], 'opts' => array_map(fn($o) => array_diff_key($o, ['roomId' => 1, 'currency' => 1]), $ops), 'fuente' => 'propio'];
    }
    return $res;
}
/** Tarifas de alojamientos propios con el formato de avm_lite_tarifas(). */
function avm_prop_tarifas(array $ids, string $in, string $out, int $rooms, int $adults, array $kids, int $max = 8): array {
    $res = []; $noches = max(1, (int) round((strtotime($out) - strtotime($in)) / 86400));
    foreach ($ids as $pid) {
        $p = avm_prop_get((string) $pid, 'alojamiento'); if (!$p) continue;
        $ops = array_slice(avm_prop_hotel_opciones($p, $in, $out, $rooms, $adults, $kids), 0, $max);
        if ($ops) $res[$pid] = ['from' => $ops[0]['total'], 'currency' => 'USD', 'board' => $ops[0]['board'], 'nights' => $noches, 'options' => $ops];
    }
    return $res;
}
/** Ficha de un alojamiento propio con el formato de avm_lite_hotel(). */
function avm_prop_hotel(string $pid): array {
    $p = avm_prop_get($pid, 'alojamiento');
    if (!$p) return ['ok' => false, 'error' => 'no_existe', 'status' => 404];
    $rooms = [];
    foreach (array_values((array) ($p['habitaciones'] ?? [])) as $hi => $h) {
        if (trim((string) ($h['nombre'] ?? '')) === '') continue;
        $fotos = [];
        foreach ((array) ($h['fotos'] ?? []) as $ref) if (is_string($ref) && str_starts_with($ref, 'db:')) { $u = 'api/imagen.php?id=' . rawurlencode(substr($ref, 3)); $fotos[] = ['url' => $u, 'thumb' => $u, 'caption' => '']; }
        $rooms[] = ['id' => (string) $hi, 'name' => (string) $h['nombre'], 'size' => (string) ($h['tamano'] ?? ''), 'max' => (int) ($h['max'] ?? 0), 'beds' => (string) ($h['camas'] ?? ''),
            'photo' => $fotos[0]['url'] ?? '', 'photos' => $fotos, 'amenities' => [], 'description' => (string) ($h['descripcion'] ?? '')];
    }
    $lineas = fn($v) => array_values(array_filter(array_map('trim', is_array($v) ? $v : explode("\n", (string) $v))));
    return ['ok' => true, 'hotel' => [
        'id' => $pid, 'name' => (string) $p['nombre'], 'stars' => (float) ($p['estrellas'] ?? 0), 'rating' => 0.0, 'reviews' => 0,
        // city = destino: es lo que viaja con la cotización y lo que se usa para buscar sus adicionales
        'address' => trim(implode(', ', array_filter([(string) ($p['direccion'] ?? ''), (string) ($p['ciudad'] ?? '')]))), 'city' => (string) ($p['destino'] ?? $p['ciudad'] ?? ''), 'country' => (string) ($p['pais'] ?? ''), 'chain' => '', 'type' => (string) ($p['clase'] ?? 'Hotel'),
        'lat' => (float) ($p['lat'] ?? 0), 'lng' => (float) ($p['lng'] ?? 0), 'description' => (string) ($p['descripcion'] ?? ''), 'important' => (string) ($p['politica'] ?? ''),
        'checkin' => (string) ($p['checkin'] ?? ''), 'checkout' => (string) ($p['checkout'] ?? ''), 'images' => avm_prop_fotos($p), 'facilities' => $lineas($p['incluye'] ?? []),
        'rooms' => $rooms, 'poi' => [], 'pros' => [], 'categories' => [], 'airport' => '', 'fuente' => 'propio',
    ]];
}

/* ---------- Actividades ---------- */
function avm_prop_act_precio(array $p, string $fecha): ?array {
    $t = avm_prop_temporada($p, $fecha);
    if ($t < 0 && empty($p['temporadas'])) $t = 0; // sin temporadas: un solo precio
    if ($t < 0) return null;
    $pr = (array) (($p['precios'] ?? [])[$t] ?? []);
    $ad = avm_prop_num($pr['adulto'] ?? 0);
    return $ad > 0 ? ['adulto' => $ad, 'nino' => avm_prop_num($pr['nino'] ?? 0)] : null;
}
function avm_prop_buscar_actividades(string $q, string $d1, int $people): array {
    $fecha = $d1 !== '' && strtotime($d1) ? date('Y-m-d', strtotime($d1)) : date('Y-m-d');
    $out = [];
    foreach (avm_prop_de('actividad') as $id => $p) {
        if ($q !== '' && !avm_prop_coincide($p, $q)) continue;
        $pr = avm_prop_act_precio($p, $fecha); if (!$pr) continue;
        $una = avm_prop_liq(avm_prop_usd($pr['adulto'], $p), 'actividad', false);
        $todas = avm_prop_liq(avm_prop_usd($pr['adulto'] * max(1, $people), $p), 'actividad');
        $out[] = ['id' => 'p-' . $id, 'title' => (string) $p['nombre'], 'photo' => avm_prop_fotos($p, 1)[0]['url'] ?? '', 'rating' => 0.0, 'reviews' => 0,
            'duration' => (string) ($p['duracion'] ?? ''), 'category' => (string) ($p['categoria'] ?? 'Actividad'), 'pickup' => !empty($p['recogida']),
            'price' => $una['total'], 'total' => $todas['total'], 'currency' => 'USD', 'priceNote' => 'por persona', 'liq' => $todas, 'fuente' => 'propio'];
    }
    return $out;
}
function avm_prop_actividad(string $pid): array {
    $p = avm_prop_get($pid, 'actividad');
    if (!$p) return ['ok' => false, 'error' => 'no_existe', 'status' => 404];
    $pr = avm_prop_act_precio($p, date('Y-m-d'));
    if (!$pr) foreach (array_keys((array) ($p['temporadas'] ?? [])) as $i) { $x = (array) (($p['precios'] ?? [])[$i] ?? []); if (avm_prop_num($x['adulto'] ?? 0) > 0) { $pr = ['adulto' => avm_prop_num($x['adulto'])]; break; } }
    $lineas = fn($v) => array_values(array_filter(array_map('trim', is_array($v) ? $v : explode("\n", (string) $v))));
    return ['ok' => true, 'tour' => [
        'id' => $pid, 'title' => (string) $p['nombre'], 'description' => (string) ($p['descripcion'] ?? ''), 'highlights' => [],
        'included' => implode("\n", $lineas($p['incluye'] ?? [])), 'excluded' => (string) ($p['noIncluye'] ?? ''), 'meeting' => (string) ($p['encuentro'] ?? ''), 'info' => (string) ($p['politica'] ?? ''),
        'bring' => [], 'notFor' => [], 'rating' => 0.0, 'reviews' => 0, 'duration' => (string) ($p['duracion'] ?? ''), 'pickup' => !empty($p['recogida']),
        'images' => avm_prop_fotos($p), 'itinerary' => [], 'cancel' => '', 'category' => (string) ($p['categoria'] ?? 'Actividad'),
        'city' => (string) ($p['ciudad'] ?? $p['destino'] ?? ''), 'country' => (string) ($p['pais'] ?? ''),
        'price' => $pr ? avm_prop_liq(avm_prop_usd($pr['adulto'], $p), 'actividad', false)['total'] : 0, 'currency' => 'USD', 'priceNote' => 'por persona', 'lat' => 0, 'lng' => 0, 'fuente' => 'propio',
    ]];
}

/* ---------- Transportes ---------- */
/** Traslados propios del destino con precio total para las personas y el trayecto (ida, o ida y regreso). */
function avm_prop_traslados(string $q, string $fecha, int $pax, bool $regreso): array {
    $fecha = $fecha !== '' && strtotime($fecha) ? date('Y-m-d', strtotime($fecha)) : date('Y-m-d');
    $out = [];
    foreach (avm_prop_de('transporte') as $id => $p) {
        if (!avm_prop_coincide($p, $q)) continue;
        $t = avm_prop_temporada($p, $fecha); if ($t < 0 && empty($p['temporadas'])) $t = 0; if ($t < 0) continue;
        foreach (array_values((array) ($p['trayectos'] ?? [])) as $ti => $tr) {
            $v = avm_prop_num(((array) (($tr['precios'] ?? [])[$t] ?? []))['valor'] ?? 0); if ($v <= 0) continue;
            $cap = max(1, (int) ($tr['capacidad'] ?? 0) ?: 99);
            $porVeh = ($tr['cobro'] ?? 'persona') === 'vehiculo';
            if ($porVeh && $pax > $cap * 4) continue;
            $neto = ($porVeh ? $v * (int) ceil($pax / $cap) : $v * $pax) * ($regreso ? 2 : 1);
            $liq = avm_prop_liq(avm_prop_usd($neto, $p), 'transporte');
            $out[] = ['id' => 'p-' . $id . '~' . $ti, 'name' => trim(($tr['desde'] ?? '') . ' – ' . ($tr['hasta'] ?? ''), ' –') ?: (string) $p['nombre'],
                'servicio' => ($tr['servicio'] ?? 'compartido') === 'privado' ? 'Privado' : 'Compartido', 'proveedor' => (string) $p['nombre'], 'total' => $liq['total'], 'liq' => $liq];
        }
    }
    usort($out, fn($a, $b) => $a['total'] <=> $b['total']);
    return $out;
}

/* ---------- Circuitos ---------- */
/** Circuitos publicados con su precio "desde" por persona en acomodación doble. */
function avm_prop_circuitos(): array {
    $out = [];
    foreach (avm_prop_de('circuito') as $id => $p) {
        $min = 0.0;
        foreach ((array) ($p['precios'] ?? []) as $pr) { $v = avm_prop_num(((array) $pr)['dbl'] ?? 0) ?: avm_prop_num(((array) $pr)['sgl'] ?? 0); if ($v > 0 && (!$min || $v < $min)) $min = $v; }
        $hoy = date('Y-m-d');
        $salidas = array_values(array_filter(array_map('trim', is_array($p['salidas'] ?? null) ? $p['salidas'] : explode("\n", (string) ($p['salidas'] ?? ''))), fn($s) => $s !== '' && $s >= $hoy));
        sort($salidas);
        $out[] = ['id' => 'p-' . $id, 'name' => (string) $p['nombre'], 'place' => (string) ($p['destino'] ?? ''), 'duration' => trim(($p['noches'] ?? '') ? ((int) $p['noches'] + 1) . ' días' : (string) ($p['duracion'] ?? '')),
            'desc' => (string) ($p['descripcion'] ?? ''), 'inc' => array_values(array_filter(array_map('trim', explode("\n", (string) ($p['incluye'] ?? ''))))),
            'photo' => avm_prop_fotos($p, 1)[0]['url'] ?? '', 'price' => $min > 0 ? avm_prop_liq(avm_prop_usd($min, $p), 'circuito', false)['total'] : 0, 'salidas' => array_slice($salidas, 0, 8)];
    }
    return $out;
}
