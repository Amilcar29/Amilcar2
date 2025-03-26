<?php
/**
 * Archivo de funciones auxiliares (helpers) para toda la aplicación
 */

/**
 * Sanitiza la entrada para prevenir inyecciones
 * 
 * @param string $input Texto a sanitizar
 * @return string Texto sanitizado
 */
function sanitizeInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

/**
 * Genera una URL relativa a la base del sistema
 * 
 * @param string $path Ruta relativa
 * @return string URL completa
 */
function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Formatea una fecha para mostrar
 * 
 * @param string $date Fecha en formato Y-m-d
 * @param string $format Formato deseado
 * @return string Fecha formateada
 */
function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

/**
 * Trunca un texto a la longitud especificada
 * 
 * @param string $text Texto a truncar
 * @param int $length Longitud máxima
 * @param string $append Texto a añadir al final
 * @return string Texto truncado
 */
function truncateText($text, $length = 100, $append = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $text = substr($text, 0, $length);
    $text = substr($text, 0, strrpos($text, ' '));
    
    return $text . $append;
}

/**
 * Genera un token aleatorio seguro
 * 
 * @param int $length Longitud del token
 * @return string Token generado
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Verifica si una cadena es una fecha válida
 * 
 * @param string $date Fecha a verificar
 * @param string $format Formato esperado
 * @return bool True si es una fecha válida
 */
function isValidDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Calcula la diferencia entre dos fechas en días
 * 
 * @param string $date1 Primera fecha
 * @param string $date2 Segunda fecha
 * @return int Número de días de diferencia
 */
function dateDiff($date1, $date2) {
    $datetime1 = new DateTime($date1);
    $datetime2 = new DateTime($date2);
    $interval = $datetime1->diff($datetime2);
    
    return $interval->days;
}

/**
 * Determina si una fecha ya pasó
 * 
 * @param string $date Fecha a verificar
 * @return bool True si la fecha ya pasó
 */
function isDatePassed($date) {
    $today = new DateTime();
    $checkDate = new DateTime($date);
    
    return $today > $checkDate;
}

/**
 * Devuelve el nombre del mes en español
 * 
 * @param int $month Número del mes (1-12)
 * @return string Nombre del mes
 */
function getMonthName($month) {
    $months = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre'
    ];
    
    return $months[$month] ?? '';
}

/**
 * Formatea un número a formato de moneda
 * 
 * @param float $amount Cantidad a formatear
 * @param string $symbol Símbolo de moneda
 * @return string Cantidad formateada
 */
function formatCurrency($amount, $symbol = '$') {
    return $symbol . ' ' . number_format($amount, 2, '.', ',');
}

/**
 * Comprueba si el usuario actual tiene un rol específico
 * 
 * @param int|array $roles Rol o array de roles a verificar
 * @return bool True si tiene alguno de los roles
 */
function hasRole($roles) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    
    if (is_array($roles)) {
        return in_array($_SESSION['user_role'], $roles);
    }
    
    return $_SESSION['user_role'] == $roles;
}

/**
 * Redirecciona a una URL
 * 
 * @param string $url URL a redireccionar
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Obtiene el valor de un parámetro GET o POST
 * 
 * @param string $key Nombre del parámetro
 * @param mixed $default Valor por defecto
 * @return mixed Valor del parámetro
 */
function input($key, $default = null) {
    if (isset($_POST[$key])) {
        return sanitizeInput($_POST[$key]);
    }
    
    if (isset($_GET[$key])) {
        return sanitizeInput($_GET[$key]);
    }
    
    return $default;
}