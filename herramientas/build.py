"""Construye produccion/public_html/index.html desde avm-terminal.html, copia img/ y genera datos-iniciales.json desde datos/.
Uso (desde cualquier carpeta): python herramientas/build.py"""
import sys, os, json, shutil, glob
os.chdir(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))  # raíz del repositorio
src, out, dbdir = (sys.argv[1:4] + ["avm-terminal.html", "produccion/public_html", "datos"][len(sys.argv[1:4]):])[:3]
s = open(src, encoding="utf-8").read()
def rep(a, b, n=1):
    global s
    c = s.count(a); assert c == n, (a[:70], c); s = s.replace(a, b)
rep("<title>Terminal AVM</title>", '<title>Agencia de viajes</title>\n<meta name="description" content="Cotiza en línea tu viaje: hoteles, traslados, actividades y asistencia médica.">')
s = '<!doctype html>\n<html lang="es">\n<head>\n<meta charset="utf-8">\n<meta name="viewport" content="width=device-width,initial-scale=1">\n' + s
rep("\n<script>\n", '\n<script src="avm-db.js"></script>\n<script>\n')
rep("async function runEngine() {\n", "async function runEngine() {\n  if (window.AVM_SERVER_ENGINE) return; // en producción las automatizaciones corren en el servidor\n")
rep('Para gestionar el sitio, abre esta página desde claude.ai con tu sesión iniciada. Mientras tanto ves el contenido por defecto.', 'No hay conexión con el servidor. Revisa la configuración del sitio.')
rep('Tu acceso a esta página es de solo lectura. Pide al propietario que te invite como editor para hacer cambios.', 'Tu sesión terminó. Vuelve a ingresar en ingresar.php.')
rep('Para guardar solicitudes, abre el sitio desde claude.ai con tu sesión iniciada.', 'No hay conexión con el servidor en este momento.')
rep('"Abre esta página desde claude.ai con tu sesión iniciada para poder guardar."', '"No hay conexión con el servidor. Intenta de nuevo."')
rep('  hotelApi: "",\n', '  hotelApi: "api/hoteles.php",\n')
rep('<a href="#inicio">← Ver sitio</a></div>', '<a href="#inicio">← Ver sitio</a><a href="ingresar.php?salir=1">Cerrar sesión</a></div>')
assert "claude.ai" not in s, [l for l in s.splitlines() if "claude.ai" in l][:3]
os.makedirs(out + "/img", exist_ok=True)
open(out + "/index.html", "w", encoding="utf-8").write(s)
for f in glob.glob("img/*"): shutil.copy(f, out + "/img/")
seed = {}
for f in glob.glob(dbdir + "/*/*.json"):
    coll = os.path.basename(os.path.dirname(f)); doc = os.path.basename(f)[:-5]
    if coll in ("site", "destinations", "tours", "groups", "lists", "templates", "automations", "images"):
        seed[f"{coll}/{doc}"] = json.load(open(f, encoding="utf-8"))
json.dump(seed, open(out + "/datos-iniciales.json", "w", encoding="utf-8"), ensure_ascii=False, indent=1)
print("ok", len(seed), "documentos")
