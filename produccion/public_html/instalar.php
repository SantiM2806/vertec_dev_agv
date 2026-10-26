<?php
/**
 * Instalador de la plataforma (una sola vez): crea o actualiza las tablas y el usuario dueño de la plataforma.
 * Si encuentra una instalación de una sola agencia, la convierte en la agencia 1 sin perder datos.
 * Cuando termina se bloquea solo. Bórralo después de usarlo.
 */
require __DIR__ . '/api/_nucleo.php';
avm_session();
header('X-Frame-Options: DENY');
header('Cache-Control: no-store');
$e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$lock = dirname(__DIR__) . '/avm-instalado.lock';
$msg = ''; $ok = false; $fatal = ''; $log = [];
$plataforma = '';

if (!is_file(dirname(__DIR__) . '/avm-config.php')) {
    $fatal = 'Falta el archivo avm-config.php en la carpeta que está por encima de public_html. Súbelo y recarga esta página.';
} else try {
    $pdo = avm_db();
    $plataforma = avm_plataforma_nombre();
    if (empty(avm_config()['app_secret'])) $fatal = 'Falta app_secret en avm-config.php: escribe una frase larga y aleatoria (se usa para cifrar las llaves de cada agencia).';
} catch (Throwable $ex) {
    $fatal = 'No pude conectar con la base de datos. Revisa db_host, db_name, db_user y db_pass en avm-config.php. Detalle: ' . $ex->getMessage();
}
if (!$fatal && is_file($lock)) $fatal = 'La plataforma ya está instalada. Borra este archivo (instalar.php) del servidor.';
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));

if (!$fatal && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim((string) ($_POST['correo'] ?? '')));
    $pass = (string) ($_POST['clave'] ?? '');
    if (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) $msg = 'La página expiró. Intenta de nuevo.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $msg = 'Escribe un correo válido.';
    elseif (strlen($pass) < 12) $msg = 'La contraseña debe tener al menos 12 caracteres.';
    elseif ($pass !== (string) ($_POST['clave2'] ?? '')) $msg = 'Las contraseñas no coinciden.';
    else {
        $log = avm_schema($pdo, ['slug' => preg_replace('/[^a-z0-9-]/', '', strtolower((string) ($_POST['slug1'] ?? ''))) ?: 'agencia1', 'name' => trim((string) ($_POST['nombre1'] ?? '')) ?: 'Agencia 1']);
        // Dueño de la plataforma: agencia 0, rol super
        $pdo->prepare("INSERT INTO avm_users (tenant, email, pass, role) VALUES (0, ?, ?, 'super') ON DUPLICATE KEY UPDATE pass = VALUES(pass), role = 'super'")
            ->execute([$email, password_hash($pass, PASSWORD_DEFAULT)]);
        @file_put_contents($lock, date('c'));
        $ok = true;
        $msg = 'Listo. La base de datos está preparada y tu usuario de la plataforma quedó creado.';
    }
}
$migra = !$fatal && in_array('path', avm_columnas($pdo, 'avm_docs'), true) && !in_array('tenant', avm_columnas($pdo, 'avm_docs'), true);
?><!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex"><title>Instalar · <?= $e($plataforma ?: 'Plataforma') ?></title>
<style>
:root{--ink:#14181d;--muted:#5d6670;--line:#dde1e5;--paper:#f5f6f4;--card:#fff;--signal:#BBCF26;--danger:#B4372B;--ok:#1F8A55}
@media (prefers-color-scheme:dark){:root{--ink:#eef1ee;--muted:#a3abb3;--line:#2c333a;--paper:#101418;--card:#171c21;--danger:#E06A5C;--ok:#4CC08A}}
*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;background:var(--paper);color:var(--ink);font:16px/1.5 system-ui,-apple-system,"Segoe UI",sans-serif;padding:16px}
.card{width:100%;max-width:460px;background:var(--card);border:1px solid var(--line);border-radius:20px;padding:28px;display:grid;gap:14px}
h1{font-size:22px;margin:0}p{margin:0;color:var(--muted);font-size:14px}
label{display:grid;gap:6px;font-size:13px;font-weight:600}input{font:inherit;padding:11px 12px;border:1px solid var(--line);border-radius:10px;background:var(--card);color:var(--ink)}
button,.btn{font:600 15px/1 inherit;padding:14px;border:0;border-radius:12px;background:var(--ink);color:var(--paper);cursor:pointer;text-align:center;text-decoration:none}
.err{color:var(--danger)}.ok{color:var(--ok);font-weight:600}fieldset{border:1px solid var(--line);border-radius:12px;padding:12px;display:grid;gap:10px}legend{font-size:13px;font-weight:700;padding:0 6px}
ul{margin:0;padding-left:18px;color:var(--muted);font-size:14px}
</style></head><body><div class="card">
<h1>Instalar <?= $e($plataforma ?: 'la plataforma') ?></h1>
<?php if ($fatal): ?>
  <p class="err"><?= $e($fatal) ?></p>
<?php elseif ($ok): ?>
  <p class="ok"><?= $e($msg) ?></p>
  <?php if ($log): ?><ul><?php foreach ($log as $l): ?><li><?= $e($l) ?></li><?php endforeach; ?></ul><?php endif; ?>
  <p><b>Ahora borra el archivo instalar.php</b> del servidor.</p>
  <a class="btn" href="plataforma.php">Ir al panel de la plataforma</a>
<?php else: ?>
  <p>Crea el usuario dueño de la plataforma. Con él creas las agencias, sus dominios y sus administradores.</p>
  <?php if ($msg): ?><p class="err" role="alert"><?= $e($msg) ?></p><?php endif; ?>
  <form method="post" style="display:grid;gap:14px">
    <label>Tu correo<input type="email" name="correo" autocomplete="username" required></label>
    <label>Contraseña (mínimo 12 caracteres)<input type="password" name="clave" autocomplete="new-password" minlength="12" required></label>
    <label>Repite la contraseña<input type="password" name="clave2" autocomplete="new-password" minlength="12" required></label>
    <?php if ($migra): ?>
    <fieldset><legend>Datos existentes</legend>
      <p>Esta base ya tiene el contenido de un sitio. Se convertirá en la primera agencia de la plataforma, sin perder nada.</p>
      <label>Nombre de esa agencia<input name="nombre1" required placeholder="Agencia de viajes"></label>
      <label>Subdominio (solo minúsculas, números y guiones)<input name="slug1" required pattern="[a-z0-9-]{2,60}" placeholder="miagencia"></label>
    </fieldset>
    <?php endif; ?>
    <input type="hidden" name="csrf" value="<?= $e($_SESSION['csrf']) ?>">
    <button type="submit">Instalar</button>
  </form>
<?php endif; ?>
</div></body></html>
