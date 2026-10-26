"""Paquetes para subir a Dongee: dist/actualizacion-N.zip (archivos que cambian) y dist/sitio-avm.zip (sitio completo).
Uso: python herramientas/empaquetar.py 15"""
import zipfile, os, glob, sys
os.chdir(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
os.makedirs("dist", exist_ok=True)
zipname = "dist/actualizacion-" + (sys.argv[1] if len(sys.argv) > 1 else "nueva") + ".zip"
root = 'produccion/public_html'
files = ['index.html', 'index.php', 'sitemap.php', 'robots.php', '.htaccess', 'baja.php', 'api/_lib.php', 'api/_correo.php', 'api/_campanas.php', 'api/_liteapi.php', 'api/campana.php', 'api/catalogo.php', 'api/hoteles.php', 'api/cron.php', 'api/buscar.php']
files += ['img/correo/' + os.path.basename(f) for f in sorted(glob.glob(root + '/img/correo/*.jpg'))]
with zipfile.ZipFile(zipname, 'w', zipfile.ZIP_DEFLATED) as z:
    for f in files:
        z.write(os.path.join(root, f), f)
print(len(files), 'archivos', os.path.getsize(zipname) // 1024, 'KB')
with zipfile.ZipFile('dist/sitio-avm.zip', 'w', zipfile.ZIP_DEFLATED) as z:
    for r, _, fs in os.walk(root):
        for f in fs:
            p = os.path.join(r, f)
            z.write(p, os.path.relpath(p, root).replace(os.sep, '/'))
