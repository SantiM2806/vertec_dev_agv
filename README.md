# Sitio web AVM Operadores Mayoristas

Sitio de **A volar por el mundo**:
- buscador con precios en línea de LiteAPI (hoteles; vuelos y actividades listos para activar);
- página de cotización con adicionales (actividades, traslados y asistencia médica);
- captura de solicitudes y correos automáticos con Resend;
- backoffice con contenido, tarifas (markup y fees), desglose interno, prospectos y campañas.

En producción: https://demo.avolarporelmundo.co (Dongee, PHP + MySQL).

## Estructura

| Carpeta / archivo | Qué es |
|---|---|
| `avm-terminal.html` | **Fuente** de la página (HTML, CSS y JS en un solo archivo). Se edita aquí. |
| `img/` | Imágenes del sitio (se copian al construir). |
| `datos/` | Contenido inicial (destinos, textos, listas, plantillas). Se convierte en `datos-iniciales.json`. |
| `produccion/public_html/` | El sitio que se sube al hosting. `index.html` y `datos-iniciales.json` se **generan**; el resto (`api/*.php`, `index.php`, etc.) se edita directo. |
| `produccion/avm-config.example.php` | Plantilla de la configuración (base de datos y llaves). |
| `produccion/INSTRUCCIONES.txt` | Instalación paso a paso en Dongee. |
| `herramientas/` | Construir, empaquetar, servidor local y simulador de LiteAPI. |
| `docs/liteapi/` | Referencia de los endpoints de LiteAPI que usa el sitio. |
| `propuesta/` | Propuesta comercial (HTML y PDF). |

## Requisitos para trabajar en otro computador

- Git
- Python 3.10 o superior (para construir y empaquetar)
- PHP 8.0 o superior con `pdo_mysql` y `curl` (XAMPP en Windows sirve)
- MySQL o MariaDB (incluido en XAMPP)
- Node.js, solo si quieres el simulador de LiteAPI

## Puesta en marcha en local

```bash
git clone <URL-del-repositorio> avm
cd avm
```

1. **Base de datos:** crea una base vacía, por ejemplo `avm_local`, con un usuario que tenga todos los permisos.
2. **Configuración:** copia `produccion/avm-config.example.php` como `produccion/avm-config.php` y llena `db_name`, `db_user` y `db_pass`. El archivo queda fuera de `public_html` y Git lo ignora.
   Para probar sin gastar LiteAPI, usa el simulador:
   ```php
   'liteapi_base' => 'http://127.0.0.1:8771/v3.0',
   'liteapi_key'  => 'clave-de-prueba-local',
   ```
3. **Simulador de LiteAPI** (opcional), en otra terminal:
   ```bash
   node herramientas/mock-liteapi.js
   ```
4. **Servidor local:**
   ```bash
   python herramientas/servidor-local.py
   ```
5. Abre http://localhost:8770/instalar.php, crea tu usuario administrador de prueba y entra al backoffice en http://localhost:8770/ingresar.php.

## Hacer cambios

1. Edita `avm-terminal.html` (la página) o `produccion/public_html/api/*.php` (el servidor).
2. Construye la página:
   ```bash
   python herramientas/build.py
   ```
3. Prueba en local y guarda el cambio en Git:
   ```bash
   git add -A
   git commit -m "Describe el cambio"
   git push
   ```

## Desplegar en Dongee

```bash
python herramientas/empaquetar.py 15
```

Genera dos paquetes en `dist/`:
- `actualizacion-15.zip`: los archivos que cambian. Se descomprime sobre la carpeta del sitio.
- `sitio-avm.zip`: el sitio completo, para una instalación nueva.

Después de subir una actualización, vacía el contenido de `/home5/avolarpo/avm-cache` (sin borrar la carpeta).

Para una instalación desde cero, sigue `produccion/INSTRUCCIONES.txt`.

## Seguridad

- Las llaves de LiteAPI y Resend y la contraseña de la base viven solo en `avm-config.php`, fuera de `public_html`. Nunca lo subas al repositorio ni lo compartas por chat.
- El neto del proveedor y las comisiones solo se envían a administradores con sesión iniciada; los desgloses se guardan en la colección `liquidaciones`, que solo leen los administradores.
- Mantén este repositorio **privado**.
