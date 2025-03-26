<?php
/**
 * Punto de entrada principal para la aplicación de Seguimiento de Proyectos
 * Maneja todas las solicitudes y las enruta a los controladores adecuados
 */

// Inicializar sesión
session_start();

// Carga de configuración y utilidades
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'utils/helpers.php';
require_once 'utils/Session.php';

// Carga del sistema de control de acceso
require_once 'utils/AccessControl.php';

// Ruta por defecto si no se especifica controlador
$controller = isset($_GET['controller']) ? sanitizeInput($_GET['controller']) : 'dashboard';
$action = isset($_GET['action']) ? sanitizeInput($_GET['action']) : 'index';

// Verificar sesión para rutas protegidas
if ($controller != 'auth' && $controller != 'error') {
    if (!isset($_SESSION['user_id'])) {
        // Redireccionar al login si no hay sesión
        header('Location: index.php?controller=auth&action=login');
        exit;
    }
}

// Carga del controlador adecuado
switch ($controller) {
    case 'auth':
        require_once 'controllers/AuthController.php';
        $controllerInstance = new AuthController();
        break;
        
    case 'dashboard':
        require_once 'controllers/DashboardController.php';
        $controllerInstance = new DashboardController();
        break;
        
    case 'project':
        require_once 'controllers/ProjectController.php';
        $controllerInstance = new ProjectController();
        break;
        
    case 'task':
        require_once 'controllers/TaskController.php';
        $controllerInstance = new TaskController();
        break;
        
    case 'user':
        require_once 'controllers/UserController.php';
        $controllerInstance = new UserController();
        break;
        
    case 'area':
        require_once 'controllers/AreaController.php';
        $controllerInstance = new AreaController();
        break;
        
    case 'department':
        require_once 'controllers/DepartmentController.php';
        $controllerInstance = new DepartmentController();
        break;
        
    case 'notification':
        require_once 'controllers/NotificationController.php';
        $controllerInstance = new NotificationController();
        break;
        
    case 'report':
        require_once 'controllers/ReportController.php';
        $controllerInstance = new ReportController();
        break;
        
    case 'error':
        require_once 'controllers/ErrorController.php';
        $controllerInstance = new ErrorController();
        break;
	
	case 'role':
        require_once 'controllers/RoleController.php';
        $controllerInstance = new RoleController();
        break;
        
    default:
        // Si el controlador no existe, mostrar error 404
        header('Location: index.php?controller=error&action=not_found');
        exit;
	
}

// Verificar si el método existe
if (method_exists($controllerInstance, $action)) {
    // Ejecutar la acción del controlador
    $controllerInstance->$action();
} else {
    // Si la acción no existe, mostrar error 404
    header('Location: index.php?controller=error&action=not_found');
    exit;
}

// Nota: La función sanitizeInput() ahora se carga desde utils/helpers.php