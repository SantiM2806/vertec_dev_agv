"""Genera propuesta/propuesta-avm-print.html (para PDF) a partir de propuesta/propuesta-avm.html."""
import base64, re, os
os.chdir(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
src = open("propuesta/propuesta-avm.html", encoding="utf-8").read()
title = re.search(r"<title>(.*?)</title>", src).group(1)
head_links = src[src.index("<link"):src.index("<style>")]
css = src[src.index("<style>") + 7:src.index("</style>")]
body = src[src.index("</style>") + 8:]
def inline(m):
    path = os.path.join("propuesta", m.group(1))
    data = base64.b64encode(open(path, "rb").read()).decode()
    return 'src="data:image/png;base64,' + data + '"'
body = re.sub(r'src="(img/[^"]+\.png)"', inline, body)
PRINT = """
/* Versión impresa */
@page { size: A4; margin: 14mm 12mm }
@media print {
  :root { --paper: #FFFFFF }
  html, body { background: #FFFFFF }
  body { padding-inline: 0; padding-block: 0; font-size: 13.5px }
  .doc { max-width: none }
  section { padding-block: 22px 0; break-inside: auto }
  .cover { padding-block: 0 20px }
  .card, .what li, .dates li, .integ li, .admin-tc li, .gantt-wrap, .path, .terms { break-inside: avoid }
  .gantt-wrap { overflow: visible }
  .gantt { min-width: 0; grid-template-columns: minmax(170px, 1.4fr) repeat(5, minmax(0, 1fr)); font-size: 12px }
  .gantt .bar, .path.inc .tag, .path.inc .letter, .cover h1 strong, .dates li, .goal .card, .path, .letter, .tag, .dec li::before, .integ .tag.ok { -webkit-print-color-adjust: exact; print-color-adjust: exact }
  .goal, .paths, .two { grid-template-columns: repeat(2, minmax(0, 1fr)) }
  .what, .integ { grid-template-columns: repeat(3, minmax(0, 1fr)) }
  .dates, .admin-tc { grid-template-columns: repeat(4, minmax(0, 1fr)) }
  #fechas, #integraciones, #compra, #crecimiento { break-inside: avoid }
  #administracion { break-before: page }
  h2, .eyebrow { break-after: avoid }
  #firma { break-before: page }
}
"""
def page(extra):
    return ('<!doctype html>\n<html lang="es">\n<head>\n<meta charset="utf-8">\n<meta name="viewport" content="width=device-width,initial-scale=1">\n'
            f"<title>{title}</title>\n{head_links}<style>\n[hidden]{{display:none!important}}\nbody{{margin:0}}\n{css}{extra}</style>\n</head>\n<body>\n{body.strip()}\n</body>\n</html>\n")
open("propuesta/propuesta-avm-completa.html", "w", encoding="utf-8").write(page(""))
open("propuesta/propuesta-avm-print.html", "w", encoding="utf-8").write(page(PRINT))
print("ok")
