<?php
/**
 * DepartmentController - Controlador para la gestión de departamentos
 */
class DepartmentController {
    private $db;
    private $department;
    private $area;
    private $user;
    
    public function __construct() {
        require_once 'config/database.php';
        require_once 'models/Department.php';
        require_once 'models/Area.php';
        require_once 'models/User.php';
        require_once 'utils/AccessControl.php';
        
        $this->db = new Database();
        $this->department = new Department($this->db);
        $this->area = new Area($this->db);
        $this->user = new User($this->db);
    }
    
    /**
     * Muestra la lista de departamentos
     */
    public function index() {
        // Verifica permisos: solo Administradores y Gerentes Generales
        if ($_SESSION['user_role'] > 2) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Obtiene parámetros de filtrado
        $areaId = isset($_GET['area_id']) ? (int)$_GET['area_id'] : null;
        
        // Obtiene departamentos según filtros
        if ($areaId) {
            $departments = $this->department->getDepartmentsByArea($areaId);
        } else {
            $departments = $this->department->getAllDepartments();
        }
        
        // Obtiene áreas para filtros
        $areas = $this->area->getAllAreas();
        
        // Carga la vista
        include 'views/departments/index.php';
    }
    
    /**
     * Muestra el formulario para crear un nuevo departamento
     */
    public function create() {
        // Verifica permisos: solo Administradores y Gerentes Generales
        if ($_SESSION['user_role'] > 2) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Obtiene áreas para el formulario
        $areas = $this->area->getAllAreas();
        
        // Obtiene usuarios con rol de Jefe de Departamento para asignar
        $jefes = $this->user->getUsersByRole(4); // 4 = Jefe de Departamento
        
        // Carga la vista del formulario
        include 'views/departments/create.php';
    }
    
    /**
     * Guarda un nuevo departamento en la base de datos
     */
    public function store() {
        // Verifica permisos: solo Administradores y Gerentes Generales
        if ($_SESSION['user_role'] > 2) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Verifica que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=department&action=create');
            exit;
        }
        
        // Verifica campos obligatorios
        if (empty($_POST['nombre']) || empty($_POST['area_id'])) {
            $_SESSION['error'] = 'El nombre y el área son obligatorios';
            header('Location: index.php?controller=department&action=create');
            exit;
        }
        
        // Prepara los datos para crear el departamento
        $departmentData = [
            'nombre' => htmlspecialchars($_POST['nombre']),
            'descripcion' => !empty($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : null,
            'area_id' => (int)$_POST['area_id'],
            'jefe_id' => !empty($_POST['jefe_id']) ? (int)$_POST['jefe_id'] : null
        ];
        
        // Crea el departamento en la base de datos
        $departmentId = $this->department->createDepartment($departmentData);
        
        if ($departmentId) {
            $_SESSION['success'] = 'Departamento creado correctamente';
            header('Location: index.php?controller=department&action=index');
        } else {
            $_SESSION['error'] = 'Error al crear el departamento. Intente nuevamente.';
            header('Location: index.php?controller=department&action=create');
        }
        
        exit;
    }
    
    /**
     * Muestra los detalles de un departamento específico
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
        
        $departmentId = (int)$_GET['id'];
        $department = $this->department->getDepartmentById($departmentId);
        
        // Verifica que el departamento exista
        if (!$department) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Obtiene usuarios del departamento
        $users = $this->department->getDepartmentUsers($departmentId);
        
        // Obtiene estadísticas
        $stats = $this->department->getDepartmentStats($departmentId);
        
        // Carga la vista de detalle
        include 'views/departments/view.php';
    }
    
    /**
     * Muestra el formulario para editar un departamento
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
        
        $departmentId = (int)$_GET['id'];
        $department = $this->department->getDepartmentById($departmentId);
        
        // Verifica que el departamento exista
        if (!$department) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Obtiene áreas para el formulario
        $areas = $this->area->getAllAreas();
        
        // Obtiene usuarios con rol de Jefe de Departamento para asignar
        $jefes = $this->user->getUsersByRole(4); // 4 = Jefe de Departamento
        
        // Carga la vista del formulario
        include 'views/departments/edit.php';
    }
    
    /**
     * Actualiza un departamento existente
     */
    public function update() {
        // Verifica permisos: solo Administradores y Gerentes Generales
        if ($_SESSION['user_role'] > 2) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Verifica que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=department&action=index');
            exit;
        }
        
        // Verifica que se proporcione un ID
        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        $departmentId = (int)$_POST['id'];
        $department = $this->department->getDepartmentById($departmentId);
        
        // Verifica que el departamento exista
        if (!$department) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Verifica campos obligatorios
        if (empty($_POST['nombre']) || empty($_POST['area_id'])) {
            $_SESSION['error'] = 'El nombre y el área son obligatorios';
            header('Location: index.php?controller=department&action=edit&id=' . $departmentId);
            exit;
        }
        
        // Prepara los datos para actualizar el departamento
        $departmentData = [
            'nombre' => htmlspecialchars($_POST['nombre']),
            'descripcion' => !empty($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : null,
            'area_id' => (int)$_POST['area_id'],
            'jefe_id' => !empty($_POST['jefe_id']) ? (int)$_POST['jefe_id'] : null
        ];
        
        // Actualiza el departamento en la base de datos
        $updated = $this->department->updateDepartment($departmentId, $departmentData);
        
        if ($updated) {
            $_SESSION['success'] = 'Departamento actualizado correctamente';
            header('Location: index.php?controller=department&action=view&id=' . $departmentId);
        } else {
            $_SESSION['error'] = 'Error al actualizar el departamento. Intente nuevamente.';
            header('Location: index.php?controller=department&action=edit&id=' . $departmentId);
        }
        
        exit;
    }
    
    /**
     * Elimina un departamento
     */
    public function delete() {
        // Verifica permisos: solo Administradores y Gerentes Generales
        if ($_SESSION['user_role'] > 2) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Verifica que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=department&action=index');
            exit;
        }
        
        // Verifica que se proporcione un ID
        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        $departmentId = (int)$_POST['id'];
        $department = $this->department->getDepartmentById($departmentId);
        
        // Verifica que el departamento exista
        if (!$department) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Intenta eliminar el departamento
        $deleted = $this->department->deleteDepartment($departmentId);
        
        if ($deleted) {
            $_SESSION['success'] = 'Departamento eliminado correctamente';
            header('Location: index.php?controller=department&action=index');
        } else {
            $_SESSION['error'] = 'No se puede eliminar el departamento porque tiene usuarios asociados';
            header('Location: index.php?controller=department&action=view&id=' . $departmentId);
        }
        
        exit;
    }
}