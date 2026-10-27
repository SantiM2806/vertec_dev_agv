# Desplegar la plataforma en Oracle Cloud (gratis)

Servidor **Always Free** de Oracle: una máquina virtual con Ubuntu que no se vence. El script instala Apache, PHP, MariaDB y Caddy (HTTPS automático), y deja cada agencia en su propio subdominio:

- Plataforma: `https://<ip-con-guiones>.sslip.io`
- Agencias: `https://<subdominio>.<ip-con-guiones>.sslip.io`

`sslip.io` es un servicio gratuito que convierte la IP en dirección web: no hace falta comprar un dominio para las pruebas. Más adelante puedes usar el tuyo (ver el final).

Tiempo aproximado: 30 a 45 minutos la primera vez.

## 1. Crear la cuenta

1. Entra a https://signup.cloud.oracle.com y crea la cuenta.
2. Elige la **región de inicio** con cuidado: no se puede cambiar. Para Colombia sirve *Bogotá* o *São Paulo*.
3. Oracle pide una tarjeta solo para verificar la identidad. Los recursos *Always Free* no se cobran.

## 2. Crear la máquina virtual

1. Menú ☰ → **Compute → Instances → Create instance**.
2. Nombre: `plataforma`.
3. **Image**: *Canonical Ubuntu 24.04* (o 22.04).
4. **Shape**: *Change shape → Ampere → VM.Standard.A1.Flex*, con **2 OCPU y 12 GB** de memoria. Es gratis hasta 4 OCPU y 24 GB.
   Si sale "Out of capacity", intenta más tarde u otro *Availability domain*. También sirve *VM.Standard.E2.1.Micro* (gratis, pero más pequeña).
5. **Networking**: deja que cree la red (VCN) y marca **Assign a public IPv4 address**.
6. **SSH keys**: *Generate a key pair for me* y descarga **las dos llaves**. Guárdalas: sin ellas no puedes entrar.
7. **Create**. Cuando esté en verde (*Running*), copia la **Public IP address**.

### Si sale "Out of capacity" (sin capacidad)

Es común: las máquinas A1 gratuitas se agotan por región. En vez de reintentar a mano, deja que Cloud Shell lo haga cada minuto:

1. Crea la red primero: Menú ☰ → **Networking → Virtual cloud networks → Start VCN Wizard → Create VCN with Internet Connectivity**, nombre `red-plataforma`, **Create**.
2. Abre **Cloud Shell** (ícono `>_` arriba a la derecha de la consola).
3. En el menú de Cloud Shell (engranaje o ☰) → **Upload** → sube `despliegue/oracle/reintentar-instancia.sh` desde tu computador.
4. Ejecútalo en segundo plano, para que siga aunque cierres el navegador:
   ```bash
   nohup bash reintentar-instancia.sh > reintentos.log 2>&1 &
   ```
5. Revisa cuando quieras:
   ```bash
   tail -f reintentos.log
   ```
   Cuando lo logre, muestra la IP pública y el comando para entrar.

La llave SSH queda en Cloud Shell (`~/.ssh/plataforma`): entra a la máquina desde ahí mismo con `ssh -i ~/.ssh/plataforma ubuntu@TU_IP` y sigue desde el paso 5 de esta guía. Cloud Shell cierra las sesiones inactivas tras un rato; si el log deja de avanzar, vuelve a ejecutar el paso 4.

## 3. Abrir los puertos 80 y 443

1. En la instancia: **Primary VNIC → Subnet** → la *Security List* por defecto → **Add Ingress Rules**.
2. Agrega dos reglas:
   - Source CIDR `0.0.0.0/0`, IP Protocol TCP, Destination port `80`
   - Source CIDR `0.0.0.0/0`, IP Protocol TCP, Destination port `443`

(El firewall interno de Ubuntu lo abre el script.)

## 4. Entrar al servidor

Desde la terminal de Windows (PowerShell), en la carpeta donde guardaste la llave privada:

```bash
ssh -i ssh-key-plataforma.key ubuntu@TU_IP
```

Si Windows reclama por los permisos de la llave, haz clic derecho en el archivo → Propiedades → Seguridad → deja solo a tu usuario con acceso.

## 5. Dar acceso de lectura al repositorio (privado)

En el servidor, crea una llave solo para este repositorio:

```bash
ssh-keygen -t ed25519 -C "servidor-oracle" -f ~/.ssh/id_ed25519 -N ""
```

```bash
cat ~/.ssh/id_ed25519.pub
```

Copia la línea que aparece. En GitHub: repositorio `vertec_dev_agv` → **Settings → Deploy keys → Add deploy key**. Pégala, ponle de título *Servidor Oracle* y **deja sin marcar** *Allow write access*.

## 6. Descargar el código e instalar

```bash
sudo mkdir -p /var/www && sudo chown ubuntu:ubuntu /var/www
```

```bash
git clone -b marca-blanca git@github.com:SantiM2806/vertec_dev_agv.git /var/www/plataforma
```

(La primera vez pregunta si confías en github.com: escribe `yes`.)

```bash
cd /var/www/plataforma && sudo PLATAFORMA="Vertec Travel" ACME_EMAIL=tu@correo.com bash despliegue/oracle/instalar-servidor.sh
```

Al terminar muestra las direcciones. Luego, en el navegador:

1. `https://<ip-con-guiones>.sslip.io/instalar.php`: crea tu usuario de la plataforma (contraseña de 12 caracteres o más). El instalador queda bloqueado solo.
2. `https://<ip-con-guiones>.sslip.io/plataforma.php`: crea tu agencia de demostración. Copia la contraseña temporal del administrador.
3. Si es la única agencia, la dirección principal `https://<ip-con-guiones>.sslip.io` ya muestra su sitio, y su backoffice queda en `/ingresar.php`. Si más adelante creas otras, cada una queda en su subdominio.

El primer ingreso a cada dirección tarda unos segundos mientras Caddy pide su certificado HTTPS.

## 7. Llaves de proveedores y correo

- **LiteAPI de cada agencia**: en `plataforma.php` → la agencia → *Llave de LiteAPI*. Para demos, marca *Usar la llave de LiteAPI de la plataforma* y pon tu llave sandbox en `avm-config.php` (`liteapi_key`).
- **Correos**: pon tu llave de Resend y el remitente (`resend_key`, `mail_from`) en `avm-config.php`. Las agencias sin Resend propio envían con ese correo y su nombre como remitente.

Para editar la configuración:

```bash
sudo nano /var/www/plataforma/produccion/avm-config.php
```

## 8. Actualizar cuando haya cambios en GitHub

```bash
cd /var/www/plataforma && bash despliegue/oracle/actualizar.sh
```

## Revisar que todo funcione

- Tareas programadas (correos, TRM, copias): `tail -f /var/log/plataforma-tareas.log`
- Errores de PHP: `sudo tail -f /var/log/apache2/plataforma-error.log`
- HTTPS: `sudo journalctl -u caddy -f`

## Usar un dominio propio

1. Compra el dominio (por ejemplo `tuplataforma.com`) y en su DNS crea dos registros **A** hacia la IP del servidor: `@` y `*`.
2. Cambia `base_domain` y `site_url` en `avm-config.php` a `tuplataforma.com` y `https://tuplataforma.com`.
3. Cada agencia queda en `https://<subdominio>.tuplataforma.com`.
4. Si una agencia quiere su propio dominio (por ejemplo `viajesdelsol.com`), crea en el DNS de ese dominio un registro **A** hacia la IP del servidor y escríbelo en *Dominios propios* de la agencia en `plataforma.php`. El HTTPS se activa solo.
