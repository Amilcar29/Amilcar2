<?php
/**
 * ErrorController - Controlador para la gestión de errores
 */
class ErrorController {
    
    /**
     * Muestra la página de error 404 (No encontrado)
     */
    public function not_found() {
        http_response_code(404);
        include 'views/errors/404.php';
    }
    
    /**
     * Muestra la página de error 403 (Acceso prohibido)
     */
    public function forbidden() {
        http_response_code(403);
        include 'views/errors/403.php';
    }
    
    /**
     * Muestra la página de error 500 (Error del servidor)
     */
    public function server_error() {
        http_response_code(500);
        include 'views/errors/500.php';
    }
    
    /**
     * Muestra una página de error general
     */
    public function general_error() {
        include 'views/errors/general.php';
    }
}