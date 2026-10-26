# Plataforma de viajes en marca blanca

Una sola instalación para muchas agencias de viajes. Cada agencia tiene su sitio con su marca (logo, colores, tipografía), su dominio o subdominio, su backoffice, sus usuarios y sus llaves de proveedores:

- buscador con precios en línea (LiteAPI: hoteles; vuelos y actividades listos para activar);
- cotización con adicionales (actividades, traslados y asistencia médica);
- captura de solicitudes, CRM de prospectos, correos automáticos y campañas con Resend;
- tarifas con markup y fees, y desglose interno del neto y la ganancia.

El dueño de la plataforma administra las agencias en `plataforma.php`.

## Estructura

| Carpeta / archivo | Qué es |
|---|---|
| `avm-terminal.html` | **Fuente** de la página (HTML, CSS y JS en un solo archivo). Se edita aquí. |
| `img/` | Imágenes de ejemplo del sitio (se copian al construir). |
| `datos/` | Contenido inicial de cada agencia nueva. Se convierte en `datos-iniciales.json`. |
| `produccion/public_html/` | El sitio. `index.html` y `datos-iniciales.json` se **generan**; el resto se edita directo. |
| `produccion/public_html/api/_tenant.php` | Multi-agencia: reconoce la agencia por la dirección, su configuración y su marca. |
| `produccion/public_html/api/_nucleo.php` | Base de datos, sesiones, automatizaciones y correos. |
| `produccion/public_html/plataforma.php` | Panel del dueño de la plataforma (crear agencias, dominios, llaves). |
| `produccion/public_html/api/tareas.php` | Tareas programadas (cada 5 minutos) para todas las agencias. |
| `produccion/avm-config.example.php` | Plantilla de la configuración de la plataforma. |
| `despliegue/oracle/` | Instalación en un servidor Oracle Cloud gratuito, con HTTPS automático. |
| `herramientas/` | Construir, empaquetar, servidor local y simulador de LiteAPI. |
| `docs/liteapi/` | Referencia de los endpoints de LiteAPI. |
| `propuesta/` | Propuesta comercial original (HTML y PDF). |

## Cómo se reconoce cada agencia

- `https://<subdominio>.<base_domain>` (por ejemplo `viajesdelsol.tuplataforma.com`), o
- su dominio propio, escrito en *Dominios propios* en `plataforma.php`.
- La dirección base sin subdominio muestra la portada de la plataforma.

En local: `http://<subdominio>.localhost:<puerto>` (los navegadores lo envían a este mismo equipo), o `http://localhost:<puerto>/?agencia=<subdominio>`.

## Requisitos para trabajar en otro computador

- Git y Python 3.10 o superior
- PHP 8.1 o superior con `pdo_mysql`, `curl`, `mbstring` y `openssl`, y MySQL o MariaDB (XAMPP en Windows trae todo)
- Node.js, solo para el simulador de LiteAPI

## Puesta en marcha en local

```bash
git clone https://github.com/SantiM2806/vertec_dev_agv.git plataforma
cd plataforma
git checkout marca-blanca
```

1. Crea una base de datos vacía, por ejemplo `plataforma`.
2. Copia `produccion/avm-config.example.php` como `produccion/avm-config.php` y llena la base de datos y `app_secret` (una frase larga y aleatoria). Git ignora este archivo.
   Para probar sin gastar LiteAPI usa el simulador, con `liteapi_base` `http://127.0.0.1:8771/v3.0` y `liteapi_key` `clave-de-prueba-local`, y crea agencias marcadas como demo.
3. Simulador de LiteAPI (opcional), en otra terminal:
   ```bash
   node herramientas/mock-liteapi.js
   ```
4. Servidor:
   ```bash
   python herramientas/servidor-local.py
   ```
   El servidor de PHP atiende una petición a la vez; si la página carga a medias, usa Apache de XAMPP con la carpeta `produccion/public_html`.
5. Abre `http://localhost:8770/instalar.php` y crea el usuario de la plataforma. Luego `http://localhost:8770/plataforma.php` para crear agencias, y entra a cada una en `http://<subdominio>.localhost:8770`.

Si la base ya tenía el sitio de una sola agencia, el instalador la convierte en la agencia 1 sin perder datos.

## Hacer cambios

1. Edita `avm-terminal.html` (la página) o `produccion/public_html/` (el servidor).
2. Construye la página:
   ```bash
   python herramientas/build.py
   ```
3. Prueba en local y guarda en Git:
   ```bash
   git add -A
   git commit -m "Describe el cambio"
   git push
   ```

## Desplegar

- **Servidor gratuito (Oracle Cloud)**: sigue `despliegue/oracle/README.md`. Para actualizar después: `bash despliegue/oracle/actualizar.sh` en el servidor.
- **Hosting con cPanel**: sube `produccion/public_html` y sigue `produccion/INSTRUCCIONES.txt`. Para varias agencias en subdominios necesitas un subdominio comodín (`*`) apuntando a la misma carpeta y su certificado HTTPS.

## Seguridad

- La contraseña de la base y `app_secret` viven solo en `avm-config.php`, fuera de `public_html`. Nunca lo subas al repositorio.
- Las llaves de cada agencia se guardan cifradas con `app_secret` en la base de datos.
- Cada consulta a la base filtra por agencia; una sesión solo vale para la agencia en la que se inició.
- El neto del proveedor y las comisiones solo se envían a administradores con sesión iniciada.
- Las fotos de `img/` son de ejemplo: cada agencia debe usar fotos con licencia propia.
- Mantén este repositorio **privado**.
