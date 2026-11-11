"""Genera la propuesta B2C y B2B para AVM (propuesta/propuesta-avm-b2c-b2b.html) y el plan del tablero (planeador/plan.json).
Uso: python herramientas/propuesta-avm.py; luego herramientas/prop-pdf.py y herramientas/planeador.py"""
import os
import datetime as dt
RAIZ = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
P = os.path.join(RAIZ, "propuesta", "propuesta-avm-b2c-b2b.html")
src = open(P, encoding="utf-8").read()
head = src[:src.index("</style>")]
# Quitar el CSS que agregó una corrida anterior, para no duplicarlo
MARCA_CSS = "/* Responsabilidades de AVM dentro del plan */"
if MARCA_CSS in head:
    head = head[:head.index(MARCA_CSS)].rstrip() + "\n"
head += """
/* Responsabilidades de AVM dentro del plan */
.plan tr.avm td { background: color-mix(in srgb, var(--todo) 12%, var(--card)); color: var(--ink) }
.plan tr.avm td.w { color: var(--todo) }
.plan tr.cert td { color: var(--muted); font-style: italic }
.plan td .dur { font: 500 11.5px/1 var(--f-mono); color: var(--muted); margin-left: 6px; white-space: nowrap }
/* Qué se entrega con su tiempo */
.items { display: grid; gap: 0; font-size: 14.5px }
.items li { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 12px; padding: 9px 0; border-top: 1px solid var(--line); align-items: baseline }
.items li:first-child { border-top: 0 }
.items li b { font-weight: 600 }
.items li small { display: block; color: var(--muted); font-size: 13px }
.items .t { font: 700 12px/1.3 var(--f-mono); color: var(--signal-text); white-space: nowrap; font-variant-numeric: tabular-nums }
/* Integración de un proveedor */
.steps { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px }
.steps li { border-radius: 14px; padding: 14px; background: var(--soft); display: grid; gap: 4px; align-content: start }
.steps li time { font: 700 11px/1 var(--f-mono); letter-spacing: .06em; text-transform: uppercase; color: var(--signal-text) }
.steps li b { font: 600 15px/1.3 var(--f-display) }
.steps li span { font-size: 13px; color: var(--muted) }
.steps li.wait { background: transparent; border: 1.5px dashed var(--line) }
/* Hitos */
.hitos { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px }
.hitos li { border-left: 4px solid var(--signal); background: var(--card); border-radius: 0 12px 12px 0; padding: 12px 14px; display: grid; gap: 2px }
.hitos li.main { border-left-color: var(--ink) }
.hitos time { font: 700 11.5px/1 var(--f-mono); color: var(--muted); letter-spacing: .04em }
.hitos b { font: 600 15px/1.3 var(--f-display) }
.hitos small { color: var(--muted); font-size: 12.5px }
/* Mapping */
.map-wrap { overflow-x: auto; border: 1px solid var(--line); border-radius: 16px; background: var(--card); padding: 12px }
.map-wrap svg { display: block; width: 100%; min-width: 760px; height: auto }
.mp-box { fill: var(--card); stroke: var(--line); stroke-width: 1.5 }
.mp-soft { fill: var(--soft); stroke: none }
.mp-key { fill: color-mix(in srgb, var(--signal) 26%, var(--card)); stroke: var(--signal); stroke-width: 2 }
.mp-dark { fill: var(--ink) }
.mp-t { fill: var(--ink); font: 600 14px var(--f-display) }
.mp-s { fill: var(--muted); font: 500 12px var(--f-body) }
.mp-m { fill: var(--muted); font: 700 10.5px var(--f-mono); letter-spacing: .08em }
.mp-c { fill: var(--ink); font: 500 11.5px var(--f-mono) }
.mp-w { fill: var(--paper); font: 600 13px var(--f-display) }
.mp-ok { fill: var(--ok); font: 700 13px var(--f-mono) }
.mp-line { stroke: var(--muted); stroke-width: 1.5; fill: none }
.mp-arrow { fill: var(--muted) }
@media (max-width: 760px) { .steps { grid-template-columns: 1fr 1fr } .hitos { grid-template-columns: 1fr } }
/* Cronograma gráfico */
.gw { overflow-x: auto; border: 1px solid var(--line); border-radius: 16px; background: var(--card) }
.gantt2 { min-width: 820px; display: grid }
.gr { display: grid; grid-template-columns: 230px minmax(0, 1fr); border-top: 1px solid var(--line); min-height: 40px }
.gr.gh0 { border-top: 0; min-height: 30px }
.gr.sop { background: var(--soft) }
.gl { padding: 7px 12px 7px 14px; font: 600 13px/1.25 var(--f-body); display: grid; align-content: center }
.gl small { font: 700 9.5px/1.3 var(--f-mono); letter-spacing: .08em; text-transform: uppercase; color: var(--muted) }
.gt { position: relative; margin-right: 12px }
.gt b { position: absolute; top: 0; bottom: 0; width: 0; border-left: 1px dashed var(--line) }
.gm span { position: absolute; top: 9px; font: 700 10.5px/1 var(--f-mono); color: var(--muted); text-transform: uppercase; padding-left: 4px; white-space: nowrap }
.gb { position: absolute; top: 50%; height: 14px; transform: translateY(-50%); border-radius: 5px; display: block }
.gb.c { background: var(--signal) }
.gb.b { background: var(--ink) }
.gb.w { background: var(--ok) }
.gb.s { background: color-mix(in srgb, var(--muted) 45%, transparent); height: 8px }
.gb.cert { background: repeating-linear-gradient(45deg, var(--todo) 0 4px, transparent 4px 8px); height: 10px; border: 1px solid var(--todo) }
.gb.off { top: 0; bottom: 0; height: auto; transform: none; border-radius: 0; background: repeating-linear-gradient(135deg, var(--line) 0 3px, transparent 3px 7px); opacity: .7 }
.gh { position: absolute; top: 50%; transform: translate(-50%, -50%); font-style: normal; font-size: 15px; color: var(--danger); line-height: 1 }
.gleg { display: flex; flex-wrap: wrap; gap: 8px 18px; font-size: 13px; color: var(--muted) }
.gleg span { display: inline-flex; align-items: center; gap: 6px }
.gleg .gb { position: static; transform: none; width: 22px; display: inline-block }
.gleg .gb.off { height: 12px }
.gleg .gh { position: static; transform: none }
/* Cronograma: semanas y meses legibles */
.gantt2 { min-width: 900px }
.gt u { position: absolute; top: 0; bottom: 0; background: color-mix(in srgb, var(--signal) 9%, transparent); text-decoration: none }
.gt b { border-left: 1px solid color-mix(in srgb, var(--line) 55%, transparent) }
.gm span { top: 0; bottom: 0; display: grid; place-items: center; padding: 0; text-align: center; color: var(--ink); font-size: 10.5px; border-left: 1.5px solid var(--line) }
.gr.gh1 { min-height: 22px }
.gn span { position: absolute; top: 0; bottom: 0; display: grid; place-items: center; font: 600 8.5px/1 var(--f-mono); color: var(--muted); font-variant-numeric: tabular-nums }
/* Semanas en paralelo dentro del plan */
.plan .par { display: block }
.plan .par + .par { margin-top: 4px }
.plan .pt { font: 700 10.5px/1 var(--f-mono); font-style: normal; text-transform: uppercase; letter-spacing: .04em; padding: 2px 6px; border-radius: 5px; margin-right: 7px; white-space: nowrap }
.plan .pt.b2c { background: var(--signal); color: var(--ink) }
.plan .pt.b2b { background: var(--ink); color: var(--paper) }
.plan td i { color: var(--muted); font-style: normal; font-size: 12.5px }
.items .t.ok { color: var(--ok) }
.plan tr.avm.opc td { background: var(--soft); color: var(--muted) }
"""

# ---------- Plan semana a semana ----------
# ("fase", nombre, proyecto) · ("s", texto) una semana · ("avm", texto) responsabilidad de AVM antes de esa semana
# ("hito", texto) semana que es hito · ("cert", texto, semanas, proveedor) certificación en paralelo
# ("par", pista_b2c, pista_b2b) las dos pistas avanzan a la vez
def integracion(prov, cert_sem=3, pasos=None):
    pasos = pasos or [f"{prov}: conexión, autenticación, catálogo, disponibilidad y tarifas.",
                      f"{prov}: flujo de reserva (verificación de precio, datos de pasajeros y reserva).",
                      f"{prov}: cancelación, voucher del proveedor y registro en el expediente.",
                      f"{prov}: pruebas, correcciones y envío a certificación."]
    return [("s", p) for p in pasos] + [("cert", f"Certificación de {prov}: revisión del proveedor, en paralelo con lo siguiente. Estimado: {cert_sem} semanas.", cert_sem, prov)]

plan = [
 ("fase", "B2C · Cotizaciones, hoteles, vuelos y actividades con LiteAPI", "B2C"),
 ("avm", "Opcional: AVM empieza a construir la base de datos de agencias (nombre comercial, NIT, correo y teléfono) para la conciliación contable.", "opc"),
 ("s", "PDF de la cotización y del voucher de viaje, con el diseño moderno de la marca."),
 ("s", "Reserva de hoteles con LiteAPI: verificación de precio, reserva y confirmación."),
 ("avm", "AVM solicita a LiteAPI, con mi acompañamiento, la aprobación de pagos en producción y el acceso a Flights y Experiences.", "durante"),
 ("s", "Vuelos con LiteAPI: búsqueda, precios y resultados."),
 ("s", "Vuelos con LiteAPI: datos de pasajeros, reserva y emisión."),
 ("s", "Actividades con LiteAPI: búsqueda, precio y reserva."),
 ("fase", "B2C · Pagos en línea", "B2C"),
 ("avm", "Pasarela de pagos aprobada y lista para integrar (Wompi, PayU, ePayco u otra), a nombre de AVM."),
 ("s", "Pasarela de pagos: tarjeta crédito y débito, PSE y Nequi, con confirmación automática."),
 ("s", "Pasarela de pagos: semana de pruebas con pagos reales, reembolsos y conciliación."),
 ("par",
  [("fase", "B2C · SEO, analítica y automatizaciones", "B2C"),
   ("s", "SEO técnico por destino (una sola configuración para todo el sitio), Google Analytics 4 y conversiones de Google Ads."),
   ("s", "Automatizaciones: ajustes finales a la recaptura de búsquedas y cotizaciones sin compra, y a las campañas promocionales."),
   ("fase", "B2C · Identidad visual, pruebas y salida", "B2C"),
   ("avm", "AVM entrega la identidad visual de la marca B2C: logo, colores y tipografía."),
   ("s", "Identidad visual de la marca B2C aplicada a todo el sitio."),
   ("s", "Pruebas de punta a punta con el equipo de AVM y correcciones."),
   ("s", "Pruebas finales, correcciones y revisión de seguridad."),
   ("hito", "Salida a producción del sitio B2C (requiere la aprobación de LiteAPI en producción).")],
  [("fase", "B2B · Travel Booking & Distribution: base del portal", "B2B"),
   ("s", "Plataforma B2B con su propia marca y su propio backoffice; usuarios del equipo de AVM."),
   ("s", "Registro de agencias: solicitud con RNT y NIT, revisión y aprobación de AVM."),
   ("s", "Usuarios por agencia con roles y carga de la base de datos de agencias de AVM."),
   ("s", "Precios por agencia, grupo, producto y proveedor: reglas de markup y comisión."),
   ("s", "Precios: prioridad de reglas y simulador del precio final.")]),

 ("fase", "B2B · Integración TBO (hoteles)", "B2B"),
 ("avm", "Contrato de uso de API con TBO firmado y credenciales de prueba entregadas."),
 *integracion("TBO"),

 ("fase", "B2B · Mapping: un solo catálogo", "B2B"),
 ("s", "Mapping: catálogo maestro de destinos y hoteles con LiteAPI y TBO."),
 ("s", "Mapping: habitaciones y regímenes, mejor precio y revisión manual de dudosos en el backoffice."),

 ("fase", "B2B · Integración Omnibees (hoteles)", "B2B"),
 ("avm", "Contrato de uso de API con Omnibees firmado y credenciales de prueba entregadas."),
 *integracion("Omnibees"),

 ("fase", "B2B · Integración del tercer proveedor de hoteles", "B2B"),
 ("avm", "Tercer proveedor de hoteles elegido, con contrato de uso de API firmado y credenciales de prueba entregadas."),
 *integracion("Hoteles 3", 3, ["Tercer proveedor de hoteles: conexión, autenticación, catálogo, disponibilidad y tarifas.",
                              "Tercer proveedor de hoteles: flujo de reserva y mapping al catálogo maestro.",
                              "Tercer proveedor de hoteles: cancelación, voucher del proveedor y registro en el expediente.",
                              "Tercer proveedor de hoteles: pruebas, correcciones y envío a certificación."]),

 ("fase", "B2B · Producto propio", "B2B"),
 ("avm", "AVM tiene listo el catálogo completo de producto propio que va a cargar: hoteles, traslados, actividades y circuitos, con tarifas y temporadas."),
 ("s", "Producto propio en el B2B: carga del catálogo de AVM, temporadas, cupos y precios por agencia."),

 ("fase", "B2B · Asistencia médica", "B2B"),
 ("avm", "Proveedor de asistencia médica elegido, con acuerdo firmado y credenciales de prueba."),
 *integracion("Asistencia médica", 2, ["Asistencia médica: conexión, autenticación, planes y tarifas.",
                                      "Asistencia médica: cotización por días, edades y destino.",
                                      "Asistencia médica: emisión, cancelación y voucher dentro del expediente.",
                                      "Asistencia médica: pruebas, correcciones y envío a certificación."]),

 ("fase", "B2B · Pagos y crédito de agencia", "B2B"),
 ("avm", "Pasarela de pagos del B2B aprobada y lista para integrar, a nombre de AVM."),
 ("s", "Pasarela de pagos B2B: tarjeta crédito y débito, billeteras virtuales y PSE."),
 ("s", "Pasarela de pagos B2B: semana de pruebas con pagos reales, reembolsos y conciliación."),
 ("s", "Crédito de agencia: cupo o saldo prepagado, reservas contra el cupo y movimientos."),
 ("s", "Crédito de agencia: estado de cuenta, abonos, alertas y bloqueo por mora."),

 ("fase", "B2B · Cotizaciones, vouchers y operación", "B2B"),
 ("s", "Cotizaciones y vouchers modernos con la marca de cada agencia: enlace, PDF y WhatsApp; un voucher por viaje."),
 ("s", "Operación: reservas y cancelaciones por agencia en el backoffice."),
 ("s", "Operación: reportes de ventas por agencia, proveedor y producto."),
 ("s", "Operación: conciliación con proveedores y registro contable de ventas por agencia."),

 ("fase", "B2B · Pruebas y salida a producción", "B2B"),
 ("avm", "AVM elige 2 o 3 agencias piloto."),
 ("s", "Pruebas con agencias piloto y correcciones."),
 ("s", "Pruebas finales con agencias piloto, correcciones y revisión de seguridad."),
 ("s", "Capacitación del equipo de AVM y manual de uso."),
 ("hito", "Salida a producción de Travel Booking & Distribution con las primeras agencias."),

 ("fase", "White label · El B2C con los proveedores del B2B", "WL"),
 ("s", "El sitio B2C consulta los proveedores del B2B a través del catálogo común."),
 ("s", "Precios del B2C sobre el neto de cada proveedor, con el markup de la marca B2C."),
 ("hito", "Pruebas y salida: el B2C vende con LiteAPI y con los proveedores del B2B."),
]

festivos_lunes = {dt.date(2026, 11, 16), dt.date(2027, 1, 11), dt.date(2027, 3, 22), dt.date(2027, 5, 10), dt.date(2027, 5, 31), dt.date(2027, 6, 7),
                  dt.date(2027, 7, 5), dt.date(2027, 8, 16), dt.date(2027, 10, 18), dt.date(2027, 11, 1), dt.date(2027, 11, 15)}
MES = ["ene", "feb", "mar", "abr", "may", "jun", "jul", "ago", "sep", "oct", "nov", "dic"]
DIAS = ["Lun", "Mar", "Mié", "Jue", "Vie", "Sáb", "Dom"]
f = lambda d: f"{d.day} {MES[d.month - 1]}"
fy = lambda d: f"{d.day} {MES[d.month - 1]} {d.year}"
fdd = lambda d: f"{DIAS[d.weekday()]} {d.day} {MES[d.month - 1]} {d.year}"

INICIO = dt.date(2026, 11, 16)
datos = []
lunes = INICIO; n = 0; rows = []; fases = {}; fmeta = {}; hitos = []; cert = {}; cert_ini = {}; ultimo = None
def semana():
    global lunes, n
    n += 1
    ini = lunes + dt.timedelta(days=1) if lunes in festivos_lunes else lunes
    vie = lunes + dt.timedelta(days=4); lunes += dt.timedelta(days=7)
    return ini, vie
def toca(key, ini, vie):
    a = fases.get(key); fases[key] = (a[0] if a else ini, vie, (a[2] if a else 0) + 1)
def marca(it):
    if it[0] == "fase":
        rows.append(f'<tr class="fase"><td colspan="3">{it[1]}<small>{{R:{it[1]}}}</small></td></tr>'); fmeta[it[1]] = it[2]
        datos.append({"t": "fase", "nombre": it[1], "proy": it[2]})
    elif it[0] == "avm":
        cuando = f"semana del {f(lunes)}" if "durante" in it[2:] else f"antes del {f(lunes)}"
        datos.append({"t": "avm", "id": f"avm{sum(1 for x in datos if x['t'] == 'avm') + 1:02d}", "txt": it[1], "fecha": lunes.isoformat(),
                      "modo": "durante" if "durante" in it[2:] else "antes", "opc": "opc" in it[2:]})
        rows.append(f'<tr class="avm{" opc" if "opc" in it[2:] else ""}"><td class="w">AVM</td><td class="d">{cuando}</td><td>{it[1]}</td></tr>')
def fila(ini, vie, txt, es_hito, tareas=None):
    datos.append({"t": "sem", "n": n, "ini": ini.isoformat(), "fin": vie.isoformat(), "hito": es_hito, "tareas": tareas or []})
    rows.append(f'<tr{" class=\"hito\"" if es_hito else ""}><td class="w">S{n:02d}</td><td class="d">{f(ini)} – {f(vie)}</td><td>{txt}</td></tr>')

def mitades(pista):
    out = []; pre = []; fz = proy = None
    for it in pista:
        if it[0] in ("fase", "avm"):
            pre.append(it)
            if it[0] == "fase": fz, proy = it[1], it[2]
        else:
            for parte in (1, 2):
                out.append(dict(pre=pre if parte == 1 else [], txt=it[1], hito=it[0] == "hito", fase=fz, proy=proy, parte=parte))
            pre = []
    return out

def paralelo(c, b):
    global ultimo
    A, B = mitades(c), mitades(b)
    while A or B:
        if A and B:
            lote = [A.pop(0), B.pop(0)]
        else:
            Q = A or B; h = Q.pop(0)
            if h["parte"] == 1 and Q and Q[0]["parte"] == 2:
                Q.pop(0); h = dict(h, parte=0)
            lote = [h]
        for h in lote:
            for p in h["pre"]: marca(p)
        ini, vie = semana()
        if len(lote) == 2:
            txt = "".join(f'<span class="par"><em class="pt {h["proy"].lower()}">{h["proy"]}</em>{h["txt"]} <i>({"1.ª" if h["parte"] == 1 else "2.ª"} parte)</i></span>' for h in lote)
        else:
            h = lote[0]; txt = h["txt"] + ("" if h["parte"] == 0 else f' <i>({"1.ª" if h["parte"] == 1 else "2.ª"} parte)</i>')
        es_hito = any(h["hito"] and h["parte"] in (0, 2) for h in lote)
        fila(ini, vie, txt, es_hito, [{"proy": h["proy"], "fase": h["fase"], "txt": h["txt"], "parte": h["parte"]} for h in lote])
        for h in lote:
            toca(h["fase"], ini, vie)
            if h["hito"] and h["parte"] in (0, 2): hitos.append((h["txt"], vie))
        ultimo = vie

fase_act = None
for item in plan:
    t = item[0]
    if t == "fase": marca(item); fase_act = item[1]
    elif t == "avm": marca(item)
    elif t == "cert":
        listo = ultimo + dt.timedelta(weeks=item[2])
        rows.append(f'<tr class="cert"><td class="w">⧗</td><td class="d">{f(ultimo + dt.timedelta(days=3))} – {f(listo)}</td><td>{item[1]}</td></tr>')
        cert[item[3]] = listo; cert_ini[item[3]] = ultimo + dt.timedelta(days=3)
        datos.append({"t": "cert", "id": "cert-" + item[3], "prov": item[3], "txt": item[1], "ini": (ultimo + dt.timedelta(days=3)).isoformat(), "fin": listo.isoformat()})
    elif t == "par": paralelo(item[1], item[2])
    else:
        ini, vie = semana(); fila(ini, vie, item[1], t == "hito", [{"proy": fmeta[fase_act], "fase": fase_act, "txt": item[1], "parte": 0}]); toca(fase_act, ini, vie); ultimo = vie
        if t == "hito": hitos.append((item[1], vie))

plan_html = "\n".join(rows)
for nombre, (a, b, k) in fases.items():
    plan_html = plan_html.replace("{R:" + nombre + "}", f"{fy(a)} – {fy(b)} · {k} {'semana' if k == 1 else 'semanas'}")
fin = ultimo
semanas = n
meses = round((fin - dt.date(2026, 11, 17)).days / 30.44)
import json
json.dump({"inicio": INICIO.isoformat(), "fin": fin.isoformat(), "semanas": semanas, "items": datos,
           "fases": [{"nombre": k, "proy": fmeta[k], "ini": a.isoformat(), "fin": b.isoformat(), "semanas": c} for k, (a, b, c) in fases.items()],
           "hitos": [{"txt": t, "fecha": d.isoformat()} for t, d in hitos]},
          open(os.path.join(RAIZ, "planeador", "plan.json"), "w", encoding="utf-8"), ensure_ascii=False, indent=1)
print("semanas", semanas, "fin", fin, "meses", meses, "hitos", hitos, "cert", cert)

b2c, b2b, wl = [d for _, d in hitos]
hitos_html = f"""<li class="main"><time>{fdd(b2c)}</time><b>B2C en producción</b><small>Con hoteles, vuelos y actividades de LiteAPI y pagos en línea.</small></li>
      <li><time>~ {fdd(cert['TBO'])}</time><b>TBO conectado</b><small>Fecha estimada: depende de la certificación de TBO.</small></li>
      <li><time>~ {fdd(cert['Omnibees'])}</time><b>Omnibees conectado</b><small>Fecha estimada: depende del contrato y la certificación.</small></li>
      <li><time>~ {fdd(cert['Hoteles 3'])}</time><b>Tercer proveedor de hoteles conectado</b><small>Depende del proveedor que elija AVM.</small></li>
      <li><time>~ {fdd(cert['Asistencia médica'])}</time><b>Asistencia médica conectada</b><small>Depende del proveedor que elija AVM.</small></li>
      <li class="main"><time>{fdd(b2b)}</time><b>Travel Booking &amp; Distribution en producción</b><small>Con las primeras agencias.</small></li>
      <li class="main"><time>{fdd(wl)}</time><b>B2C con los proveedores del B2B</b><small>Modelo white label: ambos proyectos conectados.</small></li>"""

pesos = lambda v: "$" + f"{v:,}".replace(",", ".")

# ---------- Cronograma gráfico: una columna por semana, meses alternados ----------
G0 = INICIO; G1 = INICIO + dt.timedelta(weeks=semanas)
tot = (G1 - G0).days
pct = lambda d: round(max(0, min(tot, (d - G0).days)) / tot * 100, 3)
def barra(a, b, cls, etiqueta=""):
    a = a - dt.timedelta(days=a.weekday()); b = b - dt.timedelta(days=b.weekday()) + dt.timedelta(days=7)   # semanas completas
    return f'<i class="gb {cls}" style="left:{pct(a)}%;width:{pct(b) - pct(a)}%" title="{etiqueta}"></i>'
# bandas de mes (alternas) y líneas de semana
bandas = []; cab = []; m = dt.date(G0.year, G0.month, 1); k = 0
while m < G1:
    sig = dt.date(m.year + (m.month == 12), m.month % 12 + 1, 1)
    a, b = max(m, G0), min(sig, G1)
    if k % 2 == 0: bandas.append(f'<u style="left:{pct(a)}%;width:{pct(b) - pct(a)}%"></u>')
    nombre_mes = MES[m.month - 1] + (f" {m.year}" if m.month == 1 else "")
    cab.append(f'<span style="left:{pct(a)}%;width:{pct(b) - pct(a)}%">{nombre_mes}</span>')
    m = sig; k += 1
lineas = "".join(f'<b style="left:{pct(G0 + dt.timedelta(weeks=w))}%"></b>' for w in range(1, semanas))
num = "".join(f'<span style="left:{pct(G0 + dt.timedelta(weeks=w))}%;width:{pct(G0 + dt.timedelta(weeks=w + 1)) - pct(G0 + dt.timedelta(weeks=w))}%">{w + 1}</span>' for w in range(semanas))
fondo = "".join(bandas) + lineas
cert_por_fase = {"B2B · Integración TBO (hoteles)": "TBO", "B2B · Integración Omnibees (hoteles)": "Omnibees", "B2B · Integración del tercer proveedor de hoteles": "Hoteles 3", "B2B · Asistencia médica": "Asistencia médica"}
filas_g = []
for nombre, proy in fmeta.items():
    a, b, _ = fases[nombre]
    cls = {"B2C": "c", "B2B": "b", "WL": "w"}[proy]
    extra = ""
    if nombre in cert_por_fase:
        p = cert_por_fase[nombre]; extra = barra(cert_ini[p], cert[p] - dt.timedelta(days=1), "cert", "Certificación " + p)
    hito = [d for _, d in hitos if a <= d <= b]
    marca_h = f'<em class="gh" style="left:{pct(hito[0] + dt.timedelta(days=3))}%">◆</em>' if hito else ""
    corto = nombre.split(" · ", 1)[1]
    filas_g.append(f'<div class="gr"><span class="gl"><small>{proy if proy != "WL" else "White label"}</small>{corto}</span><span class="gt">{fondo}{barra(a, b, cls, nombre)}{extra}{marca_h}</span></div>')
soporte = (f'<div class="gr sop"><span class="gl"><small>Todo el proyecto</small>Soporte Travel Compositor</span><span class="gt">{fondo}{barra(dt.date(2026, 11, 17), fin, "s", "Soporte Travel Compositor")}</span></div>'
           f'<div class="gr sop"><span class="gl"><small>Desde la primera certificación</small>Soporte de proveedores integrados</span><span class="gt">{fondo}{barra(cert["TBO"], fin, "s", "Soporte de proveedores")}</span></div>')
gantt_html = f"""<div class="gw"><div class="gantt2">
        <div class="gr gh0"><span class="gl"></span><span class="gt gm">{"".join(bandas)}{"".join(cab)}</span></div>
        <div class="gr gh1"><span class="gl"><small>Semana</small></span><span class="gt gn">{fondo}{num}</span></div>
        {"".join(filas_g)}
        {soporte}
      </div></div>
      <p class="gleg"><span><i class="gb c"></i>B2C</span><span><i class="gb b"></i>B2B</span><span><i class="gb w"></i>White label</span><span><i class="gb cert"></i>Certificación del proveedor (estimada)</span><span><i class="gb s"></i>Soporte</span><span><em class="gh">◆</em> Hito</span></p>"""

mapping_svg = """<svg viewBox="0 0 960 540" role="img" aria-label="Diagrama del mapping: tres proveedores con códigos distintos para el mismo hotel se unen en un solo hotel del catálogo maestro, y la búsqueda muestra el mejor precio">
  <defs><marker id="ar" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse"><path d="M0 0 10 5 0 10z" class="mp-arrow"/></marker></defs>
  <text x="20" y="28" class="mp-m">CADA PROVEEDOR LLAMA AL HOTEL A SU MANERA</text>
  <text x="372" y="28" class="mp-m">MAPPING</text>
  <text x="676" y="28" class="mp-m">CATÁLOGO MAESTRO DE AVM</text>

  <rect x="20" y="44" width="300" height="74" rx="12" class="mp-box"/>
  <text x="36" y="68" class="mp-t">LiteAPI</text><text x="36" y="88" class="mp-c">lp1003</text><text x="36" y="106" class="mp-s">Iberostar Selection Bávaro</text>
  <rect x="20" y="132" width="300" height="74" rx="12" class="mp-box"/>
  <text x="36" y="156" class="mp-t">TBO</text><text x="36" y="176" class="mp-c">1247731</text><text x="36" y="194" class="mp-s">IBEROSTAR SELECTION BAVARO RESORT</text>
  <rect x="20" y="220" width="300" height="74" rx="12" class="mp-box"/>
  <text x="36" y="244" class="mp-t">Omnibees</text><text x="36" y="264" class="mp-c">OMB-8812</text><text x="36" y="282" class="mp-s">Iberostar Sel. Bávaro - Punta Cana</text>

  <path d="M320 81 C346 81 346 169 372 169" class="mp-line" marker-end="url(#ar)"/>
  <path d="M320 169 L372 169" class="mp-line" marker-end="url(#ar)"/>
  <path d="M320 257 C346 257 346 169 372 169" class="mp-line" marker-end="url(#ar)"/>

  <rect x="374" y="44" width="262" height="250" rx="14" class="mp-soft"/>
  <text x="392" y="74" class="mp-t">1. Normalizar</text><text x="392" y="93" class="mp-s">Nombre, dirección y país sin tildes</text><text x="392" y="109" class="mp-s">ni mayúsculas ni abreviaturas.</text>
  <text x="392" y="140" class="mp-t">2. Comparar</text><text x="392" y="159" class="mp-s">Coordenadas a menos de 150 m</text><text x="392" y="175" class="mp-s">y nombre parecido en más de 85 %.</text>
  <text x="392" y="206" class="mp-t">3. Revisar dudosos</text><text x="392" y="225" class="mp-s">Los casos que no son claros los</text><text x="392" y="241" class="mp-s">confirma AVM en el backoffice.</text>
  <text x="392" y="272" class="mp-t">4. Guardar el vínculo</text><text x="392" y="288" class="mp-s">Se hace una vez y se reutiliza.</text>

  <path d="M636 169 L674 169" class="mp-line" marker-end="url(#ar)"/>
  <rect x="676" y="90" width="264" height="158" rx="14" class="mp-key"/>
  <text x="694" y="118" class="mp-c">AVM-H-00042</text>
  <text x="694" y="142" class="mp-t">Iberostar Selection Bávaro</text>
  <text x="694" y="162" class="mp-s">Punta Cana · 18.68, -68.42</text>
  <text x="694" y="190" class="mp-c">LiteAPI   lp1003</text>
  <text x="694" y="208" class="mp-c">TBO       1247731</text>
  <text x="694" y="226" class="mp-c">Omnibees  OMB-8812</text>

  <line x1="20" y1="324" x2="940" y2="324" class="mp-line" stroke-dasharray="4 5"/>
  <text x="20" y="354" class="mp-m">UNA BÚSQUEDA, TODOS LOS PROVEEDORES</text>
  <rect x="20" y="370" width="230" height="86" rx="12" class="mp-dark"/>
  <text x="38" y="398" class="mp-w">Búsqueda del cliente</text><text x="38" y="420" class="mp-w" style="font-weight:400;font-size:12.5px">Punta Cana · 20 al 23 nov</text><text x="38" y="440" class="mp-w" style="font-weight:400;font-size:12.5px">2 adultos</text>
  <path d="M250 392 C290 392 290 380 330 380" class="mp-line" marker-end="url(#ar)"/>
  <path d="M250 413 L330 413" class="mp-line" marker-end="url(#ar)"/>
  <path d="M250 434 C290 434 290 446 330 446" class="mp-line" marker-end="url(#ar)"/>
  <rect x="332" y="364" width="280" height="30" rx="8" class="mp-box"/><text x="346" y="384" class="mp-c">LiteAPI   lp1003      USD 610</text>
  <rect x="332" y="398" width="280" height="30" rx="8" class="mp-box"/><text x="346" y="418" class="mp-c">TBO       1247731     USD 585</text>
  <rect x="332" y="432" width="280" height="30" rx="8" class="mp-box"/><text x="346" y="452" class="mp-c">Omnibees  OMB-8812    USD 598</text>
  <path d="M612 413 L674 413" class="mp-line" marker-end="url(#ar)"/>
  <rect x="676" y="364" width="264" height="98" rx="14" class="mp-key"/>
  <text x="694" y="390" class="mp-t">1 hotel en el resultado</text>
  <text x="694" y="412" class="mp-s">Iberostar Selection Bávaro</text>
  <text x="694" y="440" class="mp-ok">USD 585 · TBO</text>
  <text x="20" y="500" class="mp-s">Sin mapping, el cliente vería el mismo hotel tres veces con precios distintos, y no se podría comparar ni elegir el mejor proveedor.</text>
  <text x="20" y="520" class="mp-s">Lo mismo se hace con destinos, tipos de habitación y regímenes (por ejemplo «AI», «All Inclusive» y «Todo incluido» son lo mismo).</text>
</svg>"""

body = f"""</style>

<main class="doc">

  <header class="cover">
    <div class="brandrow">
      <span><img class="logo-c" src="img/logo-color.png" alt="AVM Operadores Mayoristas" width="121" height="40"><img class="logo-w" src="img/logo-blanco.png" alt="AVM Operadores Mayoristas" width="121" height="40"></span>
      <span class="meta">Propuesta · 11 nov 2026</span>
    </div>
    <h1>Dos plataformas para AVM: <strong>sitio B2C y Travel Booking &amp; Distribution</strong></h1>
    <p class="lead">Dos marcas, cada una con su propio sitio y su propio backoffice. Primero sale el <b>B2C</b>, que ya está avanzado, con LiteAPI. Después se construye el portal <b>B2B</b> para agencias con los proveedores de AVM. Al final, el B2C vende también con esos proveedores, como un modelo white label.</p>
  </header>

  <section id="alcance">
    <span class="eyebrow">Qué se entrega</span>
    <h2>Dos proyectos, <strong>cada uno con su marca</strong></h2>
    <p class="note">Cada semana termina con un avance visible que AVM revisa en el sitio de prueba. Mientras se pule el B2C, la base del B2B avanza en paralelo.</p>
    <div class="cols">
      <div class="card">
        <span class="tag on">B2C · marca para el viajero</span>
        <ul class="items">
          <li><span><b>Buscador con LiteAPI</b><small>Hoteles, vuelos y actividades. LiteAPI sigue como proveedor adicional cuando llegan los del B2B.</small></span></li>
          <li><span><b>Reserva y pago en el sitio</b><small>Tarjeta crédito y débito, PSE y Nequi, con una semana de pruebas de la pasarela.</small></span></li>
          <li><span><b>Registro de reservas y cotizaciones de los leads</b></span><span class="t ok">Ya construido</span></li>
          <li><span><b>Cotizaciones y vouchers modernos</b><small>Enlace, PDF y WhatsApp; un voucher por viaje.</small></span></li>
          <li><span><b>Backoffice propio</b><small>Diseño, banners, markup y fees, producto y tarifarios propios, desglose de cada venta.</small></span><span class="t ok">Ya construido</span></li>
          <li><span><b>SEO técnico por destino, Google Analytics 4 y Google Ads</b></span></li>
          <li><span><b>Automatizaciones</b><small>Recaptura de búsquedas y cotizaciones sin compra, campañas promocionales.</small></span></li>
          <li><span><b>Identidad visual de la marca B2C</b></span></li>
          <li><span><b>Pruebas y salida a producción</b></span></li>
        </ul>
      </div>
      <div class="card">
        <span class="tag on">B2B · Travel Booking &amp; Distribution</span>
        <ul class="items">
          <li><span><b>Plataforma y backoffice propios</b></span></li>
          <li><span><b>Registro de agencias</b><small>Solicitud, aprobación, usuarios y roles, y carga de la base de datos de AVM.</small></span></li>
          <li><span><b>Precios por agencia, grupo, producto y proveedor</b></span></li>
          <li><span><b>Integración TBO</b> (hoteles)</span></li>
          <li><span><b>Mapping: un solo catálogo</b><small>Cada hotel sale una vez, con el mejor precio entre proveedores.</small></span></li>
          <li><span><b>Integración Omnibees</b> (hoteles)</span></li>
          <li><span><b>Tercer proveedor de hoteles</b><small>El que AVM elija.</small></span></li>
          <li><span><b>Producto propio</b><small>Hoteles, traslados, actividades y circuitos que AVM carga en el backoffice.</small></span></li>
          <li><span><b>Integración de asistencia médica</b> (un proveedor)</span></li>
          <li><span><b>Integración de actividades</b> (un proveedor)</span><span class="t">+1 mes</span></li>
          <li><span><b>Integración de vuelos</b> (un proveedor)</span><span class="t">+1 mes</span></li>
          <li><span><b>Pasarela de pagos</b><small>Tarjetas, billeteras virtuales y PSE, con una semana de pruebas.</small></span></li>
          <li><span><b>Crédito de agencia</b></span></li>
          <li><span><b>Cotizaciones y vouchers modernos</b><small>Con la marca de la agencia; un voucher por viaje.</small></span></li>
          <li><span><b>Operación</b><small>Reservas, cancelaciones, reportes y conciliación con proveedores.</small></span></li>
          <li><span><b>Pruebas con agencias piloto, capacitación y salida</b></span></li>
          <li><span><b>Integración con Dingus</b> (si AVM lo requiere)</span><span class="t">+1 mes</span></li>
        </ul>
      </div>
    </div>
    <p class="note"><b>En el B2B</b> se integran 3 proveedores de hoteles, 1 de asistencia médica, 1 de actividades y 1 de vuelos. El cronograma incluye los 3 de hoteles y el de asistencia médica; actividades y vuelos toman 1 mes cada uno desde que AVM entrega sus credenciales de prueba. <b>En el B2C</b>, vuelos y actividades se venden a través de LiteAPI. <b>White label:</b> al terminar el B2B, el sitio B2C empieza a vender también con los proveedores del B2B.</p>
  </section>

  <section id="hitos">
    <span class="eyebrow">Hitos</span>
    <h2>Las fechas <strong>que importan</strong></h2>
    <ul class="hitos">
      {hitos_html}
    </ul>
    <p class="note">Las fechas con ~ dependen de la certificación de cada proveedor.</p>
  </section>

  <section id="cronograma">
    <span class="eyebrow">Cronograma</span>
    <h2>17 nov 2026 a {fy(fin)} · <strong>{semanas} semanas de desarrollo</strong></h2>
    {gantt_html}
    <p class="note">Durante todo el proyecto se mantiene el soporte de Travel Compositor que ya está acordado con AVM, y desde la primera certificación, el soporte de los proveedores integrados.</p>
  </section>

  <section id="integraciones">
    <span class="eyebrow">Cómo se integra cada proveedor</span>
    <h2>Cuatro semanas de desarrollo <strong>y la certificación del proveedor</strong></h2>
    <ul class="steps">
      <li><time>Semana 1</time><b>Conexión</b><span>Autenticación, catálogo, disponibilidad y tarifas.</span></li>
      <li><time>Semanas 2 y 3</time><b>Flujo de reserva</b><span>Verificación de precio, reserva, cancelación y voucher.</span></li>
      <li><time>Semana 4</time><b>Pruebas</b><span>Pruebas, correcciones y envío a certificación.</span></li>
      <li class="wait"><time>~3 semanas</time><b>Certificación</b><span>La revisa el proveedor. Mientras tanto se avanza con lo siguiente.</span></li>
      <li><time>Al aprobar</time><b>Producción</b><span>El proveedor queda activo para las agencias.</span></li>
    </ul>
    <p class="note">Las 4 semanas empiezan cuando AVM entrega el contrato de uso de la API firmado y las credenciales de prueba. La certificación depende de cada proveedor y de sus tiempos de aprobación; aquí se estima en 3 semanas. <b>Cada proveedor nuevo que AVM quiera sumar toma 1 mes adicional</b> desde la entrega de sus credenciales de prueba.</p>
  </section>

  <section id="mapping">
    <span class="eyebrow">Mapping</span>
    <h2>Por qué hay que mapear <strong>antes de mezclar proveedores</strong></h2>
    <div class="map-wrap">
{mapping_svg}
    </div>
    <div class="two">
      <div class="card">
        <h3>Por qué es importante</h3>
        <ul class="bul">
          <li><span>Cada proveedor usa su propio código y su propio nombre para el mismo hotel.</span></li>
          <li><span>Sin mapping, el mismo hotel sale repetido y no se pueden comparar precios.</span></li>
          <li><span>Con mapping, una sola búsqueda consulta a todos los proveedores y muestra el mejor precio.</span></li>
          <li><span>Es la base para que el B2C use después los proveedores del B2B sin rehacer nada.</span></li>
        </ul>
      </div>
      <div class="card">
        <h3>Cómo queda desarrollado</h3>
        <ul class="bul">
          <li><span>Un catálogo maestro de AVM con un código propio por destino y por hotel.</span></li>
          <li><span>Vinculación automática por coordenadas y nombre, y revisión manual de dudosos en el backoffice.</span></li>
          <li><span>Equivalencias de tipos de habitación y regímenes entre proveedores.</span></li>
          <li><span>Cada proveedor nuevo se mapea al catálogo maestro al integrarlo; los anteriores no se tocan.</span></li>
        </ul>
      </div>
    </div>
  </section>

  <section id="plan">
    <span class="eyebrow">Plan de trabajo</span>
    <h2>Semana a semana · <strong>{semanas} semanas</strong></h2>
    <div class="plan-wrap">
      <table class="plan">
        <thead><tr><th>Sem.</th><th>Fechas</th><th>Qué se entrega</th></tr></thead>
        <tbody>
{plan_html}
        </tbody>
      </table>
    </div>
    <p class="note">Las filas <b>AVM</b> son responsabilidades de AVM que deben estar listas antes de esa semana; si llegan tarde, la etapa se corre el mismo tiempo. Las semanas marcadas B2C y B2B avanzan en paralelo. Las filas marcadas como opcionales no mueven las fechas. Los festivos de lunes ya están descontados.</p>
  </section>

  <section id="requisitos">
    <span class="eyebrow">Lo que mueve las fechas</span>
    <h2>Responsabilidades de AVM <strong>y riesgos</strong></h2>
    <div class="two">
      <div class="card">
        <h3>AVM entrega</h3>
        <ul class="dec">
          <li><span>Una persona designada para decidir<small>Con autoridad para aprobar los entregables y tomar las decisiones importantes: modelo de voucher, módulo de administración, UX/UI del sitio.</small></span></li>
          <li><span>Base de datos de agencias<small>Nombre comercial, NIT, correo y teléfono de cada agencia, para registrar sus ventas y la conciliación contable. Desde ya.</small></span></li>
          <li><span>Aprobación de LiteAPI<small>Pagos en producción y acceso a Flights y Experiences. Sin ella, el B2C no sale a producción.</small></span></li>
          <li><span>Pasarela de pagos aprobada antes de su semana<small>La que AVM elija, a nombre de AVM, lista para integrar antes de que empiece la semana de integración, tanto en el B2C como en el B2B.</small></span></li>
          <li><span>Contrato de uso de API con cada proveedor<small>Firmado, con credenciales de prueba, antes de su integración: 3 de hoteles, asistencia médica, actividades y vuelos. Con Omnibees aún no hay contacto ni contrato.</small></span></li>
          <li><span>Tercer proveedor de hoteles, asistencia médica, actividades y vuelos<small>Proveedor elegido y acuerdo firmado antes de su integración.</small></span></li>
          <li><span>Catálogo de producto propio<small>Completo, con tarifas y temporadas, antes de su semana de carga en el B2B.</small></span></li>
          <li><span>Identidad visual de la marca B2C<small>Logo, colores y tipografía antes de su semana.</small></span></li>
          <li><span>Pruebas<small>2 o 3 agencias piloto para las pruebas del B2B.</small></span></li>
        </ul>
      </div>
      <div class="card">
        <h3>Riesgos</h3>
        <ul class="risk">
          <li><b>Certificación de proveedores</b><span>Cada proveedor revisa la integración antes de abrir producción; puede tardar de 2 a 6 semanas. Mientras tanto se avanza con la fase siguiente.</span></li>
          <li><b>Cambios de alcance</b><span>Un proveedor o un producto nuevo suma sus semanas y se acuerda por escrito antes de empezarlo.</span></li>
        </ul>
        <h3 style="margin-top:10px">Fuera de alcance</h3>
        <ul class="bul">
          <li><span>Pauta publicitaria y manejo de campañas.</span></li>
          <li><span>Integración con sistemas contables.</span></li>
          <li><span>Aplicación para celulares.</span></li>
          <li><span>Fotografías y textos de destinos.</span></li>
          <li><span>Soporte a las agencias.</span></li>
          <li><span>Atención al cliente directo del canal B2C: AVM debe destinar una persona para esto.</span></li>
        </ul>
      </div>
    </div>
  </section>

  <section id="inversion">
    <span class="eyebrow">Inversión</span>
    <h2>{meses} meses · <strong>una mensualidad</strong></h2>
    <div class="card price">
      <span class="eyebrow">Mensualidad durante el desarrollo</span>
      <p class="amount">$3.500.000 <span>COP mensuales · {meses} meses · total {pesos(3500000 * meses)}</span></p>
      <p class="note">Del 17 de noviembre de 2026 al {fy(fin)}. Cubre el desarrollo, las pruebas, los despliegues y reuniones de seguimiento de ambos proyectos.</p>
    </div>
    <p class="note"><b>Proveedores adicionales</b> (por ejemplo Dingus): 1 mes adicional cada uno, desde la entrega de sus credenciales de prueba.</p>
  </section>

  <section id="costos">
    <span class="eyebrow">Costos asociados</span>
    <h2>Costos de terceros, <strong>a nombre de AVM</strong></h2>
    <p class="note">Servidor (de 10 a 40 USD al mes), dominio, correos (Resend), comisiones de la pasarela por transacción, de los proveedores y de LiteAPI, y el presupuesto de pauta.</p>
  </section>

  <section id="firma">
    <span class="eyebrow">Aceptación</span>
    <h2>Firma de <strong>la propuesta</strong></h2>
    <ol class="terms">
      <li>Las partes aceptan el alcance, el plan de trabajo y lo que queda fuera de alcance, tal como están descritos en esta propuesta.</li>
      <li>Las fechas suponen que AVM cumple a tiempo sus responsabilidades. Un retraso en ellas, o en la certificación de un proveedor, corre las fechas en el mismo tiempo.</li>
      <li>Cambios de alcance después de la firma se acuerdan por escrito, con su ajuste de tiempo y valor.</li>
    </ol>
    <div class="signs">
      <div class="sign"><span class="sline"></span><b>Por AVM Operadores Mayoristas</b><span>Nombre:</span><span>C.C. / NIT:</span><span>Fecha:</span></div>
      <div class="sign"><span class="sline"></span><b>Por el desarrollador</b><span>Nombre: <strong>Santiago Martínez</strong></span><span>C.C. / NIT: <strong>1.076.623.993</strong></span><span>Fecha: <strong>11 de noviembre de 2026</strong></span></div>
    </div>
  </section>

</main>
"""
open(P, "w", encoding="utf-8").write(head + body)
print("ok")
