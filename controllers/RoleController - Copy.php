<?php
/**
 * RoleController - Controlador para la gestión de roles
 */
class RoleController {
    private $db;
    private $role;
    
    public function __construct() {
        require_once 'config/database.php';
        require_once 'models/Role.php';
        require_once 'utils/AccessControl.php';
        
        $this->db = new Database();
        $this->role = new Role($this->db);
    }
    
    /**
     * Muestra la lista de roles
     */
    public function index() {
        // Verifica permisos: solo Administradores
        if ($_SESSION['user_role'] > 1) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Obtiene todos los roles
        $roles = $this->role->getAllRoles();
        
        // Carga la vista
        include 'views/roles/index.php';
    }
    
    /**
     * Muestra el formulario para crear un nuevo rol
     */
    public function create() {
        // Verifica permisos: solo Administradores
        if ($_SESSION['user_role'] > 1) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Carga la vista del formulario
        include 'views/roles/create.php';
    }
    
    /**
     * Guarda un nuevo rol en la base de datos
     */
    public function store() {
        // Verifica permisos: solo Administradores
        if ($_SESSION['user_role'] > 1) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Verifica que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=role&action=create');
            exit;
        }
        
        // Verifica campos obligatorios
        if (empty($_POST['nombre'])) {
            $_SESSION['error'] = 'El nombre del rol es obligatorio';
            header('Location: index.php?controller=role&action=create');
            exit;
        }
        
        // Prepara los datos para crear el rol
        $roleData = [
            'nombre' => htmlspecialchars($_POST['nombre']),
            'descripcion' => !empty($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : null
        ];
        
        // Crea el rol en la base de datos
        $roleId = $this->role->createRole($roleData);
        
        if ($roleId) {
            $_SESSION['success'] = 'Rol creado correctamente';
            header('Location: index.php?controller=role&action=index');
        } else {
            $_SESSION['error'] = 'Error al crear el rol. Intente nuevamente.';
            header('Location: index.php?controller=role&action=create');
        }
        
        exit;
    }
    
    /**
     * Muestra el formulario para editar un rol
     */
    public function edit() {
        // Verifica permisos: solo Administradores
        if ($_SESSION['user_role'] > 1) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Verifica que se proporcione un ID
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        $roleId = (int)$_GET['id'];
        $role = $this->role->getRoleById($roleId);
        
        // Verifica que el rol exista
        if (!$role) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Carga la vista del formulario
        include 'views/roles/edit.php';
    }
    
    /**
     * Actualiza un rol existente
     */
    public function update() {
        // Verifica permisos: solo Administradores
        if ($_SESSION['user_role'] > 1) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Verifica que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=role&action=index');
            exit;
        }
        
        // Verifica que se proporcione un ID
        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        $roleId = (int)$_POST['id'];
        $role = $this->role->getRoleById($roleId);
        
        // Verifica que el rol exista
        if (!$role) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Verifica campos obligatorios
        if (empty($_POST['nombre'])) {
            $_SESSION['error'] = 'El nombre del rol es obligatorio';
            header('Location: index.php?controller=role&action=edit&id=' . $roleId);
            exit;
        }
        
        // Prepara los datos para actualizar el rol
        $roleData = [
            'nombre' => htmlspecialchars($_POST['nombre']),
            'descripcion' => !empty($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : null
        ];
        
        // Actualiza el rol en la base de datos
        $updated = $this->role->updateRole($roleId, $roleData);
        
        if ($updated) {
            $_SESSION['success'] = 'Rol actualizado correctamente';
            header('Location: index.php?controller=role&action=index');
        } else {
            $_SESSION['error'] = 'Error al actualizar el rol. Intente nuevamente.';
            header('Location: index.php?controller=role&action=edit&id=' . $roleId);
        }
        
        exit;
    }
    
    /**
     * Elimina un rol
     */
    public function delete() {
        // Verifica permisos: solo Administradores
        if ($_SESSION['user_role'] > 1) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Verifica que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=role&action=index');
            exit;
        }
        
        // Verifica que se proporcione un ID
        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        $roleId = (int)$_POST['id'];
        
        // No permitir eliminar roles del sistema (ids 1 a 5)
        if ($roleId <= 5) {
            $_SESSION['error'] = 'No se pueden eliminar los roles predefinidos del sistema';
            header('Location: index.php?controller=role&action=index');
            exit;
        }
        
        $role = $this->role->getRoleById($roleId);
        
        // Verifica que el rol exista
        if (!$role) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Intenta eliminar el rol
        $deleted = $this->role->deleteRole($roleId);
        
        if ($deleted) {
            $_SESSION['success'] = 'Rol eliminado correctamente';
            header('Location: index.php?controller=role&action=index');
        } else {
            $_SESSION['error'] = 'No se puede eliminar el rol porque hay usuarios asociados';
            header('Location: index.php?controller=role&action=index');
        }
        
        exit;
    }
}