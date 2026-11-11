"""Arma planeador/index.html a partir de planeador/plantilla.html y planeador/plan.json.
plan.json lo genera el script de la propuesta, así el tablero usa las mismas semanas y fechas.
Uso: python herramientas/planeador.py"""
import json, os, shutil

raiz = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
d = os.path.join(raiz, "planeador")
plan = json.load(open(os.path.join(d, "plan.json"), encoding="utf-8"))
html = open(os.path.join(d, "plantilla.html"), encoding="utf-8").read()
datos = json.dumps(plan, ensure_ascii=False, separators=(",", ":")).replace("</", "<\\/")
open(os.path.join(d, "index.html"), "w", encoding="utf-8").write(html.replace("/*PLAN*/", datos))
os.makedirs(os.path.join(d, "img"), exist_ok=True)
for logo in ("logo-color.png", "logo-blanco.png"):
    shutil.copyfile(os.path.join(raiz, "propuesta", "img", logo), os.path.join(d, "img", logo))
print(f"Listo planeador/index.html · {plan['semanas']} semanas, {plan['inicio']} a {plan['fin']}")
