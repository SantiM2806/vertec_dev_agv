# Tablero de seguimiento del desarrollo

Página para que AVM vea en qué va el proyecto: la fecha de hoy y la semana del plan, el avance frente a lo planeado, el plan por etapas, lo que AVM debe tener listo, el cronograma y los hitos.

## Archivos que se suben al servidor

Sube la carpeta `planeador/` completa, por ejemplo a `https://dominio-de-avm.com/seguimiento/`:

- `index.html`: el tablero, ya con el plan dentro.
- `guardar.php`: guarda el avance en `estado.json`. Pide la clave de edición.
- `.htaccess` e `img/`.

No se suben `plantilla.html` ni `plan.json`, que solo sirven para armar `index.html`. Si se suben, el `.htaccess` los bloquea.

## Clave de edición (una sola vez, en el servidor)

1. Genera el hash de tu clave en una terminal con PHP:

   ```
   php -r "echo password_hash('TU-CLAVE', PASSWORD_DEFAULT), PHP_EOL;"
   ```

2. Crea `clave.php` junto a `guardar.php` con este contenido, pegando el hash:

   ```php
   <?php return '$2y$10$...el-hash...';
   ```

3. La carpeta debe permitir escritura al usuario de PHP, para que se cree `estado.json`.

`clave.php` y `estado.json` no van al repositorio. Al actualizar el tablero, no los borres del servidor: ahí está el avance.

## Uso

- Todos ven la página en modo **Lectura**.
- Para marcar avances, pulsa **Lectura** e ingresa la clave. El botón cambia a **Editando**.
  - En cada entrega, el cuadro pasa de pendiente a en curso y a hecho.
  - Los compromisos de AVM se marcan como listos.
  - Cada entrega admite una nota.
- Se guarda solo, unos segundos después de cada cambio.
- Para ver el tablero en otra fecha (probar), agrega `?hoy=2027-03-01` a la dirección.

## Si cambia el plan

1. El plan sale del mismo generador de la propuesta, que escribe `planeador/plan.json`.
2. Arma de nuevo el tablero con:

   ```
   python herramientas/planeador.py
   ```

3. Sube solo `index.html`.

El avance guardado se conserva porque cada entrega se identifica por su semana (`S08-0`, `S08-1`…). Si el plan corre las semanas, revisa que lo marcado siga coincidiendo.
