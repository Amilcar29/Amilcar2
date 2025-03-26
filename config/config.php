<?php
/**
 * Archivo de configuración general de la aplicación
 * Contiene constantes, configuraciones y ajustes globales
 */

// Información básica de la aplicación
define('APP_NAME', 'Sistema de Gestión de Proyectos');
define('APP_VERSION', '1.0.0');

// Zona horaria
date_default_timezone_set('America/Mexico_City'); // Ajusta a tu zona horaria

// Rutas de directorios (para facilitar inclusiones)
define('ROOT_PATH', dirname(__FILE__, 2)); // Ruta raíz del proyecto
define('VIEWS_PATH', ROOT_PATH . '/views');
define('MODELS_PATH', ROOT_PATH . '/models');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// Configuración de sesión (solo si no hay sesión activa)
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1); // Ayuda a mitigar ataques XSS
    ini_set('session.use_only_cookies', 1); // Obliga a usar solo cookies para sesiones
    ini_set('session.cookie_secure', 0);    // Cambia a 1 si usas HTTPS
}

// Duración máxima de la sesión (en segundos)
define('SESSION_LIFETIME', 3600); // 1 hora
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
}

// URL base del sistema (ajusta según tu entorno)
define('BASE_URL', 'http://localhost/sistema_proyectos');

// Modo de depuración (desarrollo/producción)
define('DEBUG_MODE', true); // Cambia a false en producción

// Configuración de errores
if (DEBUG_MODE) {
    // Muestra todos los errores en modo de desarrollo
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // Oculta errores en producción
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    
    // Registra errores en archivo
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . '/logs/error.log');
}

// Configuración de paginación
define('ITEMS_PER_PAGE', 10);

// Otras configuraciones globales
define('UPLOAD_MAX_SIZE', 5242880); // 5MB en bytes
define('ALLOWED_EXTENSIONS', 'jpg,jpeg,png,pdf,doc,docx,xls,xlsx');