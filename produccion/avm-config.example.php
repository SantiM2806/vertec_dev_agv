<?php
/**
 * Configuración del sitio AVM.
 * 1. Copia este archivo como avm-config.php en la carpeta que está POR ENCIMA de public_html
 *    (en Dongee: /home/<tu-usuario>/avm-config.php). Así nadie lo puede abrir desde el navegador.
 * 2. Llena los valores. No compartas este archivo por chat ni correo.
 */
return [
    // Base de datos MySQL (cPanel → Bases de datos MySQL). Dongee suele usar "localhost".
    'db_host' => 'localhost',
    'db_name' => '',   // p. ej. usuario_avm
    'db_user' => '',   // p. ej. usuario_avmweb
    'db_pass' => '',

    // Dirección pública del sitio, sin / al final (se usa en los avisos que envía el cron)
    'site_url' => 'https://demo.avolarporelmundo.co',

    // LiteAPI: hoteles del buscador (panel de LiteAPI → API Keys, llave de producción).
    'liteapi_key' => '',
    // Solo para pruebas en local con el simulador (herramientas/mock-liteapi.js):
    // 'liteapi_base' => 'http://127.0.0.1:8771/v3.0',  y  'liteapi_key' => 'clave-de-prueba-local',

    // Resend: correos automáticos (resend.com → API Keys).
    'resend_key' => '',
    // Remitente con el dominio verificado en Resend, p. ej. 'AVM Operadores Mayoristas <reservas@avolarporelmundo.co>'
    'mail_from' => '',
    // A dónde llegan las respuestas de los clientes, p. ej. 'comercial@avolarporelmundo.co'
    'mail_reply_to' => '',
    // Correos por día de tu plan de Resend (gratuito: 100). Las campañas dejan 20 de margen para las cotizaciones.
    'mail_daily_limit' => 100,
];
