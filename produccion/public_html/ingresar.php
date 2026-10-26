<?php
/** Ingreso al backoffice de la agencia. ?salir=1 cierra la sesión. */
require __DIR__ . '/api/_nucleo.php';
$t = avm_tenant_opt();
if (!$t) { header('Location: plataforma.php'); exit; }
avm_tenant();
avm_session();
$marca = avm_marca();
header('X-Frame-Options: DENY');
header('Cache-Control: no-store');

if (isset($_GET['salir'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: ./');
    exit;
}
if (avm_is_admin()) { header('Location: ./#admin'); exit; }

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = avm_db();
    $ip = substr(hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|login|' . $t['id']), 0, 40);
    $pdo->prepare('DELETE FROM avm_rate WHERE t < ?')->execute([time() - 900]);
    $n = $pdo->prepare('SELECT COUNT(*) FROM avm_rate WHERE k = ? AND t > ?');
    $n->execute([$ip, time() - 900]);
    if (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
        $error = 'La página expiró. Intenta de nuevo.';
    } elseif ((int) $n->fetchColumn() >= 5) {
        $error = 'Demasiados intentos. Espera 15 minutos.';
    } else {
        $email = strtolower(trim((string) ($_POST['correo'] ?? '')));
        $st = $pdo->prepare('SELECT id, pass FROM avm_users WHERE tenant = ? AND email = ?');
        $st->execute([$t['id'], $email]);
        $u = $st->fetch();
        if ($u && password_verify((string) ($_POST['clave'] ?? ''), $u['pass'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $u['id'];
            $_SESSION['admin_tenant'] = $t['id'];
            $pdo->prepare('UPDATE avm_users SET last_login = NOW() WHERE id = ?')->execute([$u['id']]);
            header('Location: ./#admin');
            exit;
        }
        $pdo->prepare('INSERT INTO avm_rate (k, t) VALUES (?, ?)')->execute([$ip, time()]);
        $error = 'Correo o contraseña incorrectos.';
    }
}
$e = fn($s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
?><!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex">
<title>Backoffice · <?= $e($marca['name']) ?></title>
<style>
:root{--ink:#14181d;--muted:#5d6670;--line:#dde1e5;--paper:#f5f6f4;--card:#fff;--signal:<?= $e($marca['accent']) ?>;--danger:#B4372B}
@media (prefers-color-scheme:dark){:root{--ink:#eef1ee;--muted:#a3abb3;--line:#2c333a;--paper:#101418;--card:#171c21;--danger:#E06A5C}}
*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;background:var(--paper);color:var(--ink);font:16px/1.5 system-ui,-apple-system,"Segoe UI",sans-serif;padding:16px}
form{width:100%;max-width:380px;background:var(--card);border:1px solid var(--line);border-radius:20px;padding:28px;display:grid;gap:14px}
img{height:40px;width:auto}h1{font-size:22px;margin:4px 0 0}p{margin:0;color:var(--muted);font-size:14px}
label{display:grid;gap:6px;font-size:13px;font-weight:600}input{font:inherit;padding:11px 12px;border:1px solid var(--line);border-radius:10px;background:var(--card);color:var(--ink)}
input:focus{outline:2px solid var(--signal);outline-offset:1px}
button{font:600 15px/1 inherit;padding:14px;border:0;border-radius:12px;background:var(--ink);color:var(--paper);cursor:pointer}
.err{color:var(--danger);font-size:14px}a{color:var(--muted);font-size:13px}
</style></head><body>
<form method="post" autocomplete="on">
  <?php if ($marca['logo']): ?><img src="api/marca.php?img=logo" alt="<?= $e($marca['name']) ?>"><?php else: ?><b style="font-size:20px"><?= $e($marca['name']) ?></b><?php endif; ?>
  <h1>Backoffice</h1><p>Ingresa con tu usuario de administrador.</p>
  <?php if ($error): ?><div class="err" role="alert"><?= $e($error) ?></div><?php endif; ?>
  <label>Correo<input type="email" name="correo" autocomplete="username" required value="<?= $e((string) ($_POST['correo'] ?? '')) ?>"></label>
  <label>Contraseña<input type="password" name="clave" autocomplete="current-password" required></label>
  <input type="hidden" name="csrf" value="<?= $e($_SESSION['csrf']) ?>">
  <button type="submit">Ingresar</button>
  <a href="./">← Volver al sitio</a>
</form>
</body></html>
