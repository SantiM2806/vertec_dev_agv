"""Arma dist/propuesta.zip para subir la propuesta con firma electrónica al servidor (cPanel).
Incluye la propuesta en un solo archivo (index.html, con imágenes incrustadas), firmar.php, .htaccess y el ejemplo de códigos de firma.
Uso: python herramientas/propuesta-avm.py  y luego  python herramientas/empaquetar-propuesta.py"""
import base64, os, re, time, zipfile

raiz = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
prop = os.path.join(raiz, "propuesta")
src = open(os.path.join(prop, "propuesta-avm-b2c-b2b.html"), encoding="utf-8").read()
html = re.sub(r'src="(img/[^"]+\.png)"', lambda m: 'src="data:image/png;base64,' + base64.b64encode(open(os.path.join(prop, m.group(1)), "rb").read()).decode() + '"', src)
title = re.search(r"<title>(.*?)</title>", src).group(1)
fin = html.index("</style>")
pagina = ('<!doctype html>\n<html lang="es">\n<head>\n<meta charset="utf-8">\n<meta name="viewport" content="width=device-width,initial-scale=1">\n<meta name="robots" content="noindex">\n'
          + html[:fin] + html[fin:].replace("</style>", "</style>\n</head>\n<body>", 1) + "\n</body>\n</html>\n")
os.makedirs(os.path.join(raiz, "dist"), exist_ok=True)
destino = os.path.join(raiz, "dist", "propuesta.zip")
def agregar(z, nombre, datos):
    """Agrega un archivo al zip con permisos 644 (lectura para el servidor web, escritura solo para el dueño)."""
    info = zipfile.ZipInfo(nombre, date_time=time.localtime()[:6])
    info.compress_type = zipfile.ZIP_DEFLATED
    info.external_attr = 0o100644 << 16
    z.writestr(info, datos)

with zipfile.ZipFile(destino, "w", zipfile.ZIP_DEFLATED) as z:
    agregar(z, "propuesta/index.html", pagina)
    for a in ("firmar.php", ".htaccess", "firma-codigos.php.ejemplo"):
        agregar(z, "propuesta/" + a, open(os.path.join(prop, "servidor", a), "rb").read())
print("Listo", destino, "·", title)
