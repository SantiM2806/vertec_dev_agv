# Desplegar la plataforma en Google Cloud (máquina e2-micro gratuita)

Google Cloud tiene una máquina **e2-micro** dentro de su plan gratuito permanente (*Free Tier*), solo en tres regiones de Estados Unidos. Tiene 1 GB de memoria: alcanza para pruebas con pocas agencias (el script agrega 2 GB de memoria de intercambio).

Se usa el mismo script de instalación que en Oracle. La plataforma queda en `https://<ip-con-guiones>.sslip.io` y cada agencia en `https://<subdominio>.<ip-con-guiones>.sslip.io`, con HTTPS automático.

Tiempo aproximado: 30 minutos.

## Costos: léelo antes de empezar

- La máquina e2-micro y 30 GB de disco *Standard* son gratis todos los meses, **solo** en `us-west1`, `us-central1` o `us-east1`.
- Google cobra aparte la **IP pública** de las máquinas (del orden de 3 a 4 USD al mes). Las cuentas nuevas reciben **300 USD de crédito por 90 días**, que lo cubren. Después del crédito, verifica en **Facturación → Informes** si te están cobrando la IP.
- La tarjeta es solo para verificar: mientras no actives la cuenta de pago completa, Google no cobra; si el crédito se acaba, los recursos se detienen.
- Crea una alerta de presupuesto (paso 2) para enterarte de cualquier cobro.

## 1. Crear la cuenta

1. Entra a https://console.cloud.google.com con tu cuenta de Google y acepta la prueba gratuita (*Free trial*).
2. Pide tarjeta para verificar la identidad. No cobra durante la prueba.

## 2. Alerta de presupuesto

1. Menú ☰ → **Facturación → Presupuestos y alertas → Crear presupuesto**.
2. Nombre `alerta-pruebas`, monto **1 USD**, alertas al 50 % y 100 % del gasto real. Te llegan por correo.

## 3. Crear la máquina

1. Menú ☰ → **Compute Engine → Instancias de VM**. La primera vez pide **Habilitar** la API: acéptalo y espera un minuto.
2. **Crear instancia**:
   - **Nombre:** `plataforma`
   - **Región:** `us-central1` (Iowa) u otra de las tres gratuitas. Zona: cualquiera.
   - **Configuración de la máquina:** serie **E2** → tipo **e2-micro**.
   - **Disco de arranque → Cambiar:** sistema **Ubuntu**, versión **Ubuntu 24.04 LTS** (x86/64), tipo de disco **Disco persistente estándar** (no "equilibrado"), tamaño **30 GB**.
   - **Firewall:** marca **Permitir tráfico HTTP** y **Permitir tráfico HTTPS**.
3. **Crear**. Cuando aparezca en verde, copia la **IP externa**.

## 4. Entrar a la máquina

En la lista de instancias, botón **SSH** de la máquina: abre una terminal en el navegador. No necesitas llaves ni PowerShell.

## 5. Dar acceso de lectura al repositorio (privado)

**Desde aquí todo se hace en la terminal SSH de la máquina** (la ventana del botón SSH), no en PowerShell ni en tu computador. Pega cada comando con Ctrl+V y pulsa Enter.

En esa terminal:

```bash
ssh-keygen -t ed25519 -C "servidor-google" -f ~/.ssh/id_ed25519 -N ""
```

```bash
cat ~/.ssh/id_ed25519.pub
```

Copia la línea que aparece. En GitHub: repositorio `vertec_dev_agv` → **Settings → Deploy keys → Add deploy key**. Pégala, título *Servidor Google*, **sin marcar** *Allow write access*.

## 6. Descargar el código e instalar

En la misma terminal SSH de la máquina:

Primero instala Git (algunas imágenes de Ubuntu no lo traen):

```bash
sudo apt-get update && sudo apt-get install -y git
```

```bash
sudo mkdir -p /var/www && sudo chown "$USER":"$USER" /var/www
```

```bash
git clone -b marca-blanca git@github.com:SantiM2806/vertec_dev_agv.git /var/www/plataforma
```

(Si pregunta por github.com, escribe `yes`.)

Cambia `tu@correo.com` por tu correo y ejecuta:

```bash
cd /var/www/plataforma && sudo PLATAFORMA="Vertec Travel" ACME_EMAIL=tu@correo.com bash despliegue/oracle/instalar-servidor.sh
```

Tarda unos 5 a 10 minutos en la e2-micro. Al final muestra la dirección de la plataforma.

## 7. En el navegador

1. `https://<ip-con-guiones>.sslip.io/instalar.php`: crea tu usuario de la plataforma.
2. `https://<ip-con-guiones>.sslip.io/plataforma.php`: crea una agencia de prueba y copia la contraseña temporal del administrador.
3. Pon tu llave de LiteAPI: `sudo nano /var/www/plataforma/produccion/avm-config.php` (campo `liteapi_key`), y en la agencia marca *Usar la llave de LiteAPI de la plataforma*.

## Actualizar y revisar

Igual que en Oracle (ver `despliegue/oracle/README.md`):

```bash
cd /var/www/plataforma && bash despliegue/oracle/actualizar.sh
```

- Tareas programadas: `tail -f /var/log/plataforma-tareas.log`
- Errores de PHP: `sudo tail -f /var/log/apache2/plataforma-error.log`
- HTTPS: `sudo journalctl -u caddy -f`

Importante: si detienes y vuelves a encender la máquina, Google puede cambiarle la IP externa. Si pasa, cambia `base_domain` y `site_url` en `avm-config.php` a la IP nueva.
