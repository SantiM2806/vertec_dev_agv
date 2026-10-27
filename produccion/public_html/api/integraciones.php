<?php
/**
 * Integraciones de la agencia (solo administradores): proveedores, sus credenciales y si están encendidos.
 * GET                                                   → estado de cada proveedor (las llaves nunca se devuelven)
 * POST { op: "guardar", proveedor, encendido, campos: { campo: valor }, borrar: [campo] }
 * POST { op: "probar", proveedor }                      → prueba la conexión con las credenciales guardadas
 */
require __DIR__ . '/_nucleo.php';
avm_tenant();
if (!avm_is_admin()) avm_fail(401, 'sesion');
session_write_close();

function avm_integ_estado(): array {
    $t = avm_tenant(); $out = [];
    foreach (avm_prov_registro() as $k => $p) {
        $campos = [];
        foreach ($p['campos'] as [$c, $label, $tipo]) {
            $propio = (string) ($t['config'][$c] ?? '');
            $campos[] = ['k' => $c, 'label' => $label, 'tipo' => $tipo, 'lleno' => $propio !== '', 'valor' => $tipo === 'secreto' ? '' : $propio];
        }
        $demo = $k === 'liteapi' && empty($t['config']['liteapi_key']) && !empty($t['config']['liteapi_demo']) && !empty(avm_config()['liteapi_key']);
        $out[] = ['k' => $k, 'nombre' => $p['nombre'], 'estado' => $p['estado'], 'productos' => $p['productos'], 'desc' => $p['desc'], 'web' => $p['web'],
            'encendido' => ($t['config']['prov_' . $k] ?? true) !== false, 'activo' => avm_prov_activo($k), 'demo' => $demo, 'campos' => $campos, 'probar' => isset($p['probar'])];
    }
    return $out;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $propios = [];
    foreach (avm_prop_todos() as $p) $propios[$p['tipo']] = ($propios[$p['tipo']] ?? 0) + 1;
    avm_json(['proveedores' => avm_integ_estado(), 'propios' => $propios]);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') avm_fail(405, 'metodo');
avm_check_origin();
$req = json_decode((string) file_get_contents('php://input', false, null, 0, 16 * 1024), true);
if (!is_array($req)) avm_fail(400, 'json');
$k = (string) ($req['proveedor'] ?? '');
$p = avm_prov_registro()[$k] ?? null;
if (!$p) avm_fail(400, 'proveedor');

if (($req['op'] ?? '') === 'probar') {
    if (empty($p['probar'])) avm_json(['ok' => false, 'mensaje' => 'Este proveedor todavía no tiene conexión disponible.']);
    avm_json(call_user_func($p['probar']));
}
if (($req['op'] ?? '') !== 'guardar') avm_fail(400, 'op');
$permitidos = array_column($p['campos'], 2, 0);
$vals = ['prov_' . $k => !empty($req['encendido']) ? null : false]; // null = encendido (valor por defecto)
foreach ((array) ($req['campos'] ?? []) as $c => $v) {
    if (!isset($permitidos[$c])) continue;
    $v = trim((string) $v);
    if ($permitidos[$c] === 'secreto' && $v === '') continue; // vacío = no cambiar la llave
    if (mb_strlen($v) > 500) avm_json(['ok' => false, 'error' => 'Un valor es demasiado largo.'], 400);
    $vals[$c] = $v;
}
foreach ((array) ($req['borrar'] ?? []) as $c) if (isset($permitidos[$c])) $vals[$c] = '';
avm_cfg_guardar($vals);
avm_json(['ok' => true, 'proveedores' => avm_integ_estado()]);
