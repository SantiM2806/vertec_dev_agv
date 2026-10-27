<?php
/**
 * Configuración de la plataforma (todas las agencias).
 * 1. Copia este archivo como avm-config.php en la carpeta que está POR ENCIMA de public_html.
 *    Así nadie lo puede abrir desde el navegador.
 * 2. Llena los valores. No compartas este archivo por chat ni correo.
 * Las llaves de cada agencia (LiteAPI, Resend propio…) NO van aquí: se ponen en plataforma.php y quedan cifradas.
 */
return [
    // Base de datos MySQL o MariaDB
    'db_host' => 'localhost',
    'db_name' => '',
    'db_user' => '',
    'db_pass' => '',

    // Frase larga y aleatoria. Cifra las llaves de las agencias: si la cambias, hay que volver a escribirlas.
    'app_secret' => '',

    // Nombre de tu plataforma (aparece en el panel de la plataforma y en la portada sin agencia)
    'platform_name' => 'Vertec Travel',
    // Dominio base: cada agencia queda en <subdominio>.<base_domain>, p. ej. 'tuplataforma.com' o '129.80.1.2.sslip.io'
    'base_domain' => '',
    // Agencia que se abre en la dirección principal (sin subdominio). Vacío: si hay una sola agencia, se abre esa.
    'agencia_principal' => '',
    // 'https' (normal) o 'http' (solo para pruebas sin certificado)
    'base_http' => 'https',
    // Dirección pública de la plataforma, sin / al final (para la portada sin agencia)
    'site_url' => '',

    // Correo de la plataforma con Resend (resend.com → API Keys). Las agencias sin Resend propio envían con este,
    // con su nombre como remitente. Debe ser de un dominio verificado en Resend.
    'resend_key' => '',
    'mail_from' => '',          // p. ej. 'notificaciones@tuplataforma.com'
    'mail_daily_limit' => 100,  // correos por día de tu plan de Resend, por agencia

    // LiteAPI de la plataforma: solo la usan las agencias marcadas como demo en plataforma.php.
    'liteapi_key' => '',
    // Solo para pruebas en local con el simulador (herramientas/mock-liteapi.js):
    // 'liteapi_base' => 'http://127.0.0.1:8771/v3.0',  y  'liteapi_key' => 'clave-de-prueba-local',

    // Solo en local: agencia que se abre en http://localhost (también sirve http://<subdominio>.localhost)
    'dev_tenant' => '',
];
