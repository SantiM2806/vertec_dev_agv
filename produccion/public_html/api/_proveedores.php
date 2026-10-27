<?php
/**
 * Proveedores de producto en línea, por agencia (backoffice → Integraciones).
 *
 * Cada proveedor declara qué productos trae, qué credenciales necesita y sus funciones. El buscador y las fichas
 * no llaman a un proveedor en particular: piden a todos los activos de la agencia y juntan los resultados con
 * los productos propios (Tarifarios). Cada proveedor marca sus ids con un prefijo para saber a quién pedir
 * la ficha o las tarifas: LiteAPI sin prefijo (por compatibilidad), propios "p-", TBO "tbo-", World2Meet "w2m-", Omnibees "omb-".
 *
 * Para conectar un proveedor nuevo: agregarlo a avm_prov_registro() con sus funciones (mismo formato de
 * respuesta que LiteAPI, ver _liteapi.php) y sus campos de credenciales (los secretos van en AVM_CFG_SECRETOS).
 */

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === '_proveedores.php') { http_response_code(404); exit; }

function avm_prov_registro(): array {
    return [
        'liteapi' => ['nombre' => 'LiteAPI', 'estado' => 'disponible', 'prefijo' => '', 'productos' => ['hoteles', 'actividades', 'vuelos'],
            'desc' => 'Hoteles de todo el mundo con tarifa en línea; actividades y vuelos si están habilitados en tu cuenta.',
            'web' => 'https://dashboard.liteapi.travel',
            'campos' => [['liteapi_key', 'Llave de API (producción o sandbox)', 'secreto']],
            'hoteles' => 'avm_lite_buscar_hoteles_q', 'tarifas' => 'avm_lite_tarifas', 'hotel' => 'avm_lite_hotel',
            'actividades' => 'avm_lite_buscar_actividades_q', 'actividad' => 'avm_lite_actividad', 'probar' => 'avm_lite_probar'],
        'tbo' => ['nombre' => 'TBO Holidays', 'estado' => 'proximamente', 'prefijo' => 'tbo-', 'productos' => ['hoteles', 'traslados', 'actividades'],
            'desc' => 'Hoteles, traslados y actividades con tus tarifas negociadas en TBO.', 'web' => 'https://www.tboholidays.com',
            'campos' => [['tbo_user', 'Usuario de API', 'texto'], ['tbo_pass', 'Contraseña de API', 'secreto'], ['tbo_url', 'URL del servicio (la que te dé TBO)', 'texto']]],
        'w2m' => ['nombre' => 'World2Meet', 'estado' => 'proximamente', 'prefijo' => 'w2m-', 'productos' => ['hoteles', 'traslados'],
            'desc' => 'Hoteles y traslados de World2Meet (W2M) con tu contrato de agencia.', 'web' => 'https://www.w2m.com',
            'campos' => [['w2m_user', 'Usuario', 'texto'], ['w2m_key', 'Clave de API', 'secreto'], ['w2m_agency', 'Código de agencia', 'texto']]],
        'omnibees' => ['nombre' => 'Omnibees', 'estado' => 'proximamente', 'prefijo' => 'omb-', 'productos' => ['hoteles'],
            'desc' => 'Tarifas directas de los hoteles que distribuyen con Omnibees.', 'web' => 'https://omnibees.com',
            'campos' => [['omnibees_user', 'Usuario', 'texto'], ['omnibees_key', 'Token de API', 'secreto'], ['omnibees_client', 'Código de cliente', 'texto']]],
    ];
}

/** ¿Está activo el proveedor para la agencia actual? Activo = tiene sus credenciales y no está apagado. */
function avm_prov_activo(string $k): bool {
    $p = avm_prov_registro()[$k] ?? null;
    if (!$p || $p['estado'] !== 'disponible') return false;
    $t = $GLOBALS['AVM_T'] ?? null;
    if (($t['config']['prov_' . $k] ?? true) === false) return false;
    foreach ($p['campos'] as [$campo, , $tipo]) if ($tipo === 'secreto' && (string) avm_cfg($campo, '') === '') return false;
    return true;
}
function avm_prov_activos(string $producto): array {
    return array_keys(array_filter(avm_prov_registro(), fn($p, $k) => in_array($producto, $p['productos'], true) && isset($p[$producto]) && avm_prov_activo($k), ARRAY_FILTER_USE_BOTH));
}
/** Proveedor dueño de un id (por su prefijo); los ids sin prefijo son de LiteAPI. */
function avm_prov_de_id(string $id): string {
    if (str_starts_with($id, 'p-')) return 'propio';
    foreach (avm_prov_registro() as $k => $p) if ($p['prefijo'] !== '' && str_starts_with($id, $p['prefijo'])) return $k;
    return 'liteapi';
}

/* ---------- Búsqueda en todos los proveedores ---------- */
/** Hoteles: propios + cada proveedor activo. Si todos fallan y no hay propios, devuelve el error del primero. */
function avm_prov_buscar_hoteles(string $place, string $q, string $in, string $out, int $rooms, int $adults, array $kids): array {
    if (!avm_lite_fechas_ok($in, $out)) return ['ok' => false, 'error' => 'fechas', 'status' => 400];
    $todos = avm_prop_buscar_hoteles($q, $in, $out, $rooms, $adults, $kids); $err = null;
    foreach (avm_prov_activos('hoteles') as $k) {
        $r = call_user_func(avm_prov_registro()[$k]['hoteles'], $place, $q, $in, $out, $rooms, $adults, $kids);
        if ($r['ok']) foreach ($r['hotels'] as $h) $todos[] = $h + ['fuente' => $k]; else $err = $err ?? $r;
    }
    if (!$todos) return $err ?? ['ok' => false, 'error' => 'sin_proveedores', 'status' => 503];
    usort($todos, fn($a, $b) => $a['total'] <=> $b['total']);
    return ['ok' => true, 'hotels' => $todos, 'nights' => max(1, (int) round((strtotime($out) - strtotime($in)) / 86400)), 'currency' => 'USD'];
}
function avm_prov_buscar_actividades(string $place, string $q, string $d1, string $d2, int $people): array {
    $todos = avm_prop_buscar_actividades($q, $d1, $people); $err = null; $lugar = $q;
    foreach (avm_prov_activos('actividades') as $k) {
        $r = call_user_func(avm_prov_registro()[$k]['actividades'], $place, $q, $d1, $d2, $people);
        if ($r['ok']) { foreach ($r['tours'] as $t) $todos[] = $t + ['fuente' => $k]; $lugar = $r['place'] ?? $lugar; } else $err = $err ?? $r;
    }
    if (!$todos) return $err ?? ['ok' => false, 'error' => 'sin_proveedores', 'status' => 503];
    return ['ok' => true, 'tours' => $todos, 'place' => $lugar];
}
/** Tarifas de varios hoteles: cada id va a su proveedor. */
function avm_prov_tarifas(array $ids, string $in, string $out, int $rooms, int $adults, array $kids, int $max = 8): array {
    if (!avm_lite_fechas_ok($in, $out)) return ['ok' => false, 'error' => 'fechas', 'status' => 400];
    $noches = max(1, (int) round((strtotime($out) - strtotime($in)) / 86400));
    $grupos = [];
    foreach ($ids as $id) if ($id !== '') $grupos[avm_prov_de_id((string) $id)][] = (string) $id;
    $rates = []; $err = null;
    foreach ($grupos as $k => $lista) {
        if ($k === 'propio') { $rates += avm_prop_tarifas($lista, $in, $out, $rooms, $adults, $kids, $max); continue; }
        $fn = avm_prov_registro()[$k]['tarifas'] ?? null;
        if (!$fn || !avm_prov_activo($k)) continue;
        $r = call_user_func($fn, $lista, $in, $out, $rooms, $adults, $kids, $max);
        if ($r['ok']) $rates += $r['rates']; else $err = $err ?? $r;
    }
    if (!$rates && $err) return $err;
    return ['ok' => true, 'rates' => $rates, 'nights' => $noches];
}
/** Ficha de un hotel o una actividad según el proveedor del id. */
function avm_prov_ficha(string $tipo, string $id): array {
    $k = avm_prov_de_id($id);
    if ($k === 'propio') return $tipo === 'hotel' ? avm_prop_hotel($id) : avm_prop_actividad($id);
    $fn = avm_prov_registro()[$k][$tipo] ?? null;
    if (!$fn) return ['ok' => false, 'error' => 'no_existe', 'status' => 404];
    if (!avm_prov_activo($k)) return ['ok' => false, 'error' => 'sin_configurar', 'status' => 503];
    return call_user_func($fn, $id);
}

/* ---------- Adaptadores de LiteAPI con la firma común ---------- */
function avm_lite_buscar_hoteles_q(string $place, string $q, string $in, string $out, int $rooms, int $adults, array $kids): array {
    return avm_lite_buscar_hoteles(avm_lite_resolver_lugar($place, $q), $in, $out, $rooms, $adults, $kids);
}
function avm_lite_buscar_actividades_q(string $place, string $q, string $d1, string $d2, int $people): array {
    return avm_lite_buscar_actividades(avm_lite_resolver_lugar($place, $q), $d1, $d2, $people);
}
/** Prueba de conexión: una consulta liviana con la llave actual. */
function avm_lite_probar(): array {
    $r = avm_lite_get('/data/hotelTypes', [], 60);
    if ($r['ok']) return ['ok' => true, 'mensaje' => 'Conexión correcta con LiteAPI.'];
    return ['ok' => false, 'mensaje' => $r['error'] === 'sin_configurar' ? 'Falta la llave.' : 'LiteAPI rechazó la llave o no respondió (' . $r['error'] . ').'];
}
