<?php
/**
 * Tareas programadas de la plataforma. Solo se ejecuta por consola (no desde el navegador).
 * Cada 5 minutos (crontab del servidor o "Trabajos de cron" en cPanel):
 *   php /ruta/a/public_html/api/tareas.php
 * Para cada agencia activa: TRM, copia de seguridad, solicitudes pendientes, correos con Resend y campañas.
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/_nucleo.php';

foreach (avm_tenants() as $t) {
    avm_use_tenant($t);
    $extra = [];
    try { $extra[] = avm_update_trm(); } catch (Throwable $e) { $extra[] = 'TRM: error'; }
    try { $extra[] = avm_backup(); } catch (Throwable $e) { $extra[] = 'copia: error ' . $e->getMessage(); }
    try {
        $n = avm_run_engine();
        $r = avm_send_outbox(50);
    } catch (Throwable $e) { $n = 0; $r = ['enviados' => 0, 'errores' => 1, 'nota' => 'error: ' . $e->getMessage()]; }
    try { $extra[] = avm_campanas_avance(); } catch (Throwable $e) { $extra[] = 'campañas: error'; }
    echo date('c') . " [{$t['slug']}] solicitudes procesadas: $n · correos enviados: {$r['enviados']} · errores: {$r['errores']}" . (isset($r['nota']) ? " · {$r['nota']}" : '') . ' · ' . implode(' · ', $extra) . PHP_EOL;
}
avm_use_tenant(null);
