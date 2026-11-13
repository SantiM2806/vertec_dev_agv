"""Bloque de firma electrónica de la propuesta: estilos, recuadros de firma y lógica (dibujar o firmar con el nombre).
Lo importa herramientas/propuesta-avm.py. Necesita firmar.php junto a la propuesta en el servidor (propuesta/servidor/)."""

FIRMA_CSS = """
/* Firma electrónica */
.fbox [hidden], .fb-descarga[hidden] { display: none !important }
.firmas { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin-top: 28px }
.fbox { border: 1px solid var(--line); background: var(--card); border-radius: 16px; padding: 18px; display: grid; gap: 12px; align-content: start; min-width: 0 }
.fbox.firmado { border-color: var(--signal) }
.fb-head { display: flex; justify-content: space-between; gap: 8px; flex-wrap: wrap; align-items: center }
.fb-head b { font: 600 16px/1.3 var(--f-display) }
.fb-estado { font: 700 10.5px/1 var(--f-mono); text-transform: uppercase; letter-spacing: .06em; padding: 5px 8px; border-radius: 6px; background: color-mix(in srgb, var(--naranja) 18%, transparent); color: var(--naranja-text) }
.fbox.firmado .fb-estado { background: color-mix(in srgb, var(--signal) 22%, transparent); color: var(--signal-text) }
.fb-pad { display: grid; gap: 10px }
.fb-tabs { display: inline-flex; background: var(--soft); border-radius: 10px; padding: 3px; gap: 2px; justify-self: start; flex-wrap: wrap }
.fb-tabs button { padding: 6px 11px; border-radius: 8px; font: 600 13px var(--f-body); color: var(--muted); background: none; border: 0; cursor: pointer }
.fb-tabs button[aria-pressed="true"] { background: var(--card); color: var(--ink); box-shadow: 0 1px 3px rgb(0 0 0 / .2) }
.fb-lienzo { position: relative; background: #FFFFFF; border-radius: 12px; border: 1.5px dashed #C9CFBD; height: 160px; touch-action: none }
.fb-lienzo canvas { position: absolute; inset: 0; width: 100%; height: 100%; display: block; border-radius: 12px; cursor: crosshair }
.fb-lienzo .linea { position: absolute; left: 22px; right: 22px; bottom: 40px; border-top: 1px solid #D7DCCB; pointer-events: none }
.fb-lienzo .ph { position: absolute; left: 0; right: 0; bottom: 12px; text-align: center; font: 500 12px var(--f-body); color: #8C947F; pointer-events: none }
.fb-limpiar { position: absolute; top: 8px; right: 8px; font: 600 12px var(--f-body); color: #5B6352; background: #F1F3EC; border: 0; border-radius: 7px; padding: 5px 9px; cursor: pointer }
.fb-campos { display: grid; grid-template-columns: 1fr 1fr; gap: 8px }
.fb-campos label { display: grid; gap: 4px; font: 600 12px var(--f-body); color: var(--muted) }
.fb-campos input { font: 14px var(--f-body); padding: 9px 11px; border-radius: 9px; border: 1px solid var(--line); background: var(--soft); color: var(--ink); width: 100% }
.fb-campos input:focus { outline: 2px solid var(--signal); outline-offset: 0; border-color: transparent }
.fb-campos .full { grid-column: 1 / -1 }
.fb-acepto { display: flex; gap: 9px; font-size: 13.5px; color: var(--muted); align-items: flex-start; cursor: pointer }
.fb-acepto input { margin-top: 3px; accent-color: #94B63C; width: 16px; height: 16px }
.fb-btn { height: 44px; border-radius: 10px; border: 0; font: 700 14.5px var(--f-body); background: var(--signal); color: var(--signal-ink); cursor: pointer }
.fb-btn:disabled { opacity: .45; cursor: not-allowed }
.fb-err { color: var(--danger); font-size: 13px }
.fb-firmado { display: grid; gap: 10px }
.fb-img { background: #FFFFFF; border-radius: 12px; padding: 10px; display: grid; place-items: center; min-height: 120px }
.fb-img img { max-width: 100%; max-height: 140px }
.fb-datos { font-size: 13.5px; color: var(--muted); display: grid; gap: 3px }
.fb-datos b { color: var(--ink); font-weight: 600 }
.fb-datos code { font: 500 10.5px/1.4 var(--f-mono); word-break: break-all; color: var(--muted) }
.fb-cambio { font-size: 12.5px; color: var(--danger) }
.fb-aviso { font-size: 13.5px; color: var(--muted); background: var(--soft); border-radius: 10px; padding: 10px 12px }
.fb-descarga { margin-top: 16px; border: 1.5px solid var(--signal); border-radius: 16px; padding: 18px 20px; display: grid; gap: 12px; background: color-mix(in srgb, var(--signal) 10%, var(--card)) }
.fb-descarga b { font: 600 16px/1.3 var(--f-display) }
.fb-descarga p { font-size: 13.5px; color: var(--muted); margin: 0 }
.fb-descarga .acc { display: flex; gap: 10px; flex-wrap: wrap }
.fb-descarga button { height: 44px; padding: 0 18px; border-radius: 10px; font: 700 14px var(--f-body); cursor: pointer; border: 1px solid var(--line); background: var(--card); color: var(--ink) }
.fb-descarga button.pri { background: var(--signal); border-color: var(--signal); color: var(--signal-ink) }
.fb-print { display: none }
.fb-print .sline { display: block; border-top: 1.5px solid var(--ink); margin: 40px 0 6px }
.fb-print span { display: block; padding: 6px 0; border-bottom: 1px solid var(--line); font-size: 13.5px; color: var(--muted) }
@media print {
  .fb-pad, .fb-aviso, .fb-estado, .fb-descarga { display: none !important }
  .fbox:not(.firmado) .fb-print { display: block }
  .fbox { break-inside: avoid; border: 0; padding: 0 }
  .fb-img { -webkit-print-color-adjust: exact; print-color-adjust: exact }
}
@media (max-width: 760px) { .firmas { grid-template-columns: 1fr } .fb-campos { grid-template-columns: 1fr } }
"""

def caja(parte, titulo, nombre="", documento=""):
    return f"""<div class="fbox" data-parte="{parte}">
        <div class="fb-head"><b>{titulo}</b><span class="fb-estado">Pendiente de firma</span></div>
        <p class="fb-aviso" hidden></p>
        <div class="fb-pad" hidden>
          <div class="fb-tabs" role="group" aria-label="Forma de firmar"><button type="button" data-m="dibujo" aria-pressed="true">Dibujar firma</button><button type="button" data-m="nombre" aria-pressed="false">Firmar con mi nombre (un clic)</button></div>
          <div class="fb-lienzo"><canvas aria-label="Recuadro para dibujar la firma"></canvas><span class="linea"></span><span class="ph">Dibuja tu firma con el mouse o el dedo</span><button type="button" class="fb-limpiar">Borrar</button></div>
          <div class="fb-campos">
            <label>Nombre completo<input name="nombre" autocomplete="name" value="{nombre}"></label>
            <label>C.C. / NIT<input name="documento" inputmode="numeric" value="{documento}"></label>
            <label class="full fb-cod" hidden>Código de firma<input name="codigo" autocomplete="off" type="password"></label>
          </div>
          <label class="fb-acepto"><input type="checkbox" name="acepto"> Acepto el alcance, el plan de trabajo, la inversión y los términos de esta propuesta.</label>
          <span class="fb-err" hidden></span>
          <button type="button" class="fb-btn" disabled>Firmar la propuesta</button>
        </div>
        <div class="fb-firmado" hidden></div>
        <div class="fb-print"><span class="sline"></span><span>Nombre: {nombre}</span><span>C.C. / NIT: {documento}</span><span>Fecha:</span></div>
      </div>"""

FIRMA_HTML = f"""<p class="note">Cada parte firma en su recuadro: dibujando la firma o con un clic usando su nombre. La firma queda registrada en el servidor con la fecha, la hora y la huella digital de esta versión de la propuesta.</p>
    <div class="firmas">
      {caja("avm", "Por AVM Operadores Mayoristas")}
      {caja("desarrollador", "Por el desarrollador", "Santiago Martínez", "1.076.623.993")}
    </div>
    <div class="fb-descarga" hidden>
      <b>Propuesta firmada por ambas partes</b>
      <p>Descarga tu copia: un solo archivo con la propuesta, las dos firmas y su registro, que se abre en cualquier navegador sin conexión.</p>
      <div class="acc"><button type="button" class="pri" data-accion="html">Descargar la propuesta firmada</button><button type="button" data-accion="pdf">Guardar en PDF</button></div>
    </div>
    <script>
""" + r"""(() => {
  "use strict";
  const API = "firmar.php";
  const cajas = [...document.querySelectorAll(".fbox[data-parte]")];
  const fmt = iso => new Date(iso).toLocaleString("es-CO", { dateStyle: "long", timeStyle: "short" });
  const esc = s => String(s).replace(/[&<>"]/g, c => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c]));
  let huellaActual = "";

  function mostrarFirmada(caja, f) {
    caja.classList.add("firmado");
    caja.querySelector(".fb-estado").textContent = "Firmada";
    caja.querySelector(".fb-pad").hidden = true;
    caja.querySelector(".fb-aviso").hidden = true;
    const cambio = huellaActual && f.huella && f.huella !== huellaActual;
    const el = caja.querySelector(".fb-firmado"); el.hidden = false;
    el.innerHTML = `<div class="fb-img"><img alt="Firma de ${esc(f.nombre)}" src="${esc(f.imagen)}"></div>
      <div class="fb-datos"><span>Nombre: <b>${esc(f.nombre)}</b></span><span>C.C. / NIT: <b>${esc(f.documento)}</b></span>
      <span>Firmó: <b>${esc(fmt(f.fecha))}</b>${f.metodo === "nombre" ? " · con su nombre" : ""}</span>
      <span>Huella de la versión firmada: <code>${esc(f.huella || "")}</code></span>
      ${cambio ? '<span class="fb-cambio">La propuesta cambió después de esta firma: no es la misma versión.</span>' : ""}</div>`;
    revisarDescarga();
  }

  // Descarga: solo cuando ambas partes firmaron
  const descarga = document.querySelector(".fb-descarga");
  function revisarDescarga() { if (descarga) descarga.hidden = !cajas.every(c => c.classList.contains("firmado")); }
  function descargarHTML() {
    const copia = document.documentElement.cloneNode(true);
    copia.querySelectorAll("script, .fb-pad, .fb-aviso, .fb-descarga").forEach(n => n.remove());
    copia.querySelectorAll("[hidden]").forEach(n => { if (n.closest(".fbox")) n.remove(); });
    const nota = document.createElement("p"); nota.className = "note";
    nota.textContent = "Copia descargada el " + new Date().toLocaleString("es-CO", { dateStyle: "long", timeStyle: "short" }) + " desde " + location.href.split("#")[0] + ".";
    const f = copia.querySelector("#firma"); if (f) f.appendChild(nota);
    const html = "<!doctype html>\n" + copia.outerHTML;
    const a = document.createElement("a");
    a.href = URL.createObjectURL(new Blob([html], { type: "text/html;charset=utf-8" }));
    a.download = "Propuesta Plataforma AVM - firmada.html";
    document.body.appendChild(a); a.click(); a.remove(); setTimeout(() => URL.revokeObjectURL(a.href), 2000);
  }
  if (descarga) descarga.addEventListener("click", e => {
    const b = e.target.closest("[data-accion]"); if (!b) return;
    if (b.dataset.accion === "html") descargarHTML(); else window.print();
  });

  function preparar(caja, pideCodigo) {
    const pad = caja.querySelector(".fb-pad"); pad.hidden = false;
    caja.querySelector(".fb-cod").hidden = !pideCodigo;
    const cv = caja.querySelector("canvas"), ctx = cv.getContext("2d");
    const nombre = caja.querySelector('[name="nombre"]'), doc = caja.querySelector('[name="documento"]'), cod = caja.querySelector('[name="codigo"]');
    const acepto = caja.querySelector('[name="acepto"]'), btn = caja.querySelector(".fb-btn"), err = caja.querySelector(".fb-err"), ph = caja.querySelector(".ph");
    let modo = "dibujo", trazos = 0, dibujando = false;

    function tamano() {
      const r = cv.getBoundingClientRect(), dpr = window.devicePixelRatio || 1;
      cv.width = Math.round(r.width * dpr); cv.height = Math.round(r.height * dpr);
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0); limpiar();
    }
    function limpiar() { ctx.clearRect(0, 0, cv.width, cv.height); trazos = 0; if (modo === "nombre") escribirNombre(); revisar(); }
    async function escribirNombre() {
      const r = cv.getBoundingClientRect();
      try { await document.fonts.load('48px "Great Vibes"'); } catch (e) {}
      ctx.clearRect(0, 0, cv.width, cv.height);
      const t = nombre.value.trim(); if (!t) { revisar(); return; }
      let size = 52; ctx.font = `${size}px "Great Vibes", cursive`;
      while (ctx.measureText(t).width > r.width - 40 && size > 22) { size -= 2; ctx.font = `${size}px "Great Vibes", cursive`; }
      ctx.fillStyle = "#1E2410"; ctx.textAlign = "center"; ctx.textBaseline = "alphabetic";
      ctx.fillText(t, r.width / 2, r.height - 48); trazos = 1; revisar();
    }
    function revisar() {
      ph.textContent = modo === "dibujo" ? "Dibuja tu firma con el mouse o el dedo" : "Tu firma se genera con tu nombre";
      btn.disabled = !(trazos > 0 && nombre.value.trim().length >= 3 && doc.value.trim().length >= 3 && acepto.checked && (!pideCodigo || cod.value.trim()));
    }
    const pos = e => { const r = cv.getBoundingClientRect(); return [e.clientX - r.left, e.clientY - r.top]; };
    cv.addEventListener("pointerdown", e => { if (modo !== "dibujo") return; dibujando = true; cv.setPointerCapture(e.pointerId); const [x, y] = pos(e); ctx.beginPath(); ctx.moveTo(x, y); });
    cv.addEventListener("pointermove", e => { if (!dibujando) return; const [x, y] = pos(e); ctx.lineTo(x, y); ctx.strokeStyle = "#1E2410"; ctx.lineWidth = 2.4; ctx.lineCap = "round"; ctx.lineJoin = "round"; ctx.stroke(); trazos++; });
    cv.addEventListener("pointerup", () => { dibujando = false; revisar(); });
    cv.addEventListener("pointercancel", () => { dibujando = false; });
    caja.querySelector(".fb-limpiar").addEventListener("click", limpiar);
    caja.querySelectorAll(".fb-tabs button").forEach(b => b.addEventListener("click", () => {
      modo = b.dataset.m; caja.querySelectorAll(".fb-tabs button").forEach(x => x.setAttribute("aria-pressed", x === b));
      caja.querySelector(".fb-limpiar").hidden = modo === "nombre"; cv.style.cursor = modo === "dibujo" ? "crosshair" : "default"; limpiar();
    }));
    nombre.addEventListener("input", () => { if (modo === "nombre") escribirNombre(); else revisar(); });
    [doc, cod, acepto].forEach(x => x.addEventListener("input", revisar));
    acepto.addEventListener("change", revisar);
    window.addEventListener("resize", () => { if (!pad.hidden) tamano(); });
    tamano();

    btn.addEventListener("click", async () => {
      btn.disabled = true; err.hidden = true; btn.textContent = "Firmando…";
      try {
        const r = await fetch(API, { method: "POST", headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ parte: caja.dataset.parte, nombre: nombre.value, documento: doc.value, codigo: cod.value, acepto: acepto.checked, metodo: modo, imagen: cv.toDataURL("image/png") }) });
        const j = await r.json().catch(() => ({}));
        if (r.ok && j.ok) mostrarFirmada(caja, j.firma);
        else { err.textContent = j.error || "No se pudo firmar (" + r.status + ")."; err.hidden = false; }
      } catch (e) { err.textContent = "Sin conexión con el servidor."; err.hidden = false; }
      btn.textContent = "Firmar la propuesta"; revisar();
    });
  }

  async function iniciar() {
    let datos = null;
    if (location.protocol.startsWith("http")) {
      try { const r = await fetch(API + "?" + Date.now(), { cache: "no-store" }); if (r.ok) datos = await r.json(); } catch (e) {}
    }
    if (!datos || !datos.ok) {
      cajas.forEach(c => { const a = c.querySelector(".fb-aviso"); a.hidden = false; a.textContent = "La firma está disponible cuando la propuesta está publicada en el servidor de AVM."; });
      return;
    }
    huellaActual = datos.huella_actual || "";
    cajas.forEach(c => { const f = datos.firmas[c.dataset.parte]; if (f) mostrarFirmada(c, f); else preparar(c, !!datos.codigo[c.dataset.parte]); });
  }
  iniciar();
})();
</script>"""
