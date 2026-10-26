<?php
/**
 * Darse de baja de las promociones de AVM.
 * GET muestra la confirmación (los filtros de correo abren enlaces solos, así que no da de baja al abrir).
 * POST da de baja: desde el botón de esta página o desde el "cancelar suscripción" de Gmail y Outlook.
 */
require __DIR__ . '/api/_lib.php';
header('X-Frame-Options: DENY');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');

$key = (string) ($_GET['k'] ?? '');
$sig = (string) ($_GET['s'] ?? '');
$valid = $key !== '' && preg_match('/^[mt]-[A-Za-z0-9_.~:@+-]{3,200}$/', $key)
    && hash_equals(substr(hash_hmac('sha256', $key, avm_secret()), 0, 32), $sig);
$done = false;

if ($valid && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $p = avm_get('prospects/' . $key);
    if ($p !== null) {
        $marketingLists = array_keys(array_filter(avm_coll('lists'), fn($l) => !empty($l['marketing'])));
        $p['marketing'] = false;
        $p['listas'] = array_values(array_diff((array) ($p['listas'] ?? []), $marketingLists));
        $p['baja'] = avm_iso();
        avm_put('prospects/' . $key, $p);
        // Los correos de promoción que estaban en cola para esta persona no se envían.
        foreach (avm_coll('outbox') as $id => $m) {
            if (($m['prospecto'] ?? '') === $key && ($m['tipo'] ?? '') === 'marketing' && ($m['estado'] ?? '') === 'pendiente') {
                $m['estado'] = 'descartado'; $m['motivo'] = 'Se dio de baja';
                avm_put('outbox/' . $id, $m);
            }
        }
    }
    $done = true;
    if (isset($_POST['List-Unsubscribe'])) { http_response_code(200); exit('ok'); }
}
$e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
?><!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex"><title>Promociones · AVM</title>
<style>
:root{--ink:#14181d;--muted:#5d6670;--line:#dde1e5;--paper:#f2f4ee;--card:#fff;--signal:#BBCF26;--signal-ink:#1F2508}
@media (prefers-color-scheme:dark){:root{--ink:#eef1ee;--muted:#a3abb3;--line:#2c333a;--paper:#101418;--card:#171c21}}
*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;background:var(--paper);color:var(--ink);font:16px/1.55 system-ui,-apple-system,"Segoe UI",sans-serif;padding:16px}
.card{width:100%;max-width:420px;background:var(--card);border:1px solid var(--line);border-top:4px solid var(--signal);border-radius:18px;padding:28px;display:grid;gap:14px}
img{height:38px;width:auto}h1{font-size:21px;margin:0}p{margin:0;color:var(--muted)}
button,a.btn{font:700 15px/1 inherit;padding:14px 18px;border:0;border-radius:12px;background:var(--signal);color:var(--signal-ink);cursor:pointer;text-align:center;text-decoration:none}
a{color:var(--ink)}
</style></head><body><div class="card">
<img src="img/logo-color.png" alt="AVM Operadores Mayoristas">
<?php if (!$valid): ?>
  <h1>Enlace no válido</h1>
  <p>Este enlace está incompleto o venció. Usa el enlace del último correo que recibiste, o escríbenos y te damos de baja.</p>
<?php elseif ($done): ?>
  <h1>Listo, te diste de baja</h1>
  <p>Ya no recibirás promociones de AVM. Si tienes una cotización en curso, igual te escribiremos sobre ella.</p>
  <a class="btn" href="./">Volver al sitio</a>
<?php else: ?>
  <h1>¿Dejar de recibir promociones?</h1>
  <p>Dejarás de recibir ofertas y novedades de AVM por correo. Los mensajes sobre tus cotizaciones seguirán llegando.</p>
  <form method="post"><button type="submit" style="width:100%">Darme de baja</button></form>
  <a href="./">No, volver al sitio</a>
<?php endif; ?>
</div></body></html>
