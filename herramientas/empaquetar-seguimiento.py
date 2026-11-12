"""Arma dist/seguimiento.zip con solo los archivos del tablero que se suben al servidor.
Uso: python herramientas/empaquetar-seguimiento.py"""
import os, time, zipfile

raiz = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
d = os.path.join(raiz, "planeador")
archivos = ["index.html", "guardar.php", ".htaccess", "img/logo-color.png", "img/logo-blanco.png"]
os.makedirs(os.path.join(raiz, "dist"), exist_ok=True)
destino = os.path.join(raiz, "dist", "seguimiento.zip")
def agregar(z, nombre, datos):
    """Agrega un archivo al zip con permisos 644 (lectura para el servidor web, escritura solo para el dueño)."""
    info = zipfile.ZipInfo(nombre, date_time=time.localtime()[:6])
    info.compress_type = zipfile.ZIP_DEFLATED
    info.external_attr = 0o100644 << 16
    z.writestr(info, datos)

with zipfile.ZipFile(destino, "w", zipfile.ZIP_DEFLATED) as z:
    for a in archivos:
        agregar(z, "seguimiento/" + a, open(os.path.join(d, a), "rb").read())
print("Listo", destino)
