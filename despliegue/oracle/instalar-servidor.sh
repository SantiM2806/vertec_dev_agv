#!/usr/bin/env bash
# Instala la plataforma en un servidor Ubuntu (Oracle Cloud Always Free u otro).
# Se ejecuta UNA vez, dentro de la carpeta del repositorio ya clonado:
#   sudo bash despliegue/oracle/instalar-servidor.sh
# Opcional, antes del comando:
#   BASE_DOMAIN=tuplataforma.com   dominio propio (sus registros DNS * y @ deben apuntar a esta IP)
#   PLATAFORMA="Mi Plataforma"     nombre que se ve en el panel
#   ACME_EMAIL=tu@correo.com       correo para los avisos de los certificados HTTPS
# Sin BASE_DOMAIN usa <ip-con-guiones>.sslip.io: gratis, sin comprar dominio, con HTTPS.
set -euo pipefail

if [ "$(id -u)" -ne 0 ]; then echo "Ejecútalo con sudo."; exit 1; fi
APP_DIR="$(cd "$(dirname "$0")/../.." && pwd)"
PROD="$APP_DIR/produccion"
PUB="$PROD/public_html"
CONF="$PROD/avm-config.php"
[ -f "$PUB/index.php" ] || { echo "No encuentro $PUB/index.php. Ejecuta el script desde el repositorio clonado."; exit 1; }

echo "==> Paquetes (Apache, PHP, MariaDB, Caddy)"
export DEBIAN_FRONTEND=noninteractive
apt-get update -y
apt-get install -y apache2 libapache2-mod-php php-cli php-mysql php-curl php-mbstring php-intl php-xml \
  mariadb-server curl gnupg debian-keyring debian-archive-keyring apt-transport-https netfilter-persistent iptables-persistent openssl
if ! command -v caddy >/dev/null; then
  curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | gpg --dearmor --yes -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
  curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' > /etc/apt/sources.list.d/caddy-stable.list
  apt-get update -y && apt-get install -y caddy
fi

IP="$(curl -fsS https://api.ipify.org || curl -fsS https://ifconfig.me)"
BASE="${BASE_DOMAIN:-$(echo "$IP" | tr . -).sslip.io}"
PLATAFORMA="${PLATAFORMA:-Vertec Travel}"
echo "==> IP pública: $IP · dominio base: $BASE"

echo "==> Firewall del servidor (las imágenes de Oracle bloquean 80 y 443)"
for p in 80 443; do
  iptables -C INPUT -p tcp --dport "$p" -m state --state NEW -j ACCEPT 2>/dev/null || iptables -I INPUT 5 -p tcp --dport "$p" -m state --state NEW -j ACCEPT
done
netfilter-persistent save >/dev/null

echo "==> Apache solo en 127.0.0.1:8080 (Caddy atiende 80/443 y pone el HTTPS)"
echo "Listen 127.0.0.1:8080" > /etc/apache2/ports.conf
cat > /etc/apache2/sites-available/plataforma.conf <<EOF
<VirtualHost 127.0.0.1:8080>
  ServerName localhost
  DocumentRoot $PUB
  <Directory $PUB>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
  </Directory>
  # IP real del visitante (para los límites de intentos), detrás de Caddy
  RemoteIPHeader X-Forwarded-For
  RemoteIPInternalProxy 127.0.0.1
  ErrorLog \${APACHE_LOG_DIR}/plataforma-error.log
  CustomLog \${APACHE_LOG_DIR}/plataforma-access.log combined
</VirtualHost>
EOF
a2enmod rewrite headers remoteip >/dev/null
a2dissite 000-default >/dev/null 2>&1 || true
a2ensite plataforma >/dev/null
# Fotos y logos suben comprimidos; los tarifarios en PDF pueden pesar hasta 25 MB
PHPINI="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
sed -i 's/^post_max_size = .*/post_max_size = 26M/; s/^upload_max_filesize = .*/upload_max_filesize = 25M/' "/etc/php/$PHPINI/apache2/php.ini"
systemctl restart apache2

echo "==> Base de datos"
systemctl enable --now mariadb >/dev/null
if [ ! -f "$CONF" ]; then
  DB_PASS="$(openssl rand -hex 18)"
  mysql -e "CREATE DATABASE IF NOT EXISTS plataforma CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
            CREATE USER IF NOT EXISTS 'plataforma'@'localhost' IDENTIFIED BY '$DB_PASS';
            ALTER USER 'plataforma'@'localhost' IDENTIFIED BY '$DB_PASS';
            GRANT ALL PRIVILEGES ON plataforma.* TO 'plataforma'@'localhost'; FLUSH PRIVILEGES;"
  SECRET="$(openssl rand -hex 32)"
  cat > "$CONF" <<EOF
<?php
// Creado por instalar-servidor.sh. No lo subas al repositorio (Git ya lo ignora).
return [
    'db_host' => 'localhost',
    'db_name' => 'plataforma',
    'db_user' => 'plataforma',
    'db_pass' => '$DB_PASS',
    'app_secret' => '$SECRET',
    'platform_name' => '$PLATAFORMA',
    'base_domain' => '$BASE',
    'base_http' => 'https',
    'site_url' => 'https://$BASE',
    'resend_key' => '',
    'mail_from' => '',
    'mail_daily_limit' => 100,
    'liteapi_key' => '',
];
EOF
  echo "   avm-config.php creado (contraseña de la base y app_secret aleatorios)."
else
  echo "   avm-config.php ya existe: no lo toco."
fi
chown root:www-data "$CONF"; chmod 640 "$CONF"
# Carpetas que escribe el sitio: caché, copias diarias, tarifarios en PDF y el candado del instalador
mkdir -p "$PROD/avm-cache" "$PROD/avm-copias" "$PROD/avm-archivos"
chown www-data:www-data "$PROD/avm-cache" "$PROD/avm-copias" "$PROD/avm-archivos"
chgrp www-data "$PROD"; chmod 775 "$PROD"

echo "==> Caddy: HTTPS automático para la plataforma y cada agencia"
cat > /etc/caddy/Caddyfile <<EOF
{
	${ACME_EMAIL:+email $ACME_EMAIL}
	on_demand_tls {
		ask http://127.0.0.1:8080/api/dominio.php
	}
}
https:// {
	tls {
		on_demand
	}
	encode gzip
	reverse_proxy 127.0.0.1:8080
}
http:// {
	redir https://{host}{uri} permanent
}
EOF
systemctl enable caddy >/dev/null
systemctl restart caddy

echo "==> Tareas programadas cada 5 minutos"
touch /var/log/plataforma-tareas.log; chown www-data:www-data /var/log/plataforma-tareas.log
echo "*/5 * * * * www-data php $PUB/api/tareas.php >> /var/log/plataforma-tareas.log 2>&1" > /etc/cron.d/plataforma
chmod 644 /etc/cron.d/plataforma

echo
echo "Listo. Ahora, en el navegador:"
echo "  1. https://$BASE/instalar.php   → crea tu usuario de la plataforma"
echo "  2. El instalador queda bloqueado solo después de usarlo."
echo "  3. https://$BASE/plataforma.php → crea tu primera agencia"
echo "     Cada agencia queda en https://<subdominio>.$BASE"
echo "El primer ingreso a cada dirección tarda unos segundos mientras se emite su certificado HTTPS."
