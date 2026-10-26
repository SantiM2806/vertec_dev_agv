<?php
/**
 * Acciones de campañas desde el backoffice (solo administradores).
 * POST { op: "prueba", id, para }  → envía ya mismo un correo de prueba de la campaña
 * POST { op: "enviar", id }        → pone la campaña en cola para toda la lista
 */
require __DIR__ . '/_lib.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') avm_fail(405, 'metodo');
if (!avm_is_admin()) avm_fail(401, 'sesion');
session_write_close();
avm_check_origin();

$req = json_decode((string) file_get_contents('php://input', false, null, 0, 64 * 1024), true);
if (!is_array($req)) avm_fail(400, 'json');
$id = (string) ($req['id'] ?? '');
if (!preg_match(AVM_SEG, $id)) avm_fail(400, 'id');
$camp = avm_get('campaigns/' . $id);
if (!$camp) avm_json(['ok' => false, 'error' => 'Guarda la campaña antes de probarla o enviarla.'], 404);
$contact = (avm_get('site/content') ?? [])['contact'] ?? [];

switch ($req['op'] ?? '') {
    case 'prueba':
        $para = strtolower(trim((string) ($req['para'] ?? '')));
        if (!filter_var($para, FILTER_VALIDATE_EMAIL)) avm_json(['ok' => false, 'error' => 'Escribe un correo válido para la prueba.'], 400);
        $ejemplo = ['nombre' => 'Laura Gómez', 'correo' => $para];
        $m = avm_campana_mensaje($camp, $id, 'prueba', $ejemplo, avm_campana_ficha($camp, $id), $contact);
        avm_json(avm_enviar_uno($m, $contact));
    case 'enviar':
        if (!in_array($camp['estado'] ?? 'borrador', ['borrador', 'enviando', 'pausada'], true)) avm_json(['ok' => false, 'error' => 'Esta campaña ya terminó.'], 409);
        try { $n = avm_campana_encolar($id); }
        catch (RuntimeException $e) { avm_json(['ok' => false, 'error' => 'La campaña necesita lista, asunto y mensaje.'], 400); }
        avm_json(['ok' => true, 'enCola' => $n]);
    default:
        avm_fail(400, 'op');
}
