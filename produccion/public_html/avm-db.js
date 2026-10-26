/* Conector de la página con el servidor de AVM (api/db.php).
   Ofrece la misma interfaz de base de datos que usaba el prototipo —doc, collection, onSnapshot,
   set, update, delete, acquire— y la de usuario —isOwner, canEdit, can, id—, así la página
   no cambia. Mantiene una copia local de los documentos y la sincroniza cada pocos segundos. */
(() => {
  const API = "api/db.php";
  const cache = new Map();          // ruta → datos
  const subs = new Set();           // { kind: "doc" | "coll", path, fn }
  let me = { id: null, admin: false }, since = 0, ready = null, timer = null;
  const clone = o => o == null ? o : JSON.parse(JSON.stringify(o));
  const err = code => Object.assign(new Error(code), { code });

  async function sync() {
    const r = await fetch(`${API}?op=sync&since=${since}`, { credentials: "same-origin", cache: "no-store" });
    if (!r.ok) throw err(r.status === 503 ? "no_db" : "unavailable");
    const j = await r.json();
    me = j.me || me;
    const changed = new Set();
    for (const d of j.docs || []) { if (d.data == null) cache.delete(d.path); else cache.set(d.path, d.data); changed.add(d.path); }
    since = j.now || since;
    if (changed.size) notify(changed);
  }
  const parent = p => p.split("/").slice(0, -1).join("/");
  function notify(paths) {
    for (const s of subs) {
      const hit = s.kind === "doc" ? (!paths || paths.has(s.path)) : (!paths || [...paths].some(p => parent(p) === s.path));
      if (hit) try { s.fn(); } catch (e) { console.warn(e); }
    }
  }
  function schedule() {
    clearTimeout(timer);
    timer = setTimeout(async () => { if (!document.hidden) { try { await sync(); } catch {} } schedule(); }, me.admin ? 5000 : 30000);
  }
  document.addEventListener("visibilitychange", () => { if (!document.hidden && ready) sync().catch(() => {}); });

  async function post(body) {
    const r = await fetch(API, { method: "POST", credentials: "same-origin", headers: { "Content-Type": "application/json", "X-AVM": "1" }, body: JSON.stringify(body) });
    const j = await r.json().catch(() => ({}));
    if (r.status === 401 || r.status === 403) throw err("permission_denied");
    if (r.status === 404) throw err("invalid_argument");
    if (!r.ok) throw err(j.error || "unavailable");
    return j;
  }
  const local = (path, data) => { if (data == null) cache.delete(path); else cache.set(path, clone(data)); notify(new Set([path])); };
  const rid = () => Array.from(crypto.getRandomValues(new Uint8Array(10)), b => b.toString(16).padStart(2, "0")).join("");

  const docSnap = path => { const d = cache.get(path); return { id: path.split("/").pop(), exists: d !== undefined, data: () => clone(d), metadata: {} }; };
  const docRef = path => ({
    id: path.split("/").pop(), path,
    get: async () => { await ready; return docSnap(path); },
    set: async data => { await post({ op: "set", path, data }); local(path, data); },
    update: async data => { await post({ op: "update", path, data }); local(path, { ...(cache.get(path) || {}), ...data }); },
    delete: async () => { await post({ op: "delete", path }); local(path, null); },
    acquire: async (o = {}) => post({ op: "acquire", path, holder: o.holder, ttlMs: o.ttlMs }),
    onSnapshot(next, onErr) {
      const s = { kind: "doc", path, fn: () => next(docSnap(path)) };
      subs.add(s); ready.then(s.fn, e => onErr?.(e));
      return () => subs.delete(s);
    },
    collection: name => collRef(path + "/" + name)
  });
  const query = (path, ord) => {
    let docs = [...cache.keys()].filter(k => parent(k) === path).map(docSnap);
    if (ord) docs.sort((a, b) => { const x = a.data()?.[ord[0]], y = b.data()?.[ord[0]]; return (x > y ? 1 : x < y ? -1 : 0) * (ord[1] === "desc" ? -1 : 1); });
    return { docs, size: docs.length, empty: !docs.length, docChanges: () => [], metadata: {} };
  };
  const collRef = (path, ord) => ({
    path,
    doc: id => docRef(path + "/" + (id || rid())),
    orderBy: (f, dir) => collRef(path, [f, dir || "asc"]),
    limit: () => collRef(path, ord), where: () => collRef(path, ord),
    get: async () => { await ready; return query(path, ord); },
    onSnapshot(next, onErr) {
      const s = { kind: "coll", path, fn: () => next(query(path, ord)) };
      subs.add(s); ready.then(s.fn, e => onErr?.(e));
      return () => subs.delete(s);
    }
  });

  const db = { doc: docRef, collection: collRef };
  const user = {
    isOwner: async () => { await ready; return me.admin; },
    canEdit: async () => { await ready; return me.admin; },
    can: async () => { await ready; return me.admin; },
    id: async () => { await ready; return me.id; },
    me: async () => { await ready; return { ...me }; }
  };
  ready = sync().finally(schedule);

  // Las automatizaciones corren en el servidor (api/db.php y el cron), no en el navegador.
  window.AVM_SERVER_ENGINE = true;
  window.claude = { use: async name => { await ready.catch(() => {}); return name === "db" ? db : name === "user" ? user : null; } };

  // El backoffice exige sesión de administrador.
  const guard = () => { if (location.hash === "#admin") ready.then(() => { if (!me.admin) location.replace("ingresar.php"); }); };
  addEventListener("hashchange", guard); guard();
})();
