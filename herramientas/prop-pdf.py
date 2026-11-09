"""Versión imprimible y PDF de una propuesta de la carpeta propuesta/.
Uso: python herramientas/prop-pdf.py propuesta-avm-b2c-b2b.html "Propuesta B2C y B2B AVM.pdf"
Incrusta las imágenes, agrega estilos de impresión (A4) y genera el PDF con Chrome o Edge en modo headless."""
import base64, os, re, subprocess, sys

os.chdir(os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), "propuesta"))
src_name = sys.argv[1] if len(sys.argv) > 1 else "propuesta-avm-b2c-b2b.html"
pdf_name = sys.argv[2] if len(sys.argv) > 2 else os.path.splitext(src_name)[0] + ".pdf"
src = open(src_name, encoding="utf-8").read()

def inline(m):
    data = base64.b64encode(open(m.group(1), "rb").read()).decode()
    return 'src="data:image/png;base64,' + data + '"'
body = re.sub(r'src="(img/[^"]+\.png)"', inline, src)

PRINT = """
/* Impresión en A4 */
@page { size: A4; margin: 14mm 12mm }
@media print {
  :root { --paper: #FFFFFF }
  html, body { background: #FFFFFF }
  body { padding-inline: 0; padding-block: 0; font-size: 13px }
  .doc { max-width: none }
  section { padding-block: 22px 0 }
  .cover { padding-block: 0 18px }
  .card, .scope li, .dates li, .path, .risk li, .dec li, .hrs tr, .inv tr { break-inside: avoid }
  .gantt-wrap, .hrs-wrap, .inv-wrap { overflow: visible }
  .gantt, .hrs, .inv { min-width: 0 !important; font-size: 11.5px }
  .gantt .bar, .tag, .tag.on, .hrs tr.sub td, .hrs tr.tot td, .inv tr.rec td, .scen .rec, .dates li, .cover h1 strong, .dec li::before, .scope li { -webkit-print-color-adjust: exact; print-color-adjust: exact }
  .scen, .paths, .two { grid-template-columns: repeat(2, minmax(0, 1fr)) }
  .scope { grid-template-columns: repeat(3, minmax(0, 1fr)) }
  .dates { grid-template-columns: repeat(4, minmax(0, 1fr)) }
  #horas, #cronograma, #firma { break-before: page }
  h2, .eyebrow { break-after: avoid }
}
"""
title = re.search(r"<title>(.*?)</title>", src).group(1)
head_end = body.index("</style>")
page = ('<!doctype html>\n<html lang="es">\n<head>\n<meta charset="utf-8">\n<meta name="viewport" content="width=device-width,initial-scale=1">\n'
        + body[:head_end] + PRINT + body[head_end:].replace("</style>", "</style>\n</head>\n<body>", 1) + "\n</body>\n</html>\n")
print_name = os.path.splitext(src_name)[0] + "-print.html"
open(print_name, "w", encoding="utf-8").write(page)

navegadores = [r"C:\Program Files\Google\Chrome\Application\chrome.exe", r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe",
               r"C:\Program Files\Microsoft\Edge\Application\msedge.exe", "/usr/bin/google-chrome", "/usr/bin/chromium"]
nav = next((n for n in navegadores if os.path.isfile(n)), None)
if not nav:
    sys.exit(f"Listo {print_name}. No encontré Chrome ni Edge para el PDF: ábrelo y usa Imprimir → Guardar como PDF.")
url = "file:///" + os.path.abspath(print_name).replace("\\", "/")
subprocess.run([nav, "--headless=new", "--disable-gpu", "--no-pdf-header-footer", "--print-to-pdf=" + os.path.abspath(pdf_name), url],
               stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, check=False)
print(f"Listo: {print_name} y {pdf_name}")
