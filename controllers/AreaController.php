<?php
/**
 * AreaController - Controlador para la gestión de áreas
 */
class AreaController {
    private $db;
    private $area;
    private $user;
    
    public function __construct() {
        require_once 'config/database.php';
        require_once 'models/Area.php';
        require_once 'models/User.php';
        require_once 'utils/AccessControl.php';
        
        $this->db = new Database();
        $this->area = new Area($this->db);
        $this->user = new User($this->db);
    }
    
    /**
     * Muestra la lista de áreas
     */
    public function index() {
        // Verifica permisos: solo Administradores y Gerentes Generales
        if ($_SESSION['user_role'] > 2) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Obtiene todas las áreas
        $areas = $this->area->getAllAreas();
        
        // Carga la vista
        include 'views/areas/index.php';
    }
    
    /**
     * Muestra el formulario para crear una nueva área
     */
    public function create() {
        // Verifica permisos: solo Administradores y Gerentes Generales
        if ($_SESSION['user_role'] > 2) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Obtiene usuarios con rol de Gerente de Área para asignar
        $gerentes = $this->user->getUsersByRole(3); // 3 = Gerente de Área
        
        // Carga la vista del formulario
        include 'views/areas/create.php';
    }
    
    /**
     * Guarda una nueva área en la base de datos
     */
    public function store() {
        // Verifica permisos: solo Administradores y Gerentes Generales
        if ($_SESSION['user_role'] > 2) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Verifica que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=area&action=create');
            exit;
        }
        
        // Verifica campos obligatorios
        if (empty($_POST['nombre'])) {
            $_SESSION['error'] = 'El nombre del área es obligatorio';
            header('Location: index.php?controller=area&action=create');
            exit;
        }
        
        // Prepara los datos para crear el área
        $areaData = [
            'nombre' => htmlspecialchars($_POST['nombre']),
            'descripcion' => !empty($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : null,
            'gerente_id' => !empty($_POST['gerente_id']) ? (int)$_POST['gerente_id'] : null
        ];
        
        // Crea el área en la base de datos
        $areaId = $this->area->createArea($areaData);
        
        if ($areaId) {
            $_SESSION['success'] = 'Área creada correctamente';
            header('Location: index.php?controller=area&action=index');
        } else {
            $_SESSION['error'] = 'Error al crear el área. Intente nuevamente.';
            header('Location: index.php?controller=area&action=create');
        }
        
        exit;
    }
    
    /**
     * Muestra los detalles de un área específica
     */
    public function view() {
        // Verifica permisos: solo Administradores y Gerentes Generales
        if ($_SESSION['user_role'] > 2) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Verifica que se proporcione un ID
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        $areaId = (int)$_GET['id'];
        $area = $this->area->getAreaById($areaId);
        
        // Verifica que el área exista
        if (!$area) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Obtiene departamentos del área
        require_once 'models/Department.php';
        $department = new Department($this->db);
        $departments = $department->getDepartmentsByArea($areaId);
        
        // Obtiene usuarios del área
        $users = $this->area->getAreaUsers($areaId);
        
        // Carga la vista de detalle
        include 'views/areas/view.php';
    }
    
    /**
     * Muestra el formulario para editar un área
     */
    public function edit() {
        // Verifica permisos: solo Administradores y Gerentes Generales
        if ($_SESSION['user_role'] > 2) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Verifica que se proporcione un ID
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        $areaId = (int)$_GET['id'];
        $area = $this->area->getAreaById($areaId);
        
        // Verifica que el área exista
        if (!$area) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Obtiene usuarios con rol de Gerente de Área para asignar
        $gerentes = $this->user->getUsersByRole(3); // 3 = Gerente de Área
        
        // Carga la vista del formulario
        include 'views/areas/edit.php';
    }
    
    /**
     * Actualiza un área existente
     */
    public function update() {
        // Verifica permisos: solo Administradores y Gerentes Generales
        if ($_SESSION['user_role'] > 2) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Verifica que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=area&action=index');
            exit;
        }
        
        // Verifica que se proporcione un ID
        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        $areaId = (int)$_POST['id'];
        $area = $this->area->getAreaById($areaId);
        
        // Verifica que el área exista
        if (!$area) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Verifica campos obligatorios
        if (empty($_POST['nombre'])) {
            $_SESSION['error'] = 'El nombre del área es obligatorio';
            header('Location: index.php?controller=area&action=edit&id=' . $areaId);
            exit;
        }
        
        // Prepara los datos para actualizar el área
        $areaData = [
            'nombre' => htmlspecialchars($_POST['nombre']),
            'descripcion' => !empty($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : null,
            'gerente_id' => !empty($_POST['gerente_id']) ? (int)$_POST['gerente_id'] : null
        ];
        
        // Actualiza el área en la base de datos
        $updated = $this->area->updateArea($areaId, $areaData);
        
        if ($updated) {
            $_SESSION['success'] = 'Área actualizada correctamente';
            header('Location: index.php?controller=area&action=view&id=' . $areaId);
        } else {
            $_SESSION['error'] = 'Error al actualizar el área. Intente nuevamente.';
            header('Location: index.php?controller=area&action=edit&id=' . $areaId);
        }
        
        exit;
    }
    
    /**
     * Elimina un área
     */
    public function delete() {
        // Verifica permisos: solo Administradores y Gerentes Generales
        if ($_SESSION['user_role'] > 2) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Verifica que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=area&action=index');
            exit;
        }
        
        // Verifica que se proporcione un ID
        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        $areaId = (int)$_POST['id'];
        $area = $this->area->getAreaById($areaId);
        
        // Verifica que el área exista
        if (!$area) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Intenta eliminar el área
        $deleted = $this->area->deleteArea($areaId);
        
        if ($deleted) {
            $_SESSION['success'] = 'Área eliminada correctamente';
            header('Location: index.php?controller=area&action=index');
        } else {
            $_SESSION['error'] = 'No se puede eliminar el área porque tiene departamentos o proyectos asociados';
            header('Location: index.php?controller=area&action=view&id=' . $areaId);
        }
        
        exit;
    }
}