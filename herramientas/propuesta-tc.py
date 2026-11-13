"""Genera la propuesta de administración y gestión de Travel Compositor para AVM (propuesta/propuesta-tc.html)
y su paquete para el servidor (dist/propuesta-tc.zip), con la misma línea gráfica y la firma electrónica de la propuesta B2C/B2B.
Uso: python herramientas/propuesta-tc.py"""
import base64, os, re, sys, time, zipfile

RAIZ = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
PROP = os.path.join(RAIZ, "propuesta")
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from firma_bloque import FIRMA_HTML

# Mismo encabezado y estilos de la propuesta principal (ya trae la línea gráfica y los estilos de la firma)
base = open(os.path.join(PROP, "propuesta-avm-b2c-b2b.html"), encoding="utf-8").read()
head = base[:base.index("</style>")].replace("<title>Propuesta Plataforma AVM</title>", "<title>Propuesta Travel Compositor AVM</title>")
head += """
/* Propuesta Travel Compositor */
.srv { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px }
.srv li { border: 1px solid var(--line); background: var(--card); border-radius: 14px; padding: 14px 16px; display: grid; gap: 4px; align-content: start }
.srv li b { font: 600 15.5px/1.3 var(--f-display) }
.srv li span { font-size: 14px; color: var(--muted) }
.srv li i { font-style: normal; font-size: 18px }
.sla { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px }
.sla li { border-radius: 14px; padding: 16px; background: var(--card); border: 1px solid var(--line); border-top: 4px solid var(--c); display: grid; gap: 6px; align-content: start }
.sla li em { font: 700 11px/1 var(--f-mono); letter-spacing: .08em; text-transform: uppercase; color: var(--c); font-style: normal }
.sla li b { font: 700 24px/1.1 var(--f-display) }
.sla li span { font-size: 13.5px; color: var(--muted) }
.horario { display: flex; gap: 14px; align-items: center; flex-wrap: wrap; border: 1px solid var(--signal); border-radius: 14px; padding: 16px 18px; background: color-mix(in srgb, var(--signal) 10%, var(--card)) }
.horario b { font: 700 20px/1.2 var(--f-display) }
.horario span { color: var(--muted); font-size: 14px }
@media (max-width: 760px) { .srv, .sla { grid-template-columns: 1fr } }
"""

SERVICIOS = [
    ("🏢", "Configuración de agencias", "Alta, datos, condiciones y ajustes de cada agencia en el portal."),
    ("👤", "Creación de usuarios", "Usuarios y permisos para las agencias y para el equipo de AVM."),
    ("💲", "Configuración de tarifas", "Márgenes, comisiones y condiciones por agencia, producto o proveedor."),
    ("🔌", "Integración de proveedores", "Activación y configuración de proveedores de hoteles, traslados y servicios disponibles en Travel Compositor."),
    ("📦", "Productos propios", "Carga y actualización de los paquetes, circuitos y servicios de AVM."),
    ("🎫", "Tickets con Travel Compositor", "Reporte, seguimiento y cierre de incidencias con el soporte del proveedor."),
    ("🤝", "Soporte a AVM", "Acompañamiento al equipo en el uso diario del portal y en la resolución de dudas."),
    ("🎓", "Capacitación", "Capacitación al equipo de AVM y a las agencias nuevas en el uso del portal."),
]
srv_html = "\n".join(f'      <li><i aria-hidden="true">{e}</i><b>{t}</b><span>{d}</span></li>' for e, t, d in SERVICIOS)

body = f"""</style>

<main class="doc">

  <header class="cover">
    <div class="brandrow">
      <span><img class="logo-c" src="img/logo-color.png" alt="AVM Operadores Mayoristas" width="121" height="40"><img class="logo-w" src="img/logo-blanco.png" alt="AVM Operadores Mayoristas" width="121" height="40"></span>
      <span class="meta">Propuesta · {time.strftime('%d')} nov 2026</span>
    </div>
    <h1>Administración y gestión de <strong>Travel Compositor</strong></h1>
    <p class="lead">Me encargo de la operación diaria del portal de reservas de AVM en Travel Compositor: agencias, usuarios, tarifas, proveedores y productos propios, el contacto con el soporte del proveedor y el acompañamiento al equipo. Así AVM y sus agencias venden sin depender de configuraciones pendientes.</p>
  </header>

  <section id="alcance">
    <span class="eyebrow">Qué incluye</span>
    <h2>Todo lo que necesita <strong>el portal para operar</strong></h2>
    <ul class="srv">
{srv_html}
    </ul>
  </section>

  <section id="atencion">
    <span class="eyebrow">Atención</span>
    <h2>Todos los días, <strong>con tiempos de respuesta claros</strong></h2>
    <div class="horario"><b>Lunes a domingo</b><span>Las solicitudes llegan por WhatsApp o correo y cada una se atiende según su urgencia.</span></div>
    <ul class="sla">
      <li style="--c:var(--danger)"><em>Urgente</em><b>Hasta 2 horas</b><span>El portal o las reservas están detenidos, o una agencia no puede vender.</span></li>
      <li style="--c:var(--naranja)"><em>Normal</em><b>El mismo día</b><span>Usuarios, agencias, ajustes de tarifas, dudas de uso y tickets con Travel Compositor.</span></li>
      <li style="--c:var(--signal)"><em>Programado</em><b>Hasta 3 días</b><span>Proveedores nuevos, carga de productos propios y capacitaciones, según la información que entregue AVM.</span></li>
    </ul>
    <p class="note">Los tiempos cuentan desde que llega la solicitud con la información completa. Lo que dependa del soporte de Travel Compositor sigue sus tiempos; yo hago el seguimiento hasta cerrarlo.</p>
  </section>

  <section id="requisitos">
    <span class="eyebrow">Lo que necesito de AVM</span>
    <h2>Para operar <strong>sin demoras</strong></h2>
    <div class="two">
      <div class="card">
        <h3>AVM entrega</h3>
        <ul class="dec">
          <li><span>Acceso de administrador<small>Usuario con permisos de administración en Travel Compositor.</small></span></li>
          <li><span>Una persona que aprueba<small>Valida los cambios de tarifas, márgenes y comisiones antes de aplicarlos.</small></span></li>
          <li><span>Información completa<small>Datos de agencias, tarifas, productos propios y contratos con proveedores.</small></span></li>
          <li><span>Contrato con Travel Compositor vigente<small>Licencia y soporte del proveedor a nombre de AVM.</small></span></li>
        </ul>
      </div>
      <div class="card">
        <h3>Fuera de alcance</h3>
        <ul class="bul">
          <li><span>Desarrollo a la medida o integraciones por API con Travel Compositor.</span></li>
          <li><span>Licencia y costos de Travel Compositor y de los proveedores.</span></li>
          <li><span>Venta y atención a clientes finales y agencias en sus reservas.</span></li>
          <li><span>Diseño gráfico, fotografías y textos de productos.</span></li>
        </ul>
      </div>
    </div>
  </section>

  <section id="inversion">
    <span class="eyebrow">Inversión</span>
    <h2>Una mensualidad, <strong>sin plazo fijo</strong></h2>
    <div class="card price">
      <span class="eyebrow">Administración y gestión de Travel Compositor</span>
      <p class="amount">$1.500.000 <span>COP mensuales</span></p>
      <p class="note">El servicio se renueva cada mes. Cualquiera de las partes puede terminarlo avisando con 30 días de anticipación.</p>
    </div>
    <p class="note"><b>Costos de terceros, a nombre de AVM:</b> licencia y soporte de Travel Compositor y comisiones de los proveedores.</p>
  </section>

  <section id="firma">
    <span class="eyebrow">Aceptación</span>
    <h2>Firma de <strong>la propuesta</strong></h2>
    <ol class="terms">
      <li>Las partes aceptan el alcance, la atención y lo que queda fuera de alcance, tal como están descritos en esta propuesta.</li>
      <li>Los tiempos de respuesta suponen que AVM entrega la información completa y los accesos necesarios.</li>
      <li>Cambios de alcance se acuerdan por escrito, con su ajuste de valor.</li>
    </ol>
    {FIRMA_HTML}
  </section>

</main>
"""
html_src = head + body
open(os.path.join(PROP, "propuesta-tc.html"), "w", encoding="utf-8").write(html_src)

# Versión de un solo archivo (imágenes incrustadas) y paquete para el servidor
html = re.sub(r'src="(img/[^"]+\.png)"', lambda m: 'src="data:image/png;base64,' + base64.b64encode(open(os.path.join(PROP, m.group(1)), "rb").read()).decode() + '"', html_src)
fin = html.index("</style>")
pagina = ('<!doctype html>\n<html lang="es">\n<head>\n<meta charset="utf-8">\n<meta name="viewport" content="width=device-width,initial-scale=1">\n<meta name="robots" content="noindex">\n'
          + html[:fin] + html[fin:].replace("</style>", "</style>\n</head>\n<body>", 1) + "\n</body>\n</html>\n")
open(os.path.join(PROP, "propuesta-tc-completa.html"), "w", encoding="utf-8").write(pagina)

def agregar(z, nombre, datos):
    info = zipfile.ZipInfo(nombre, date_time=time.localtime()[:6]); info.compress_type = zipfile.ZIP_DEFLATED
    info.external_attr = 0o100644 << 16; z.writestr(info, datos)
os.makedirs(os.path.join(RAIZ, "dist"), exist_ok=True)
with zipfile.ZipFile(os.path.join(RAIZ, "dist", "propuesta-tc.zip"), "w", zipfile.ZIP_DEFLATED) as z:
    agregar(z, "propuesta-tc/index.html", pagina)
    for a in ("firmar.php", ".htaccess", "firma-codigos.php.ejemplo"):
        agregar(z, "propuesta-tc/" + a, open(os.path.join(PROP, "servidor", a), "rb").read())
print("Listo propuesta/propuesta-tc.html y dist/propuesta-tc.zip")
