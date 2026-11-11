"""Arma dist/seguimiento.zip con solo los archivos del tablero que se suben al servidor.
Uso: python herramientas/empaquetar-seguimiento.py"""
import os, zipfile

raiz = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
d = os.path.join(raiz, "planeador")
archivos = ["index.html", "guardar.php", ".htaccess", "img/logo-color.png", "img/logo-blanco.png"]
os.makedirs(os.path.join(raiz, "dist"), exist_ok=True)
destino = os.path.join(raiz, "dist", "seguimiento.zip")
with zipfile.ZipFile(destino, "w", zipfile.ZIP_DEFLATED) as z:
    for a in archivos:
        z.write(os.path.join(d, a), "seguimiento/" + a)
print("Listo", destino)
