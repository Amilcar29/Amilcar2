<?php
/**
 * Session - Clase para la gestión de sesiones
 */
class Session {
    /**
     * Inicializa la sesión con configuraciones seguras
     */
    public static function init() {
        if (session_status() == PHP_SESSION_NONE) {
            // Configurar cookies de sesión seguras
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path' => '/',
                'domain' => '',
                'secure' => false, // Cambiar a true si se usa HTTPS
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            session_start();
        }
    }
    
    /**
     * Establece un valor en la sesión
     * 
     * @param string $key Clave
     * @param mixed $value Valor
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Obtiene un valor de la sesión
     * 
     * @param string $key Clave
     * @param mixed $default Valor por defecto
     * @return mixed Valor almacenado o valor por defecto
     */
    public static function get($key, $default = null) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }
    
    /**
     * Verifica si una clave existe en la sesión
     * 
     * @param string $key Clave a verificar
     * @return bool True si existe
     */
    public static function has($key) {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Elimina un valor de la sesión
     * 
     * @param string $key Clave a eliminar
     */
    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    /**
     * Elimina todos los valores de la sesión
     */
    public static function clear() {
        session_unset();
    }
    
    /**
     * Destruye la sesión por completo
     */
    public static function destroy() {
        session_unset();
        session_destroy();
        
        // Elimina la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
    }
    
    /**
     * Regenera el ID de sesión
     * 
     * @param bool $deleteOldSession Eliminar sesión antigua
     */
    public static function regenerate($deleteOldSession = true) {
        session_regenerate_id($deleteOldSession);
    }
    
    /**
     * Establece un mensaje flash en la sesión
     * 
     * @param string $type Tipo de mensaje (success, error, warning, info)
     * @param string $message Mensaje
     */
    public static function setFlash($type, $message) {
        self::set('flash_' . $type, $message);
    }
    
    /**
     * Obtiene un mensaje flash y lo elimina
     * 
     * @param string $type Tipo de mensaje
     * @return string|null Mensaje o null si no existe
     */
    public static function getFlash($type) {
        $key = 'flash_' . $type;
        $message = self::get($key);
        self::remove($key);
        
        return $message;
    }
    
    /**
     * Verifica si hay un mensaje flash de determinado tipo
     * 
     * @param string $type Tipo de mensaje
     * @return bool True si existe
     */
    public static function hasFlash($type) {
        return self::has('flash_' . $type);
    }
}