<?php
/**
 * Instalador de una sola vez: crea las tablas, el primer administrador y carga el contenido
 * inicial (datos-iniciales.json). Cuando termina se bloquea solo. Bórralo después de usarlo.
 */
require __DIR__ . '/api/_lib.php';
avm_session();
header('X-Frame-Options: DENY');
header('Cache-Control: no-store');
$e = fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
$lock = dirname(__DIR__) . '/avm-instalado.lock';
$msg = ''; $ok = false; $fatal = '';

if (!is_file(dirname(__DIR__) . '/avm-config.php')) {
    $fatal = 'Falta el archivo avm-config.php en la carpeta que está por encima de public_html. Súbelo y recarga esta página.';
} else try {
    $pdo = avm_db();
} catch (Throwable $ex) {
    $fatal = 'No pude conectar con la base de datos. Revisa db_host, db_name, db_user y db_pass en avm-config.php. Detalle: ' . $ex->getMessage();
}
if (!$fatal && is_file($lock)) $fatal = 'El sitio ya está instalado. Borra este archivo (instalar.php) del servidor.';
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));

if (!$fatal && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim((string) ($_POST['correo'] ?? '')));
    $pass = (string) ($_POST['clave'] ?? '');
    if (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) $msg = 'La página expiró. Intenta de nuevo.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $msg = 'Escribe un correo válido.';
    elseif (strlen($pass) < 10) $msg = 'La contraseña debe tener al menos 10 caracteres.';
    elseif ($pass !== (string) ($_POST['clave2'] ?? '')) $msg = 'Las contraseñas no coinciden.';
    else {
        $pdo->exec("CREATE TABLE IF NOT EXISTS avm_docs (
            path VARCHAR(420) NOT NULL PRIMARY KEY, coll VARCHAR(210) NOT NULL, id VARCHAR(200) NOT NULL,
            data LONGTEXT NOT NULL, updated BIGINT NOT NULL, deleted TINYINT(1) NOT NULL DEFAULT 0,
            KEY coll (coll), KEY updated (updated)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $pdo->exec("CREATE TABLE IF NOT EXISTS avm_users (
            id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(190) NOT NULL UNIQUE, pass VARCHAR(255) NOT NULL,
            created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, last_login DATETIME NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $pdo->exec("CREATE TABLE IF NOT EXISTS avm_rate (k VARCHAR(80) NOT NULL, t INT NOT NULL, KEY kt (k, t)) ENGINE=InnoDB");
        $pdo->prepare('INSERT INTO avm_users (email, pass) VALUES (?, ?) ON DUPLICATE KEY UPDATE pass = VALUES(pass)')
            ->execute([$email, password_hash($pass, PASSWORD_DEFAULT)]);
        $n = 0;
        $seed = __DIR__ . '/datos-iniciales.json';
        if (is_file($seed)) {
            foreach (json_decode(file_get_contents($seed), true) ?: [] as $path => $doc) {
                if (avm_get($path) === null && is_array($doc)) { avm_put($path, $doc); $n++; }
            }
            @unlink($seed);
        }
        @file_put_contents($lock, date('c'));
        $ok = true;
        $msg = "Listo. Se crearon las tablas, tu usuario y $n documentos de contenido.";
    }
}
?><!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex"><title>Instalar · AVM</title>
<style>
:root{--ink:#14181d;--muted:#5d6670;--line:#dde1e5;--paper:#f5f6f4;--card:#fff;--signal:#BBCF26;--danger:#B4372B;--ok:#1F8A55}
@media (prefers-color-scheme:dark){:root{--ink:#eef1ee;--muted:#a3abb3;--line:#2c333a;--paper:#101418;--card:#171c21;--danger:#E06A5C;--ok:#4CC08A}}
*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;background:var(--paper);color:var(--ink);font:16px/1.5 system-ui,-apple-system,"Segoe UI",sans-serif;padding:16px}
.card{width:100%;max-width:440px;background:var(--card);border:1px solid var(--line);border-radius:20px;padding:28px;display:grid;gap:14px}
h1{font-size:22px;margin:0}p{margin:0;color:var(--muted);font-size:14px}
label{display:grid;gap:6px;font-size:13px;font-weight:600}input{font:inherit;padding:11px 12px;border:1px solid var(--line);border-radius:10px;background:var(--card);color:var(--ink)}
button,.btn{font:600 15px/1 inherit;padding:14px;border:0;border-radius:12px;background:var(--ink);color:var(--paper);cursor:pointer;text-align:center;text-decoration:none}
.err{color:var(--danger)}.ok{color:var(--ok);font-weight:600}
</style></head><body><div class="card">
<h1>Instalar el sitio de AVM</h1>
<?php if ($fatal): ?>
  <p class="err"><?= $e($fatal) ?></p>
<?php elseif ($ok): ?>
  <p class="ok"><?= $e($msg) ?></p>
  <p><b>Ahora borra el archivo instalar.php</b> desde el Administrador de archivos de Dongee.</p>
  <a class="btn" href="ingresar.php">Ir al backoffice</a>
<?php else: ?>
  <p>Crea el usuario administrador del backoffice. Usa un correo tuyo y una contraseña de al menos 10 caracteres.</p>
  <?php if ($msg): ?><p class="err" role="alert"><?= $e($msg) ?></p><?php endif; ?>
  <form method="post" style="display:grid;gap:14px">
    <label>Correo del administrador<input type="email" name="correo" autocomplete="username" required></label>
    <label>Contraseña<input type="password" name="clave" autocomplete="new-password" minlength="10" required></label>
    <label>Repite la contraseña<input type="password" name="clave2" autocomplete="new-password" minlength="10" required></label>
    <input type="hidden" name="csrf" value="<?= $e($_SESSION['csrf']) ?>">
    <button type="submit">Instalar</button>
  </form>
<?php endif; ?>
</div></body></html>
