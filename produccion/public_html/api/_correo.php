<?php
/**
 * Diseño de los correos de cada agencia: su nombre, logo y colores (backoffice → Marca).
 *
 * Cada correo se arma con lo que la persona cotizó: el producto (paquete, hotel, traslado,
 * actividad, circuito, asistencia o salida grupal) define el título, la introducción, los datos
 * de la ficha y la foto; el destino aparece en todo. El texto de la plantilla del backoffice va
 * debajo como mensaje personal. Tablas y estilos en línea para Gmail, Outlook y celular.
 */
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === '_correo.php') { http_response_code(404); exit; }

const AVM_MESES = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
const AVM_MESES_L = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
const AVM_FOTOS = ['punta cana' => 'punta-cana', 'riviera maya' => 'riviera-maya', 'playa del carmen' => 'riviera-maya', 'aruba' => 'aruba',
    'panama' => 'panama', 'curazao' => 'curazao', 'cancun' => 'cancun', 'cartagena' => 'cartagena', 'san andres' => 'san-andres',
    'medellin' => 'medellin', 'cusco' => 'cusco', 'machu picchu' => 'cusco', 'miami' => 'miami', 'madrid' => 'madrid',
    // Regiones (circuitos y asistencia)
    'caribe' => 'punta-cana', 'mexico' => 'cancun', 'centroamerica' => 'panama', 'sudamerica' => 'cusco', 'norteamerica' => 'miami',
    'europa' => 'madrid', 'colombia' => 'cartagena'];

/** Producto de la solicitud, con sus textos. */
function avm_producto(array $l): array {
    $v = (array) ($l['viaje'] ?? []);
    $raw = avm_norm((string) ($v['producto'] ?? ''));
    $k = match (true) {
        in_array($raw, ['vuelos', 'vuelo'], true) => 'vuelo',
        in_array($raw, ['paquetes', 'paquete'], true) => 'paquete',
        in_array($raw, ['hoteles', 'hotel'], true) => 'hotel',
        in_array($raw, ['traslados', 'traslado'], true) || ($l['tipo'] ?? '') === 'traslado' => 'traslado',
        in_array($raw, ['actividades', 'tour'], true) || ($l['tipo'] ?? '') === 'tour' => 'actividad',
        in_array($raw, ['circuitos', 'circuito'], true) || ($l['tipo'] ?? '') === 'circuito' => 'circuito',
        $raw === 'asistencia' => 'asistencia',
        str_contains($raw, 'salida') || str_contains($raw, 'fam') || ($l['tipo'] ?? '') === 'grupo' => 'grupo',
        ($l['tipo'] ?? '') === 'agencia' => 'agencia',
        ($l['tipo'] ?? '') === 'boletin' => 'boletin',
        default => 'viaje',
    };
    return ['key' => $k] + match ($k) {
        'vuelo' => ['label' => 'Tiquetes aéreos', 'corto' => 'Vuelo'],
        'paquete' => ['label' => 'Paquete todo incluido', 'corto' => 'Paquete'],
        'hotel' => ['label' => 'Hotel', 'corto' => 'Hotel'],
        'traslado' => ['label' => 'Traslado aeropuerto – hotel', 'corto' => 'Traslado'],
        'actividad' => ['label' => 'Tours y actividades', 'corto' => 'Actividad'],
        'circuito' => ['label' => 'Circuito', 'corto' => 'Circuito'],
        'asistencia' => ['label' => 'Asistencia médica de viaje', 'corto' => 'Asistencia'],
        'grupo' => ['label' => 'Salida grupal', 'corto' => 'Salida grupal'],
        'agencia' => ['label' => 'Registro de agencia', 'corto' => 'Agencia'],
        'boletin' => ['label' => 'Boletín de promociones', 'corto' => 'Boletín'],
        default => ['label' => 'Viaje', 'corto' => 'Viaje'],
    };
}

function avm_fecha_corta(string $d): string {
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $d, $m)) return '';
    return (int) $m[3] . ' ' . AVM_MESES[(int) $m[2] - 1] . ' ' . $m[1];
}
function avm_mes(string $d): string {
    if (!preg_match('/^(\d{4})-(\d{2})/', $d, $m)) return '';
    return AVM_MESES_L[(int) $m[2] - 1] . ' de ' . $m[1];
}
function avm_noches(string $a, string $b): int {
    $x = strtotime($a); $y = strtotime($b);
    return ($x && $y && $y > $x) ? (int) round(($y - $x) / 86400) : 0;
}
function avm_plural(int $n, string $uno, string $varios): string { return $n . ' ' . ($n === 1 ? $uno : $varios); }

/**
 * Todo lo que el correo necesita saber de la solicitud: producto, destino, título,
 * introducción, filas de la ficha, foto y datos de contacto. Se guarda en la bandeja de salida.
 */
function avm_ficha(array $l): array {
    $v = (array) ($l['viaje'] ?? []);
    $p = avm_producto($l);
    // Destino real (en tours y salidas, "nombre" es el nombre del tour, no el lugar)
    $dest = (string) ($v['destino'] ?? $v['to'] ?? $v['lugar'] ?? (in_array($p['key'], ['actividad', 'circuito', 'grupo'], true) ? '' : avm_dest_of($l)));
    $nombreSalida = (string) ($v['nombre'] ?? '');
    $d1 = (string) ($v['d1'] ?? $v['llegada'] ?? $v['salida_fecha'] ?? '');
    $d2 = (string) ($v['d2'] ?? $v['salida'] ?? '');
    if ($p['key'] === 'grupo' && preg_match('/^\d{4}-\d{2}-\d{2}/', (string) ($v['salida'] ?? ''))) { $d1 = (string) $v['salida']; $d2 = (string) ($v['regreso'] ?? ''); }
    $a = avm_fecha_corta($d1); $b = avm_fecha_corta($d2); $n = avm_noches($d1, $d2);
    $ad = (int) ($v['adults'] ?? $v['adultos'] ?? 0); $ni = (int) ($v['kids'] ?? $v['ninos'] ?? 0); $ha = (int) ($v['rooms'] ?? $v['habitaciones'] ?? 0);
    $pers = (int) ($v['people'] ?? $v['pax'] ?? $v['travelers'] ?? $v['personas'] ?? 0);
    $viajeros = implode(', ', array_filter([$ad ? avm_plural($ad, 'adulto', 'adultos') : '', $ni ? avm_plural($ni, 'niño', 'niños') : '']));
    $rows = [];
    $add = function (string $lab, string $val) use (&$rows) { if (trim($val) !== '') $rows[] = [$lab, $val]; };
    $en = $dest ? " en $dest" : '';

    switch ($p['key']) {
        case 'vuelo':
            $ida = ($v['trip'] ?? 'RT') === 'OW';
            $cab = ['ECONOMY' => 'Turista', 'PREMIUM_ECONOMY' => 'Turista premium', 'BUSINESS' => 'Ejecutiva', 'FIRST' => 'Primera'][$v['cabin'] ?? ''] ?? '';
            $titulo = 'Tu vuelo' . ($dest ? " a $dest" : '');
            $intro = 'Estamos confirmando disponibilidad y tarifa de tu vuelo' . (!empty($v['from']) ? ' desde ' . $v['from'] : '') . ($dest ? " a $dest" : '') . ($ida ? ', solo ida.' : ', ida y regreso.');
            $add('Origen', (string) ($v['from'] ?? '')); $add('Destino', $dest);
            $add('Ida', $a); $add('Regreso', $ida ? 'Solo ida' : $b);
            $add('Viajeros', $viajeros); $add('Clase', $cab);
            $add('Aerolínea', (string) ($v['aerolinea'] ?? '')); $add('Vuelos', (string) ($v['vuelos'] ?? ''));
            break;
        case 'paquete':
        case 'hotel':
            $titulo = $p['key'] === 'paquete' ? 'Tu paquete a ' . ($dest ?: 'tu destino') : 'Tu hotel' . ($en ?: ' para tu viaje');
            $intro = $p['key'] === 'paquete'
                ? "Estamos armando tu paquete$en con hotel, traslados aeropuerto – hotel – aeropuerto y asistencia médica."
                : "Estamos revisando hoteles, disponibilidad y tarifas$en para tus fechas.";
            $add('Destino', $dest); $add('Hotel', (string) ($v['hotel'] ?? ''));
            $add($p['key'] === 'hotel' ? 'Estadía' : 'Fechas', $a ? ($b ? "$a – $b" : $a) . ($n ? ' · ' . avm_plural($n, 'noche', 'noches') : '') : '');
            $add('Habitación', (string) ($v['habitacion'] ?? '')); $add('Régimen', (string) ($v['regimen'] ?? ''));
            $add('Habitaciones', $ha ? (string) $ha : ''); $add('Viajeros', $viajeros);
            break;
        case 'traslado':
            $titulo = 'Tu traslado' . ($en ?: ' aeropuerto – hotel');
            $hotel = (string) ($v['hotel'] ?? '');
            $ida = ($v['trip'] ?? 'RT') === 'OW';
            $intro = 'Estamos confirmando tu traslado' . ($dest ? " desde el aeropuerto de $dest" : ' desde el aeropuerto') . ($hotel ? " hasta $hotel" : ' hasta tu hotel') . ($ida ? '.' : ', ida y regreso.');
            $add('Aeropuerto', $dest); $add('Hotel', $hotel);
            $add('Llegada', $a . (!empty($v['t1']) ? ' · vuelo ' . $v['t1'] : ''));
            $add('Regreso', $ida ? 'Solo ida' : $b);
            $add('Pasajeros', $pers ? (string) $pers : '');
            break;
        case 'actividad':
            $titulo = ($nombreSalida ?: 'Tus tours y actividades') . ($nombreSalida ? '' : $en);
            $intro = $nombreSalida ? "Estamos revisando cupos y horarios para $nombreSalida." : "Estamos buscando tours, entradas y experiencias$en para tus fechas.";
            $add('Destino', $dest ?: (string) ($v['lugar'] ?? '')); $add('Tour', $nombreSalida);
            $add('Fechas', $a ? ($b && $b !== $a ? "$a – $b" : $a) : ''); $add('Personas', $pers ? (string) $pers : '');
            break;
        case 'circuito':
            $region = (string) ($v['region'] ?? $dest);
            $mes = avm_mes((string) ($v['month'] ?? ''));
            $titulo = $nombreSalida ?: 'Tu circuito por ' . ($region ?: 'el mundo');
            $intro = 'Estamos revisando las salidas' . ($region ? " por $region" : '') . ($mes ? " en $mes" : '') . ', con itinerario, hoteles y guía.';
            $add('Región', $region); $add('Circuito', $nombreSalida); $add('Salida', $mes ?: $a); $add('Personas', $pers ? (string) $pers : '');
            break;
        case 'asistencia':
            $region = (string) ($v['region'] ?? $dest);
            $titulo = 'Tu asistencia médica' . ($region ? " para $region" : ' de viaje');
            $intro = 'Estamos cotizando la cobertura médica para tu viaje' . ($region ? " a $region" : '') . ($n ? ', por ' . avm_plural($n + 1, 'día', 'días') : '') . '.';
            $add('Destino', $region); $add('Vigencia', $a ? ($b ? "$a – $b" : $a) : ''); $add('Viajeros', $pers ? (string) $pers : $viajeros);
            break;
        case 'grupo':
            $titulo = 'Tu cupo en ' . ($nombreSalida ?: 'la salida grupal');
            $intro = 'Estamos confirmando los cupos disponibles' . ($nombreSalida ? " en $nombreSalida" : '') . '.';
            $add('Salida', $nombreSalida); $add('Destino', $dest); $add('Fechas', $a ? ($b ? "$a – $b" : $a) : '');
            break;
        case 'agencia':
            $titulo = 'Recibimos el registro de tu agencia';
            $intro = 'Validamos la información comercial y te enviamos tu usuario para el portal de reservas.';
            $add('Agencia', (string) ($l['agencia']['razonSocial'] ?? '')); $add('NIT', (string) ($l['agencia']['nit'] ?? '')); $add('RNT', (string) ($l['agencia']['rnt'] ?? ''));
            break;
        case 'boletin':
            $titulo = 'Ya estás en la lista de promociones';
            $intro = 'Te enviaremos las mejores tarifas de temporada antes que nadie.';
            break;
        default:
            $titulo = 'Recibimos tu solicitud' . ($dest ? " para $dest" : '');
            $intro = 'Un asesor de ' . avm_marca()['name'] . ' revisa la disponibilidad y te envía la cotización con el precio final.';
            $add('Destino', $dest); $add('Fechas', $a ? ($b ? "$a – $b" : $a) : ''); $add('Viajeros', $viajeros);
    }
    if (!empty($v['adicionales'])) $rows[] = ['Adicionales', (string) $v['adicionales']];
    if (!empty($v['precioVisto'])) $rows[] = ['Precio visto', (string) $v['precioVisto']];
    if (!empty($l['numero'])) $rows[] = ['Solicitud', (string) $l['numero']];

    return [
        'producto' => $p['key'], 'productoLabel' => $p['label'], 'productoCorto' => $p['corto'],
        'destino' => $dest ?: (string) ($v['region'] ?? ''), 'titulo' => $titulo, 'intro' => $intro, 'filas' => $rows,
        'resumen' => $p['corto'] . ($dest ? " en $dest" : (!empty($v['region']) ? ' · ' . $v['region'] : '')) . ($a ? " · $a" . ($b && $b !== $a ? " – $b" : '') : ''),
        'numero' => (string) ($l['numero'] ?? ''), 'nombre' => (string) ($l['nombre'] ?? ''), 'celular' => (string) ($l['celular'] ?? ''),
        'correo' => (string) ($l['correo'] ?? ''), 'comentarios' => (string) ($l['comentarios'] ?? ''),
        'foto' => avm_foto_destino($dest ?: (string) ($v['region'] ?? '') ?: $nombreSalida),
        'tipo' => (string) ($l['tipo'] ?? ''), 'origen' => (string) ($l['origen'] ?? ''), 'hotel' => (string) ($v['hotel'] ?? ''), 'precioVisto' => (string) ($v['precioVisto'] ?? ''),
    ];
}

function avm_site_url(): string { return avm_tenant_url(); }
function avm_foto_destino(string $dest): string {
    $n = avm_norm($dest);
    foreach (AVM_FOTOS as $k => $slug) if ($n !== '' && str_contains($n, $k)) return $slug;
    return 'portada';
}
function avm_wa_num(string $tel): string {
    $t = preg_replace('/\D/', '', $tel);
    return (strlen($t) === 10 && $t[0] === '3') ? '57' . $t : $t;
}

/* ---------- Enlace para darse de baja (firmado, para que nadie dé de baja a otro) ---------- */
function avm_secret(): string { return avm_app_secret() . '|' . (($GLOBALS['AVM_T'] ?? null)['id'] ?? 0); }
function avm_baja_url(string $key): string {
    if ($key === '') return '';
    return avm_site_url() . '/baja.php?k=' . rawurlencode($key) . '&s=' . substr(hash_hmac('sha256', $key, avm_secret()), 0, 32);
}

/** Filas del desglose interno: etiqueta y valor de cada concepto. */
function avm_desglose_filas(array $l): array {
    $p = fn($x) => rtrim(rtrim(number_format((float) $x, 2, ',', '.'), '0'), ',') . ' %';
    return array_values(array_filter([
        isset($l['netoProv']) ? ['Neto del proveedor', $l['netoProv']] : null,
        isset($l['comLite']) ? ['Comisión LiteAPI (a favor de la agencia)', $l['comLite']] : null,
        ['Tarifa LiteAPI', $l['neto'] ?? 0],
        ['Markup ' . $p($l['markupPct'] ?? 0), $l['markup'] ?? 0],
        ['Fee fijo', $l['fijo'] ?? 0],
        ['Fee general ' . $p($l['feePct'] ?? 0), $l['fee'] ?? 0],
        ['Fee bancario ' . $p($l['bancoPct'] ?? 0), $l['banco'] ?? 0],
        ['Comisión operador ' . $p($l['operadorPct'] ?? 0), $l['operador'] ?? 0],
        abs((float) ($l['redondeo'] ?? 0)) >= 0.01 ? ['Redondeo', $l['redondeo']] : null,
        ['Total al cliente', $l['total'] ?? 0],
        ['Ganancia de la agencia', $l['ganancia'] ?? $l['margen'] ?? 0],
    ]));
}
/** Desglose en el aviso interno (neto, comisiones y fees). Nunca va en correos al cliente. */
function avm_desglose_html(array $liq, callable $e, string $font, string $mono, string $ink, string $muted, string $line): string {
    if (empty($liq['items'])) return '';
    $trm = (float) ($liq['trm'] ?? 0);
    $usd = fn($x) => 'USD ' . number_format((float) $x, 2, ',', '.');
    $cop = fn($x) => $trm > 0 ? 'COP ' . number_format(round((float) $x * $trm), 0, ',', '.') : '';
    $html = '';
    foreach ($liq['items'] as $it) {
        $l = (array) ($it['liq'] ?? []);
        $rows = '';
        foreach (avm_desglose_filas($l) as $i => [$lab, $val]) {
            $fuerte = $lab === 'Total al cliente' || $lab === 'Ganancia de la agencia';
            $bt = $i === 0 ? 'none' : '1px solid ' . $line;
            $rows .= '<tr><td style="padding:8px 0;border-top:' . $bt . ';font:' . ($fuerte ? 700 : 500) . ' 14px/1.4 ' . $font . ';color:' . ($fuerte ? $ink : $muted) . '">' . $e($lab) . '</td>'
                . '<td align="right" style="padding:8px 0;border-top:' . $bt . ';font:' . ($fuerte ? 700 : 600) . ' 14px/1.4 ' . $font . ';color:' . $ink . ';white-space:nowrap">' . $e($usd($val))
                . ($trm > 0 ? '<br><span style="font-weight:400;font-size:12px;color:' . $muted . '">' . $e($cop($val)) . '</span>' : '') . '</td></tr>';
        }
        $notas = [];
        if (!empty($l['taxOut'])) $notas[] = 'Impuestos a pagar en el hotel: ' . $usd($l['taxOut']) . '.';
        if (!empty($l['ssp'])) $notas[] = 'Precio público sugerido por LiteAPI: ' . $usd($l['ssp']) . (($l['total'] ?? 0) < $l['ssp'] ? ' (el total está por debajo: LiteAPI no permite publicar por debajo de este valor).' : '.');
        $html .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid ' . $line . ';border-radius:14px;margin:0 0 26px"><tr><td style="padding:16px 20px">'
            . '<p style="margin:0 0 4px;font:700 11px/1.4 ' . $mono . ';letter-spacing:.08em;text-transform:uppercase;color:' . $muted . '">Desglose interno · ' . $e($it['producto'] ?? '') . '</p>'
            . '<p style="margin:0 0 10px;font:600 15px/1.4 ' . $font . ';color:' . $ink . '">' . $e($it['nombre'] ?? '') . '</p>'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0">' . $rows . '</table>'
            . ($notas ? '<p style="margin:10px 0 0;font:400 12.5px/1.5 ' . $font . ';color:' . $muted . '">' . $e(implode(' ', $notas)) . '</p>' : '')
            . ($trm > 0 ? '<p style="margin:6px 0 0;font:400 12.5px/1.5 ' . $font . ';color:' . $muted . '">Pesos con TRM de ' . $e(number_format($trm, 2, ',', '.')) . '.</p>' : '')
            . '</td></tr></table>';
    }
    return $html;
}

/** Correo completo en HTML con la marca de la agencia. $m es el documento de la bandeja de salida. */
function avm_email_html(array $m, array $contact): string {
    $e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    $site = avm_site_url();
    $f = (array) ($m['ficha'] ?? []);
    $interno = ($m['tipo'] ?? '') === 'interno';
    $marketing = ($m['tipo'] ?? '') === 'marketing';
    $font = "'Outfit','Hanken Grotesk',Helvetica,Arial,sans-serif";
    $mono = "'Courier New',Courier,monospace";
    $B = avm_marca(); $nom = (string) $B['name'];
    $ink = '#14181D'; $muted = '#5B6560'; $line = '#DCE0D6'; $soft = '#F6F7F2';
    // Color de la marca para botones; para textos y enlaces, el oscuro de la marca si el acento es claro
    $green = avm_hex((string) $B['accent'], '#BBCF26'); $greenInk = avm_tinta($green); $barra = avm_hex((string) $B['dark'], '#1A2024');
    $olive = avm_luz($green) > 0.3 ? (avm_luz($barra) < 0.2 ? $barra : $ink) : $green;
    $titulo = (string) ($f['titulo'] ?? ($m['asunto'] ?? ''));
    $prod = (string) ($f['productoCorto'] ?? '');
    $dest = (string) ($f['destino'] ?? '');

    // Filas de la ficha (en el aviso interno, primero los datos de contacto)
    $filas = (array) ($f['filas'] ?? []);
    if ($interno) {
        $contacto = array_values(array_filter([['Nombre', $f['nombre'] ?? ''], ['Celular', $f['celular'] ?? ''], ['Correo', $f['correo'] ?? '']], fn($r) => trim((string) $r[1]) !== ''));
        $extra = !empty($f['comentarios']) ? [['Comentarios', $f['comentarios']]] : [];
        $filas = array_merge($contacto, $filas, $extra);
    }
    $rows = '';
    foreach ($filas as $i => [$lab, $val]) {
        $bt = $i === 0 ? 'none' : '1px solid ' . $line;
        $rows .= '<tr><td style="padding:11px 0;border-top:' . $bt . ';font:700 11px/1.4 ' . $mono . ';letter-spacing:.08em;text-transform:uppercase;color:' . $muted . ';width:118px;vertical-align:top">' . $e($lab) . '</td>'
            . '<td style="padding:11px 0;border-top:' . $bt . ';font:600 15px/1.45 ' . $font . ';color:' . $ink . '">' . $e($val) . '</td></tr>';
    }
    $ficha = $rows ? '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:' . $soft . ';border-radius:14px;margin:6px 0 26px"><tr><td style="padding:8px 20px 8px">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0">' . $rows . '</table></td></tr></table>' : '';

    $btn = fn($href, $txt, $bg, $fg) => '<table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 8px 12px 0;display:inline-table"><tr><td style="border-radius:12px;background:' . $bg . '">'
        . '<a href="' . $e($href) . '" style="display:inline-block;padding:15px 22px;font:700 15px/1 ' . $font . ';color:' . $fg . ';text-decoration:none;border-radius:12px">' . $e($txt) . '</a></td></tr></table>';
    $chip = fn($txt, $bg, $fg) => '<span style="display:inline-block;padding:6px 10px;border-radius:8px;background:' . $bg . ';font:700 11px/1 ' . $mono . ';letter-spacing:.08em;text-transform:uppercase;color:' . $fg . '">' . $e($txt) . '</span>';

    if ($interno) {
        // ---------- Aviso al comercial ----------
        $origen = match ($f['tipo'] ?? '') {
            'busqueda' => 'Buscó precios en línea en el sitio',
            'viajero', 'tour', 'traslado' => !empty($f['precioVisto']) ? 'Eligió una opción con precio en línea y quiere reservarla' : 'Pidió que un asesor le cotice',
            'agencia' => 'Agencia que quiere trabajar con ' . $nom,
            'boletin' => 'Se suscribió al boletín',
            default => 'Solicitud desde el sitio',
        };
        $wa = avm_wa_num((string) ($f['celular'] ?? ''));
        $saludo = 'Hola' . (!empty($f['nombre']) ? ' ' . explode(' ', (string) $f['nombre'])[0] : '') . ', te escribimos de ' . $nom . ' por tu cotización de '
            . mb_strtolower($prod ?: 'viaje', 'UTF-8') . ($dest ? " en $dest" : '') . (!empty($f['numero']) ? ' (' . $f['numero'] . ')' : '') . '.';
        $head = '<p style="margin:0 0 14px">' . $chip('Nueva solicitud', $green, $greenInk) . ($prod ? ' ' . $chip($prod, $soft, $ink) : '') . '</p>'
            . '<h1 style="margin:0 0 8px;font:700 28px/1.15 ' . $font . ';color:' . $ink . '">' . $e($prod ? $prod . ($dest ? " en $dest" : '') : 'Nueva solicitud') . '</h1>'
            . '<p style="margin:0 0 22px;font:500 15px/1.5 ' . $font . ';color:' . $muted . '">' . $e($origen) . '. Escríbele hoy, mientras está decidiendo.</p>';
        $cta = ($wa ? $btn('https://wa.me/' . $wa . '?text=' . rawurlencode($saludo), 'Escribirle por WhatsApp', $green, $greenInk) : '')
            . (!empty($f['correo']) ? $btn('mailto:' . $f['correo'] . '?subject=' . rawurlencode('Tu cotización de ' . mb_strtolower($prod ?: 'viaje', 'UTF-8') . ($dest ? " en $dest" : '') . ' · ' . $nom), 'Responder por correo', $ink, '#FFFFFF') : '')
            . '<p style="margin:2px 0 0;font:400 13.5px/1.5 ' . $font . '"><a href="' . $e($site . '/#admin') . '" style="color:' . $olive . '">Ver la solicitud en el backoffice</a></p>';
        $body = $head . $ficha . avm_desglose_html((array) ($m['liq'] ?? []), $e, $font, $mono, $ink, $muted, $line) . $cta;
        $foto = '';
    } else {
        // ---------- Correo al cliente ----------
        $paras = '';
        foreach (preg_split('/\n\s*\n/', trim((string) ($m['cuerpo'] ?? ''))) as $p) {
            if (trim($p) === '') continue;
            $paras .= '<p style="margin:0 0 16px;font:400 16px/1.6 ' . $font . ';color:' . $ink . '">' . nl2br($e(trim($p))) . '</p>';
        }
        $eyebrow = $marketing ? 'Promociones ' . $nom . ($dest ? ' · ' . $dest : '') : trim(($prod ?: 'Tu viaje') . ($dest ? ' · ' . $dest : ''));
        $intro = !$marketing && !empty($f['intro']) ? '<p style="margin:0 0 20px;font:500 17px/1.55 ' . $font . ';color:' . $ink . '">' . $e($f['intro']) . '</p>' : '';
        // Próximos pasos
        $pasos = $marketing ? '' : '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 26px"><tr>'
            . implode('', array_map(fn($i, $t) => '<td width="33%" style="vertical-align:top;padding:0 ' . ($i < 2 ? '10px' : '0') . ' 0 0">'
                . '<div style="border-top:3px solid ' . ($i === 0 ? $green : $line) . ';padding-top:10px">'
                . '<span style="font:700 11px/1 ' . $mono . ';color:' . ($i === 0 ? $olive : $muted) . '">0' . ($i + 1) . '</span>'
                . '<p style="margin:6px 0 0;font:600 13.5px/1.4 ' . $font . ';color:' . ($i === 0 ? $ink : $muted) . '">' . $e($t) . '</p></div></td>',
                [0, 1, 2], ['Recibimos tu solicitud', 'Un asesor te envía la cotización', 'Confirmas y reservas']))
            . '</tr></table>';
        $waAvm = avm_wa_num((string) ($contact['waMain'] ?? ''));
        $cta = $marketing
            ? (!empty($f['url']) ? $btn((string) $f['url'], $dest ? 'Ver el paquete' : 'Ver promociones', $green, $greenInk) : '')
              . ($waAvm ? $btn('https://wa.me/' . $waAvm . '?text=' . rawurlencode('Hola, me interesa la promoción' . ($dest ? " de $dest" : '') . '.'), 'Escribir por WhatsApp', $ink, '#FFFFFF') : '')
            : (($waAvm ? $btn('https://wa.me/' . $waAvm . '?text=' . rawurlencode('Hola, quiero continuar con mi cotización de ' . mb_strtolower($prod ?: 'viaje', 'UTF-8') . ($dest ? " en $dest" : '') . (!empty($f['numero']) ? ' (' . $f['numero'] . ')' : '') . '.'), 'Escribir por WhatsApp', $green, $greenInk) : '')
            . '<p style="margin:2px 0 0;font:400 14px/1.5 ' . $font . ';color:' . $muted . '">O responde este correo y te contestamos por aquí.</p>');
        $head = '<p style="margin:0 0 10px;font:700 11px/1 ' . $mono . ';letter-spacing:.14em;text-transform:uppercase;color:' . $olive . '">' . $e($eyebrow) . '</p>'
            . '<h1 style="margin:0 0 16px;font:700 28px/1.15 ' . $font . ';color:' . $ink . '">' . $e($marketing ? ($m['asunto'] ?? '') : $titulo) . '</h1>';
        $body = $head . $intro . $paras . $ficha . $pasos . $cta;
        $foto = '<tr><td style="position:relative"><img src="' . $e($site . '/img/correo/' . ($f['foto'] ?? avm_foto_destino($dest)) . '.jpg') . '" width="600" alt="' . $e($dest ?: 'Viajes ' . $nom) . '" style="display:block;width:100%;max-width:600px;height:auto;border:0"></td></tr>';
    }

    $num = !empty($f['numero']) ? '<span style="font:700 11px/1 ' . $mono . ';letter-spacing:.08em;color:' . $muted . '">' . $e($f['numero']) . '</span>' : '';
    $sedes = implode(' · ', array_map(fn($s) => $e(($s[0] ?? '') . ': ' . ($s[1] ?? '')), (array) ($contact['sedes'] ?? [])));
    $tels = $e(implode(' · ', array_slice((array) ($contact['sales'] ?? []), 0, 2)));
    $legal = $interno ? 'Aviso automático del sitio de ' . $e($nom) . '.'
        : ($marketing ? 'Recibes este correo porque aceptaste recibir promociones de ' . $e($nom) . '. <a href="' . $e(avm_baja_url((string) ($m['prospecto'] ?? ''))) . '" style="color:#FFFFFF">Darme de baja</a>.'
            : 'Recibes este correo porque cotizaste en el sitio de ' . $e($nom) . '.');
    $logo = avm_logo_url(); $logoOsc = avm_logo_url(true);
    $marcaTop = $logo ? '<img src="' . $e($logo) . '" width="120" alt="' . $e($nom) . '" style="display:block;width:120px;height:auto;border:0">'
        : '<span style="font:700 20px/1.2 ' . $font . ';color:' . $ink . '">' . $e($nom) . '</span>';
    $pre = $interno ? (($f['resumen'] ?? '') . ' · ' . ($f['celular'] ?? '')) : (($f['intro'] ?? '') ?: 'Recibimos tu solicitud ' . ($f['numero'] ?? '') . '.');

    return '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<meta name="color-scheme" content="light"><meta name="supported-color-schemes" content="light"><title>' . $e($m['asunto'] ?? $nom) . '</title></head>'
        . '<body style="margin:0;padding:0;background:#F2F4EE">'
        . '<div style="display:none;max-height:0;overflow:hidden;opacity:0">' . $e($pre) . '</div>'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F2F4EE"><tr><td align="center" style="padding:24px 12px">'
        . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#FFFFFF;border-radius:20px;overflow:hidden">'
        . '<tr><td style="padding:22px 28px;border-bottom:4px solid ' . $green . '"><table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>'
        . '<td>' . $marcaTop . '</td>'
        . '<td align="right" style="vertical-align:middle">' . $num . '</td></tr></table></td></tr>'
        . $foto
        . '<tr><td style="padding:30px 28px 34px">' . $body . '</td></tr>'
        . '<tr><td style="background:' . $barra . ';padding:26px 28px">'
        . ($logoOsc && ($B['logoDark'] ?? '') ? '<img src="' . $e($logoOsc) . '" width="96" alt="' . $e($nom) . '" style="display:block;width:96px;height:auto;border:0;margin:0 0 14px">' : '')
        . '<p style="margin:0 0 6px;font:600 14px/1.5 ' . $font . ';color:#F1F3EC">' . $e($nom . ($B['legal'] ? ' · ' . $B['legal'] : '')) . '</p>'
        . ($sedes ? '<p style="margin:0 0 6px;font:400 13px/1.5 ' . $font . ';color:#A9B3AC">' . $sedes . '</p>' : '')
        . '<p style="margin:0 0 14px;font:400 13px/1.5 ' . $font . ';color:#A9B3AC">' . $tels . ($tels && !empty($contact['email']) ? ' · ' : '') . $e($contact['email'] ?? '') . '</p>'
        . '<p style="margin:0;font:400 12px/1.5 ' . $font . ';color:#8A948E">' . $legal . '</p>'
        . '</td></tr></table>'
        . '</td></tr></table></body></html>';
}
