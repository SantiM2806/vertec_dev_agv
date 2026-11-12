"""Arma dist/propuesta.zip para subir la propuesta con firma electrónica al servidor (cPanel).
Incluye la propuesta en un solo archivo (index.html, con imágenes incrustadas), firmar.php, .htaccess y el ejemplo de códigos de firma.
Uso: python herramientas/propuesta-avm.py  y luego  python herramientas/empaquetar-propuesta.py"""
import base64, os, re, zipfile

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
with zipfile.ZipFile(destino, "w", zipfile.ZIP_DEFLATED) as z:
    z.writestr("propuesta/index.html", pagina)
    for a in ("firmar.php", ".htaccess", "firma-codigos.php.ejemplo"):
        z.write(os.path.join(prop, "servidor", a), "propuesta/" + a)
print("Listo", destino, "·", title)
