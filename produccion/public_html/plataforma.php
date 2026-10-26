<?php
/**
 * Panel de la plataforma (dueño del sistema): crea agencias, sus dominios, su administrador inicial,
 * sus llaves de proveedores y su estado (activa o suspendida).
 * ?salir=1 cierra la sesión.
 */
require __DIR__ . '/api/_nucleo.php';
avm_session();
header('X-Frame-Options: DENY');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex');
$e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$pdo = avm_db();
$nombreP = avm_plataforma_nombre();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrfOk = fn() => hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''));
$aviso = ''; $error = ''; $claveNueva = null;

if (isset($_GET['salir'])) { unset($_SESSION['super_id']); header('Location: plataforma.php'); exit; }

/* ---------- Ingreso ---------- */
if (!avm_is_super()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ip = substr(hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|super'), 0, 40);
        $pdo->prepare('DELETE FROM avm_rate WHERE t < ?')->execute([time() - 900]);
        $n = $pdo->prepare('SELECT COUNT(*) FROM avm_rate WHERE k = ? AND t > ?'); $n->execute([$ip, time() - 900]);
        if (!$csrfOk()) $error = 'La página expiró. Intenta de nuevo.';
        elseif ((int) $n->fetchColumn() >= 5) $error = 'Demasiados intentos. Espera 15 minutos.';
        else {
            $st = $pdo->prepare("SELECT id, pass FROM avm_users WHERE tenant = 0 AND role = 'super' AND email = ?");
            $st->execute([strtolower(trim((string) ($_POST['correo'] ?? '')))]);
            $u = $st->fetch();
            if ($u && password_verify((string) ($_POST['clave'] ?? ''), $u['pass'])) {
                session_regenerate_id(true); $_SESSION['super_id'] = (int) $u['id'];
                $pdo->prepare('UPDATE avm_users SET last_login = NOW() WHERE id = ?')->execute([$u['id']]);
                header('Location: plataforma.php'); exit;
            }
            $pdo->prepare('INSERT INTO avm_rate (k, t) VALUES (?, ?)')->execute([$ip, time()]);
            $error = 'Correo o contraseña incorrectos.';
        }
    }
    $vista = 'ingreso';
} else {
    $vista = isset($_GET['t']) ? 'agencia' : 'lista';
    /* ---------- Acciones ---------- */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!$csrfOk()) $error = 'La página expiró. Intenta de nuevo.';
        else try {
            $op = (string) ($_POST['op'] ?? '');
            $slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
            $doms = implode(',', array_filter(array_map(fn($d) => preg_replace('/^www\./', '', strtolower(trim($d))), preg_split('/[\s,]+/', (string) ($_POST['domains'] ?? ''))),
                fn($d) => preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $d)));
            if ($op === 'crear') {
                $nombre = trim((string) ($_POST['name'] ?? ''));
                $correo = strtolower(trim((string) ($_POST['admin'] ?? '')));
                if ($nombre === '' || !preg_match('/^[a-z0-9][a-z0-9-]{1,58}[a-z0-9]$/', $slug)) throw new RuntimeException('Escribe el nombre y un subdominio válido (minúsculas, números y guiones).');
                if (in_array($slug, ['www', 'api', 'admin', 'plataforma', 'mail', 'app'], true)) throw new RuntimeException('Ese subdominio está reservado.');
                if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Escribe el correo del administrador de la agencia.');
                $cfg = !empty($_POST['liteapi_demo']) ? ['liteapi_demo' => true] : [];
                $pdo->prepare("INSERT INTO avm_tenants (slug, name, domains, estado, plan, config) VALUES (?, ?, ?, 'activa', ?, ?)")
                    ->execute([$slug, $nombre, $doms, (string) ($_POST['plan'] ?? 'demo') === 'cliente' ? 'cliente' : 'demo', json_encode((object) $cfg)]);
                $tid = (int) $pdo->lastInsertId();
                $n = avm_sembrar($tid);
                // Nombre de la marca en el contenido de la agencia
                avm_use_tenant(avm_tenant_fila('id', (string) $tid));
                $c = avm_get('site/content') ?? [];
                $c['brand'] = array_merge((array) ($c['brand'] ?? []), ['name' => $nombre]);
                $c['contact'] = array_merge((array) ($c['contact'] ?? []), ['email' => $correo, 'notify' => [$correo]]);
                avm_put('site/content', $c);
                avm_use_tenant(null);
                $claveNueva = [$correo, rtrim(strtr(base64_encode(random_bytes(12)), '+/', 'Ab'), '=')];
                $pdo->prepare("INSERT INTO avm_users (tenant, email, pass, role) VALUES (?, ?, ?, 'admin')")->execute([$tid, $correo, password_hash($claveNueva[1], PASSWORD_DEFAULT)]);
                $aviso = "Agencia creada con $n documentos de contenido inicial.";
                $_GET['t'] = $tid; $vista = 'agencia';
            } elseif ($op === 'guardar' || $op === 'clave') {
                $t = avm_tenant_fila('id', (string) (int) ($_POST['id'] ?? 0));
                if (!$t) throw new RuntimeException('La agencia no existe.');
                if ($op === 'clave') {
                    $correo = strtolower(trim((string) ($_POST['admin'] ?? '')));
                    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Escribe un correo válido.');
                    $claveNueva = [$correo, rtrim(strtr(base64_encode(random_bytes(12)), '+/', 'Ab'), '=')];
                    $pdo->prepare("INSERT INTO avm_users (tenant, email, pass, role) VALUES (?, ?, ?, 'admin') ON DUPLICATE KEY UPDATE pass = VALUES(pass)")
                        ->execute([$t['id'], $correo, password_hash($claveNueva[1], PASSWORD_DEFAULT)]);
                    $aviso = 'Contraseña creada. Compártela con la agencia por un canal seguro; no se vuelve a mostrar.';
                } else {
                    $pdo->prepare('UPDATE avm_tenants SET name = ?, domains = ?, estado = ?, plan = ? WHERE id = ?')->execute([
                        trim((string) ($_POST['name'] ?? '')) ?: $t['name'], $doms, ($_POST['estado'] ?? '') === 'suspendida' ? 'suspendida' : 'activa',
                        ($_POST['plan'] ?? '') === 'cliente' ? 'cliente' : 'demo', $t['id']]);
                    avm_use_tenant($t);
                    $vals = ['liteapi_demo' => !empty($_POST['liteapi_demo']) ? true : null, 'mail_from' => trim((string) ($_POST['mail_from'] ?? '')), 'mail_reply_to' => trim((string) ($_POST['mail_reply_to'] ?? ''))];
                    foreach (['liteapi_key', 'resend_key'] as $k) {
                        if (!empty($_POST['borrar_' . $k])) $vals[$k] = '';
                        elseif (trim((string) ($_POST[$k] ?? '')) !== '') $vals[$k] = trim((string) $_POST[$k]);
                    }
                    avm_cfg_guardar($vals);
                    avm_use_tenant(null);
                    $aviso = 'Cambios guardados.';
                }
                $_GET['t'] = $t['id']; $vista = 'agencia';
            }
        } catch (PDOException $ex) {
            $error = str_contains($ex->getMessage(), 'Duplicate') ? 'Ese subdominio o correo ya existe.' : 'Error de base de datos: ' . $ex->getMessage();
        } catch (RuntimeException $ex) { $error = $ex->getMessage(); }
    }
}
$csrf = $e($_SESSION['csrf']);
$base = trim((string) (avm_config()['base_domain'] ?? ''), '.');
?><!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex"><title>Panel · <?= $e($nombreP) ?></title>
<style>
:root{--ink:#14181d;--muted:#5d6670;--line:#dde1e5;--paper:#f5f6f4;--card:#fff;--soft:#eef0f2;--signal:#BBCF26;--danger:#B4372B;--ok:#1F8A55}
@media (prefers-color-scheme:dark){:root{--ink:#eef1ee;--muted:#a3abb3;--line:#2c333a;--paper:#101418;--card:#171c21;--soft:#1d2228;--danger:#E06A5C;--ok:#4CC08A}}
*{box-sizing:border-box}body{margin:0;background:var(--paper);color:var(--ink);font:15px/1.5 system-ui,-apple-system,"Segoe UI",sans-serif}
.wrap{max-width:1080px;margin:0 auto;padding:24px 16px 60px;display:grid;gap:18px}
header{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}h1{font-size:24px;margin:0}h2{font-size:18px;margin:0 0 12px}
.card{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:20px}
.login{max-width:380px;margin:12vh auto;display:grid;gap:14px}
label{display:grid;gap:5px;font-size:13px;font-weight:600}input,select{font:inherit;padding:10px 12px;border:1px solid var(--line);border-radius:10px;background:var(--card);color:var(--ink);width:100%}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px}
button,.btn{font:600 14px/1 inherit;padding:12px 16px;border:0;border-radius:10px;background:var(--ink);color:var(--paper);cursor:pointer;text-decoration:none;display:inline-block}
.btn.ghost{background:transparent;color:var(--ink);border:1px solid var(--line)}
table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:10px 8px;border-top:1px solid var(--line);vertical-align:top}th{font-size:12px;color:var(--muted);border-top:0}
.muted{color:var(--muted);font-size:13px}.err{color:var(--danger)}.ok{color:var(--ok);font-weight:600}
.tag{font-size:12px;padding:3px 8px;border-radius:999px;background:var(--soft)}.tag.on{background:color-mix(in srgb,var(--ok) 18%,transparent);color:var(--ok)}.tag.off{background:color-mix(in srgb,var(--danger) 15%,transparent);color:var(--danger)}
.clave{background:var(--soft);border-radius:12px;padding:14px;font:600 15px/1.5 ui-monospace,Consolas,monospace;word-break:break-all}
.chk{display:flex;gap:8px;align-items:center;font-weight:500}.chk input{width:auto}
.row{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
</style></head><body>
<?php if ($vista === 'ingreso'): ?>
<form class="card login" method="post">
  <h1><?= $e($nombreP) ?></h1><p class="muted">Panel de la plataforma</p>
  <?php if ($error): ?><p class="err" role="alert"><?= $e($error) ?></p><?php endif; ?>
  <label>Correo<input type="email" name="correo" autocomplete="username" required></label>
  <label>Contraseña<input type="password" name="clave" autocomplete="current-password" required></label>
  <input type="hidden" name="csrf" value="<?= $csrf ?>"><button type="submit">Ingresar</button>
</form>
<?php else: ?>
<div class="wrap">
  <header><h1><a href="plataforma.php" style="color:inherit;text-decoration:none"><?= $e($nombreP) ?></a> <span class="muted">· Agencias</span></h1><a class="btn ghost" href="?salir=1">Cerrar sesión</a></header>
  <?php if ($aviso): ?><p class="ok" role="status"><?= $e($aviso) ?></p><?php endif; ?>
  <?php if ($error): ?><p class="err" role="alert"><?= $e($error) ?></p><?php endif; ?>
  <?php if ($claveNueva): ?><div class="card"><h2>Acceso del administrador</h2><p class="muted">Cópialo ahora: la contraseña no se vuelve a mostrar.</p>
    <div class="clave">Correo: <?= $e($claveNueva[0]) ?><br>Contraseña: <?= $e($claveNueva[1]) ?></div></div><?php endif; ?>

  <?php if ($vista === 'lista'): $lista = avm_tenants(false); ?>
  <div class="card"><h2>Agencias (<?= count($lista) ?>)</h2>
    <?php if (!$lista): ?><p class="muted">Aún no hay agencias. Crea la primera abajo.</p><?php else: ?>
    <div style="overflow-x:auto"><table><thead><tr><th>Agencia</th><th>Dirección</th><th>Plan</th><th>Estado</th><th></th></tr></thead><tbody>
    <?php foreach ($lista as $t): $url = avm_tenant_url($t); ?>
      <tr><td><b><?= $e($t['name']) ?></b><br><span class="muted"><?= $e($t['slug']) ?></span></td>
        <td><a href="<?= $e($url) ?>" target="_blank" rel="noopener"><?= $e(preg_replace('#^https?://#', '', $url)) ?></a></td>
        <td><?= $e($t['plan']) ?></td><td><span class="tag <?= $t['estado'] === 'activa' ? 'on' : 'off' ?>"><?= $e($t['estado']) ?></span></td>
        <td><a class="btn ghost" href="?t=<?= (int) $t['id'] ?>">Editar</a></td></tr>
    <?php endforeach; ?></tbody></table></div><?php endif; ?>
  </div>
  <form class="card" method="post"><h2>Nueva agencia</h2>
    <div class="grid">
      <label>Nombre comercial<input name="name" required placeholder="Viajes del Sol"></label>
      <label>Subdominio<input name="slug" required pattern="[a-z0-9][a-z0-9-]{1,58}[a-z0-9]" placeholder="viajesdelsol"><span class="muted"><?= $base ? 'Quedará en viajesdelsol.' . $e($base) : 'Falta base_domain en avm-config.php; en local entra con ?agencia=viajesdelsol' ?></span></label>
      <label>Dominio propio (opcional)<input name="domains" placeholder="viajesdelsol.com"></label>
      <label>Correo del administrador<input type="email" name="admin" required></label>
      <label>Plan<select name="plan"><option value="demo">Demo</option><option value="cliente">Cliente</option></select></label>
    </div>
    <p style="margin:12px 0"><label class="chk"><input type="checkbox" name="liteapi_demo" value="1"> Usar la llave de LiteAPI de la plataforma (solo para demos)</label></p>
    <input type="hidden" name="op" value="crear"><input type="hidden" name="csrf" value="<?= $csrf ?>"><button type="submit">Crear agencia</button>
    <p class="muted" style="margin-top:10px">Se crea con contenido de ejemplo, su marca con este nombre y un administrador con contraseña temporal.</p>
  </form>

  <?php else: $t = avm_tenant_fila('id', (string) (int) $_GET['t']); if (!$t): ?><p class="err">La agencia no existe.</p><?php else:
    avm_use_tenant($t); $tieneLite = avm_open((string) ($t['config']['liteapi_key'] ?? '')) !== ''; $tieneResend = avm_open((string) ($t['config']['resend_key'] ?? '')) !== '';
    $admins = $pdo->prepare("SELECT email, last_login FROM avm_users WHERE tenant = ? ORDER BY email"); $admins->execute([$t['id']]); $admins = $admins->fetchAll(); avm_use_tenant(null); ?>
  <form class="card" method="post"><div class="row" style="justify-content:space-between"><h2><?= $e($t['name']) ?></h2><a class="btn ghost" href="<?= $e(avm_tenant_url($t)) ?>" target="_blank" rel="noopener">Abrir sitio ↗</a></div>
    <div class="grid">
      <label>Nombre<input name="name" value="<?= $e($t['name']) ?>"></label>
      <label>Subdominio<input value="<?= $e($t['slug']) ?>" disabled></label>
      <label>Dominios propios (separados por coma)<input name="domains" value="<?= $e($t['domains']) ?>"></label>
      <label>Plan<select name="plan"><option value="demo"<?= $t['plan'] === 'demo' ? ' selected' : '' ?>>Demo</option><option value="cliente"<?= $t['plan'] === 'cliente' ? ' selected' : '' ?>>Cliente</option></select></label>
      <label>Estado<select name="estado"><option value="activa"<?= $t['estado'] === 'activa' ? ' selected' : '' ?>>Activa</option><option value="suspendida"<?= $t['estado'] !== 'activa' ? ' selected' : '' ?>>Suspendida</option></select></label>
    </div>
    <h2 style="margin-top:20px">Proveedores y correo</h2>
    <div class="grid">
      <label>Llave de LiteAPI <?= $tieneLite ? '<span class="tag on">configurada</span>' : '<span class="tag">sin llave</span>' ?><input name="liteapi_key" type="password" autocomplete="off" placeholder="<?= $tieneLite ? 'Déjalo vacío para no cambiarla' : 'Pega la llave' ?>">
        <?php if ($tieneLite): ?><span class="chk"><input type="checkbox" name="borrar_liteapi_key" value="1"> Borrar la llave</span><?php endif; ?></label>
      <label>Llave de Resend (opcional) <?= $tieneResend ? '<span class="tag on">configurada</span>' : '<span class="tag">usa la de la plataforma</span>' ?><input name="resend_key" type="password" autocomplete="off" placeholder="Solo si la agencia envía desde su dominio">
        <?php if ($tieneResend): ?><span class="chk"><input type="checkbox" name="borrar_resend_key" value="1"> Borrar la llave</span><?php endif; ?></label>
      <label>Remitente propio (con su Resend)<input name="mail_from" value="<?= $e($t['config']['mail_from'] ?? '') ?>" placeholder="Viajes del Sol <reservas@viajesdelsol.com>"></label>
      <label>Responder a<input name="mail_reply_to" value="<?= $e($t['config']['mail_reply_to'] ?? '') ?>" placeholder="ventas@viajesdelsol.com"></label>
    </div>
    <p style="margin:12px 0"><label class="chk"><input type="checkbox" name="liteapi_demo" value="1"<?= !empty($t['config']['liteapi_demo']) ? ' checked' : '' ?>> Usar la llave de LiteAPI de la plataforma (solo para demos)</label></p>
    <input type="hidden" name="op" value="guardar"><input type="hidden" name="id" value="<?= (int) $t['id'] ?>"><input type="hidden" name="csrf" value="<?= $csrf ?>">
    <div class="row"><button type="submit">Guardar cambios</button><a class="btn ghost" href="plataforma.php">Volver</a></div>
  </form>
  <form class="card" method="post"><h2>Administradores</h2>
    <?php if ($admins): ?><table><thead><tr><th>Correo</th><th>Último ingreso</th></tr></thead><tbody><?php foreach ($admins as $a): ?><tr><td><?= $e($a['email']) ?></td><td class="muted"><?= $e($a['last_login'] ?: 'Nunca') ?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?>
    <div class="row" style="margin-top:12px"><input type="email" name="admin" required placeholder="correo@agencia.com" style="max-width:320px">
      <input type="hidden" name="op" value="clave"><input type="hidden" name="id" value="<?= (int) $t['id'] ?>"><input type="hidden" name="csrf" value="<?= $csrf ?>">
      <button type="submit">Crear acceso o nueva contraseña</button></div>
    <p class="muted" style="margin-top:8px">Si el correo ya existe, se le genera una contraseña nueva.</p>
  </form>
  <?php endif; endif; ?>
</div>
<?php endif; ?>
</body></html>
