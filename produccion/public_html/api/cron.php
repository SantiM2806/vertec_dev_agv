<?php
/**
 * Tarea programada (cron) del sitio AVM. Solo se ejecuta por consola (no desde el navegador).
 * En cPanel → Trabajos de cron, cada 5 minutos:
 *   php /home/<tu-usuario>/public_html/api/cron.php
 * Procesa solicitudes que hayan quedado pendientes y envía (o reintenta) los correos con Resend.
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/_lib.php';

$extra = [];
try { $extra[] = avm_update_trm(); } catch (Throwable $e) { $extra[] = 'TRM: error'; }
try { $extra[] = avm_backup(); } catch (Throwable $e) { $extra[] = 'copia: error ' . $e->getMessage(); }
$n = avm_run_engine();
$r = avm_send_outbox(50);
try { $extra[] = avm_campanas_avance(); } catch (Throwable $e) { $extra[] = 'campañas: error'; }
echo date('c') . " solicitudes procesadas: $n · correos enviados: {$r['enviados']} · errores: {$r['errores']}" . (isset($r['nota']) ? " · {$r['nota']}" : '') . ' · ' . implode(' · ', $extra) . PHP_EOL;
