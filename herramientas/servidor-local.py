"""Sirve el sitio en local con el servidor de PHP (las direcciones limpias como /hotel/<id> llegan a index.php).
Uso (desde cualquier carpeta): python herramientas/servidor-local.py [puerto]
Busca php en el PATH y, si no está, en C:\\xampp\\php\\php.exe."""
import os, shutil, subprocess, sys

raiz = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
sitio = os.path.join(raiz, "produccion", "public_html")

puerto = sys.argv[1] if len(sys.argv) > 1 else "8770"
php = shutil.which("php") or next((p for p in [r"C:\xampp\php\php.exe"] if os.path.isfile(p)), None)
if not php:
    sys.exit("No encontré PHP. Instala PHP 8 (o XAMPP) y agrégalo al PATH.")
if not os.path.isfile(os.path.join(raiz, "produccion", "avm-config.php")):
    print("Aviso: falta produccion/avm-config.php (cópialo desde avm-config.example.php y llénalo).")
print(f"Sitio en http://localhost:{puerto}  (Ctrl+C para detener)")
subprocess.run([php, "-S", f"localhost:{puerto}", "-t", sitio])
