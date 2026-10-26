<?php
/**
 * Campañas de correo: enviar una plantilla a una lista de marketing, opcionalmente con un
 * destino del sitio (foto, ficha y botón "Ver el paquete").
 *
 * campaigns/<id> = { nombre, lista, plantilla, asunto, cuerpo, destino (slug), estado, total,
 *                    enviados, errores, pendientes, descartados, creado, iniciada, terminada }
 * Estados: borrador → enviando → enviada; también pausada y cancelada.
 * Al enviar, cada destinatario queda en la bandeja de salida (outbox/c-<campaña>-<persona>);
 * el cron los manda por tandas sin pasar el límite diario de Resend.
 */
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === '_campanas.php') { http_response_code(404); exit; }

/** Quiénes reciben la campaña: en la lista, con permiso de promociones y correo válido. */
function avm_campana_destinatarios(string $lista): array {
    $out = [];
    foreach (avm_coll('prospects') as $key => $p) {
        if (empty($p['marketing']) || !in_array($lista, (array) ($p['listas'] ?? []), true)) continue;
        if (!filter_var($p['correo'] ?? '', FILTER_VALIDATE_EMAIL)) continue;
        $out[$key] = $p;
    }
    return $out;
}

/** Ficha del destino elegido para la campaña (o vacía si la campaña no tiene destino). */
function avm_campana_ficha(array $camp, string $campId): array {
    $slug = (string) ($camp['destino'] ?? '');
    $d = $slug !== '' ? avm_get('destinations/' . $slug) : null;
    $site = avm_site_url();
    $utm = '?utm_source=correo&utm_medium=email&utm_campaign=' . rawurlencode($campId);
    if (!$d) return ['url' => $site . '/' . $utm, 'foto' => 'portada', 'filas' => []];
    $txt = (avm_get('site/content') ?? [])['txt'] ?? [];
    $nombre = (string) ($d['name'] ?? $slug);
    $filas = [['Destino', $nombre . (!empty($d['country']) ? ', ' . $d['country'] : '')]];
    if (!empty($d['hotel'])) $filas[] = ['Hotel', (string) $d['hotel']];
    if (!empty($d['nights'])) $filas[] = ['Duración', avm_plural((int) $d['nights'], 'noche', 'noches')];
    $tags = array_slice((array) ($d['tags'] ?? []), 0, 3);
    if ($tags) $filas[] = ['Incluye', implode(' · ', $tags)];
    $o = (array) ($d['offer'] ?? []);
    $oferta = '';
    if (!empty($o['pct']) && (empty($o['until']) || $o['until'] >= date('Y-m-d'))) {
        $oferta = '−' . (int) $o['pct'] . '%' . (!empty($o['until']) ? ' hasta el ' . avm_fecha_corta((string) $o['until']) : '');
        $filas[] = ['Oferta', $oferta];
    }
    if (!empty($txt['showPrices'])) {
        $precios = array_filter(array_map(fn($s) => (float) ($s['usd'] ?? $s[2] ?? $s['price'] ?? 0), (array) ($d['seasons'] ?? [])));
        if ($precios) {
            $min = min($precios);
            if (!empty($o['pct'])) $min = round($min * (1 - $o['pct'] / 100));
            $filas[] = ['Desde', 'USD ' . number_format($min, 0, ',', '.') . ' por persona'];
        }
    }
    return ['destino' => $nombre, 'hotel' => (string) ($d['hotel'] ?? ''), 'noches' => (string) ($d['nights'] ?? ''), 'oferta' => $oferta,
        'url' => $site . '/' . $utm . '#destino-' . $slug, 'foto' => avm_foto_destino($nombre), 'filas' => $filas, 'productoCorto' => 'Paquete'];
}

/** Documento de la bandeja de salida para una persona. */
function avm_campana_mensaje(array $camp, string $campId, string $key, array $p, array $ficha, array $contact): array {
    $vars = [
        'nombre' => explode(' ', trim((string) ($p['nombre'] ?? '')))[0] ?: 'viajero', 'nombre_completo' => (string) ($p['nombre'] ?? ''),
        'destino' => $ficha['destino'] ?? 'tu próximo viaje', 'hotel' => $ficha['hotel'] ?? '', 'noches' => $ficha['noches'] ?? '',
        'oferta' => $ficha['oferta'] ?? '', 'correo_avm' => $contact['email'] ?? '', 'correo_agencia' => $contact['email'] ?? '', 'marca' => avm_marca()['name'],
        'asesor_whatsapp' => '+' . preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})$/', '$1 $2 $3 $4', (string) ($contact['waMain'] ?? '')),
    ];
    return [
        'para' => $p['correo'], 'nombre' => $p['nombre'] ?? '', 'asunto' => avm_fill((string) ($camp['asunto'] ?? ''), $vars),
        'cuerpo' => avm_fill((string) ($camp['cuerpo'] ?? ''), $vars), 'plantilla' => $camp['nombre'] ?? 'Campaña', 'tipo' => 'marketing',
        'automatizacion' => 'Campaña: ' . ($camp['nombre'] ?? ''), 'campana' => $campId, 'prospecto' => $key, 'ficha' => $ficha,
        'estado' => 'pendiente', 'creado' => avm_iso(),
    ];
}

/** Pone en cola la campaña para todos sus destinatarios. Devuelve cuántos quedaron en cola. */
function avm_campana_encolar(string $campId): int {
    $camp = avm_get('campaigns/' . $campId);
    if (!$camp) throw new RuntimeException('no_existe');
    if (empty($camp['lista']) || trim((string) ($camp['asunto'] ?? '')) === '' || trim((string) ($camp['cuerpo'] ?? '')) === '') throw new RuntimeException('incompleta');
    $contact = (avm_get('site/content') ?? [])['contact'] ?? [];
    $ficha = avm_campana_ficha($camp, $campId);
    $n = 0;
    foreach (avm_campana_destinatarios((string) $camp['lista']) as $key => $p) {
        $path = 'outbox/c-' . substr($campId, 0, 60) . '-' . substr(hash('sha1', $key), 0, 16);
        if (avm_get($path) !== null) continue; // ya estaba en cola: no se duplica
        avm_put($path, avm_campana_mensaje($camp, $campId, $key, $p, $ficha, $contact));
        $n++;
    }
    $camp['estado'] = 'enviando';
    $camp['iniciada'] = $camp['iniciada'] ?? avm_iso();
    $camp['total'] = (int) ($camp['total'] ?? 0) + $n;
    avm_put('campaigns/' . $campId, $camp);
    return $n;
}

/** Actualiza el avance de las campañas en curso y cierra las terminadas. Lo llama el cron. */
function avm_campanas_avance(): string {
    $camps = array_filter(avm_coll('campaigns'), fn($c) => in_array($c['estado'] ?? '', ['enviando', 'pausada', 'cancelada'], true) && empty($c['terminada']));
    if (!$camps) return 'sin campañas en curso';
    $cuenta = [];
    foreach (avm_coll('outbox') as $id => $m) {
        $c = (string) ($m['campana'] ?? '');
        if ($c === '' || !isset($camps[$c])) continue;
        if (($camps[$c]['estado'] ?? '') === 'cancelada' && ($m['estado'] ?? '') === 'pendiente') {
            $m['estado'] = 'descartado'; $m['motivo'] = 'Campaña cancelada'; avm_put('outbox/' . $id, $m);
        }
        $cuenta[$c][$m['estado'] ?? 'pendiente'] = ($cuenta[$c][$m['estado'] ?? 'pendiente'] ?? 0) + 1;
    }
    $res = [];
    foreach ($camps as $id => $camp) {
        $k = $cuenta[$id] ?? [];
        $camp['enviados'] = $k['enviado'] ?? 0; $camp['errores'] = $k['error'] ?? 0;
        $camp['pendientes'] = $k['pendiente'] ?? 0; $camp['descartados'] = $k['descartado'] ?? 0;
        if ($camp['pendientes'] === 0 && in_array($camp['estado'], ['enviando', 'cancelada'], true)) {
            $camp['terminada'] = avm_iso();
            if ($camp['estado'] === 'enviando') $camp['estado'] = 'enviada';
        }
        avm_put('campaigns/' . $id, $camp);
        $res[] = ($camp['nombre'] ?? $id) . ": {$camp['enviados']}/" . ($camp['total'] ?? 0);
    }
    return 'campañas: ' . implode(', ', $res);
}

/** Envía un solo correo ya armado (para la prueba de una campaña). */
function avm_enviar_uno(array $m, array $contact): array {
    $c = ['resend_key' => (string) avm_cfg('resend_key', ''), 'mail_from' => avm_mail_from(), 'mail_reply_to' => (string) avm_cfg('mail_reply_to', '')];
    if (empty($c['resend_key']) || empty($c['mail_from'])) return ['ok' => false, 'error' => 'El envío de correos no está configurado para esta agencia.'];
    $payload = ['from' => $c['mail_from'], 'to' => [$m['para']], 'subject' => '[Prueba] ' . ($m['asunto'] ?? ''), 'html' => avm_email_html($m, $contact), 'text' => $m['cuerpo'] ?? ''];
    if (!empty($c['mail_reply_to'])) $payload['reply_to'] = $c['mail_reply_to'];
    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $c['resend_key'], 'Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE)]);
    $body = curl_exec($ch); $status = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    if ($status >= 200 && $status < 300) return ['ok' => true];
    $err = json_decode((string) $body, true)['message'] ?? 'Resend respondió con error ' . $status . '.';
    return ['ok' => false, 'error' => $err];
}
