<?php
/**
 * UserController - Controlador para la gestión de usuarios
 */
class UserController {
    private $db;
    private $user;
    private $area;
    private $department;
    
    public function __construct() {
        require_once 'config/database.php';
        require_once 'models/User.php';
        require_once 'models/Area.php';
        require_once 'models/Department.php';
        require_once 'utils/AccessControl.php';
        
        $this->db = new Database();
        $this->user = new User($this->db);
        $this->area = new Area($this->db);
        $this->department = new Department($this->db);
    }
    
    /**
     * Muestra la lista de usuarios
     */
    public function index() {
        // Verifica permisos: solo Administradores y Gerentes Generales
        if ($_SESSION['user_role'] > 2) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Obtiene parámetros de filtrado
        $rol_id = isset($_GET['rol_id']) ? (int)$_GET['rol_id'] : null;
        $area_id = isset($_GET['area_id']) ? (int)$_GET['area_id'] : null;
        $departamento_id = isset($_GET['departamento_id']) ? (int)$_GET['departamento_id'] : null;
        $activo = isset($_GET['activo']) ? $_GET['activo'] : null;
        $busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : null;
        
        // Construye la consulta con filtros
        $filters = [];
        $params = [];
        
        if ($rol_id) {
            $filters[] = "u.rol_id = ?";
            $params[] = $rol_id;
        }
        
        if ($area_id) {
            $filters[] = "u.area_id = ?";
            $params[] = $area_id;
        }
        
        if ($departamento_id) {
            $filters[] = "u.departamento_id = ?";
            $params[] = $departamento_id;
        }
        
        if ($activo !== null) {
            $filters[] = "u.activo = ?";
            $params[] = $activo;
        }
        
        if ($busqueda) {
            $filters[] = "(u.nombre LIKE ? OR u.apellido LIKE ? OR u.email LIKE ?)";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }
        
        // Paginación
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        // Obtiene usuarios con filtros y paginación
        $users = $this->user->getFilteredUsers($filters, $params, $offset, $perPage);
        $totalUsers = $this->user->countFilteredUsers($filters, $params);
        
        $totalPages = ceil($totalUsers / $perPage);
        $currentPage = $page;
        
        // Construye parámetros de consulta para paginación
        $queryParams = '';
        foreach ($_GET as $key => $value) {
            if ($key != 'controller' && $key != 'action' && $key != 'page') {
                $queryParams .= "&$key=" . urlencode($value);
            }
        }
        
        // Obtiene áreas y departamentos para filtros
        $areas = $this->area->getAllAreas();
        $departments = $this->department->getAllDepartments();
        
        // Carga la vista
        include 'views/users/index.php';
    }
    
    /**
     * Muestra el formulario para crear un nuevo usuario
     */
    public function create() {
        // Verifica permisos: solo Administradores y Gerentes Generales
        if ($_SESSION['user_role'] > 2) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Obtiene roles, áreas y departamentos para el formulario
        $roles = $this->user->getRoles();
        $areas = $this->area->getAllAreas();
        
        // Inicializa departamentos vacíos
        $departments = [];
        
        // Carga la vista del formulario
        include 'views/users/create.php';
    }
    
    /**
     * Procesa la creación de un nuevo usuario
     */
    public function store() {
        // Verifica permisos: solo Administradores y Gerentes Generales
        if ($_SESSION['user_role'] > 2) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Verifica que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=user&action=create');
            exit;
        }
        
        // Valida datos requeridos
        $requiredFields = ['nombre', 'apellido', 'email', 'password', 'rol_id'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = 'Todos los campos marcados con asterisco son obligatorios';
                header('Location: index.php?controller=user&action=create');
                exit;
            }
        }
        
        // Valida formato de email
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'El formato del correo electrónico no es válido';
            header('Location: index.php?controller=user&action=create');
            exit;
        }
        
        // Verifica si el email ya existe
        if ($this->user->getUserByEmail($_POST['email'])) {
            $_SESSION['error'] = 'El correo electrónico ya está registrado por otro usuario';
            header('Location: index.php?controller=user&action=create');
            exit;
        }
        
        // Prepara datos del usuario
        $userData = [
            'nombre' => htmlspecialchars($_POST['nombre']),
            'apellido' => htmlspecialchars($_POST['apellido']),
            'email' => htmlspecialchars($_POST['email']),
            'password' => $_POST['password'],
            'rol_id' => (int)$_POST['rol_id'],
            'area_id' => !empty($_POST['area_id']) ? (int)$_POST['area_id'] : null,
            'departamento_id' => !empty($_POST['departamento_id']) ? (int)$_POST['departamento_id'] : null,
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];
        
        // Crea el usuario
        $userId = $this->user->createUser($userData);
        
        if ($userId) {
            $_SESSION['success'] = 'Usuario creado correctamente';
            header('Location: index.php?controller=user&action=index');
        } else {
            $_SESSION['error'] = 'Error al crear el usuario. Intente nuevamente.';
            header('Location: index.php?controller=user&action=create');
        }
        
        exit;
    }
    
    /**
     * Muestra la vista de detalle de un usuario
     */
    public function view() {
        // Verifica permisos: solo Administradores y Gerentes Generales pueden ver todos los usuarios
        if ($_SESSION['user_role'] > 2 && $_SESSION['user_id'] != $_GET['id']) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Verifica que se proporcione un ID
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        $userId = (int)$_GET['id'];
        $user = $this->user->getUserById($userId);
        
        // Verifica que el usuario exista
        if (!$user) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Carga la vista de detalle
        include 'views/users/view.php';
    }
    
    /**
     * Muestra el formulario para editar un usuario
     */
    public function edit() {
        // Verifica permisos: solo Administradores y Gerentes Generales, o el propio usuario
        $userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($_SESSION['user_role'] > 2 && $_SESSION['user_id'] != $userId) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Obtiene el usuario
        $user = $this->user->getUserById($userId);
        
        // Verifica que el usuario exista
        if (!$user) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Obtiene roles, áreas y departamentos para el formulario
        $roles = $this->user->getRoles();
        $areas = $this->area->getAllAreas();
        
        if ($user['area_id']) {
            $departments = $this->department->getDepartmentsByArea($user['area_id']);
        } else {
            $departments = [];
        }
        
        // Carga la vista del formulario
        include 'views/users/edit.php';
    }
    
    /**
     * Procesa la actualización de un usuario
     */
    public function update() {
        // Verifica permisos: solo Administradores y Gerentes Generales, o el propio usuario
        $userId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        if ($_SESSION['user_role'] > 2 && $_SESSION['user_id'] != $userId) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Verifica que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=user&action=edit&id=' . $userId);
            exit;
        }
        
        // Obtiene el usuario original
        $user = $this->user->getUserById($userId);
        
        // Verifica que el usuario exista
        if (!$user) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Valida datos requeridos
        $requiredFields = ['nombre', 'apellido', 'email', 'rol_id'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = 'Todos los campos marcados con asterisco son obligatorios';
                header('Location: index.php?controller=user&action=edit&id=' . $userId);
                exit;
            }
        }
        
        // Valida formato de email
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'El formato del correo electrónico no es válido';
            header('Location: index.php?controller=user&action=edit&id=' . $userId);
            exit;
        }
        
        // Verifica si el email ya existe (excluyendo el usuario actual)
        $existingUser = $this->user->getUserByEmail($_POST['email']);
        if ($existingUser && $existingUser['id'] != $userId) {
            $_SESSION['error'] = 'El correo electrónico ya está registrado por otro usuario';
            header('Location: index.php?controller=user&action=edit&id=' . $userId);
            exit;
        }
        
        // Prepara datos del usuario
        $userData = [
            'nombre' => htmlspecialchars($_POST['nombre']),
            'apellido' => htmlspecialchars($_POST['apellido']),
            'email' => htmlspecialchars($_POST['email']),
            'rol_id' => (int)$_POST['rol_id']
        ];
        
        // Solo administradores pueden cambiar área y departamento
        if ($_SESSION['user_role'] <= 2) {
            $userData['area_id'] = !empty($_POST['area_id']) ? (int)$_POST['area_id'] : null;
            $userData['departamento_id'] = !empty($_POST['departamento_id']) ? (int)$_POST['departamento_id'] : null;
            $userData['activo'] = isset($_POST['activo']) ? 1 : 0;
        }
        
        // Si se proporciona una nueva contraseña
        if (!empty($_POST['password'])) {
            // Cambia la contraseña
            $this->user->changePassword($userId, $_POST['password']);
        }
        
        // Actualiza el usuario
        $updated = $this->user->updateUser($userId, $userData);
        
        if ($updated) {
            $_SESSION['success'] = 'Usuario actualizado correctamente';
            
            // Si el usuario actualizó su propio perfil, actualiza los datos de sesión
            if ($_SESSION['user_id'] == $userId) {
                $_SESSION['user_name'] = $userData['nombre'] . ' ' . $userData['apellido'];
                $_SESSION['user_email'] = $userData['email'];
            }
            
            // Redirige a la vista adecuada
            if ($_SESSION['user_role'] <= 2 && $_SESSION['user_id'] != $userId) {
                header('Location: index.php?controller=user&action=index');
            } else {
                header('Location: index.php?controller=user&action=profile');
            }
        } else {
            $_SESSION['error'] = 'Error al actualizar el usuario. Intente nuevamente.';
            header('Location: index.php?controller=user&action=edit&id=' . $userId);
        }
        
        exit;
    }
    
    /**
     * Cambia el estado de un usuario (activo/inactivo)
     */
    public function toggleStatus() {
        // Verifica permisos: solo Administradores y Gerentes Generales
        if ($_SESSION['user_role'] > 2) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Verifica que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=user&action=index');
            exit;
        }
        
        // Verifica que se proporcionen los datos necesarios
        if (!isset($_POST['id']) || !isset($_POST['action'])) {
            header('Location: index.php?controller=user&action=index');
            exit;
        }
        
        $userId = (int)$_POST['id'];
        $action = $_POST['action'];
        
        // No permitir cambiar estado del propio usuario
        if ($userId == $_SESSION['user_id']) {
            $_SESSION['error'] = 'No puede cambiar su propio estado';
            header('Location: index.php?controller=user&action=index');
            exit;
        }
        
        // Obtiene el usuario
        $user = $this->user->getUserById($userId);
        
        // Verifica que el usuario exista
        if (!$user) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Cambia el estado según la acción
        $status = ($action == 'activate') ? 1 : 0;
        $updated = $this->user->toggleStatus($userId, $status);
        
        if ($updated) {
            $_SESSION['success'] = ($action == 'activate') ? 'Usuario activado correctamente' : 'Usuario desactivado correctamente';
        } else {
            $_SESSION['error'] = 'Error al cambiar el estado del usuario. Intente nuevamente.';
        }
        
        header('Location: index.php?controller=user&action=index');
        exit;
    }
    
    /**
     * Muestra el perfil del usuario actual
     */
    public function profile() {
        $userId = $_SESSION['user_id'];
        $user = $this->user->getUserById($userId);
        
        // Verifica que el usuario exista
        if (!$user) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Carga la vista del perfil
        include 'views/users/profile.php';
    }
    
    /**
     * Muestra el formulario para cambiar contraseña
     */
    public function changePassword() {
        // Carga la vista del formulario
        include 'views/users/change_password.php';
    }
    
    /**
     * Procesa el cambio de contraseña
     */
    public function updatePassword() {
        // Verifica que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=user&action=changePassword');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        
        // Valida datos requeridos
        if (empty($_POST['current_password']) || empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
            $_SESSION['error'] = 'Todos los campos son obligatorios';
            header('Location: index.php?controller=user&action=changePassword');
            exit;
        }
        
        // Valida que la nueva contraseña y la confirmación coincidan
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            $_SESSION['error'] = 'La nueva contraseña y la confirmación no coinciden';
            header('Location: index.php?controller=user&action=changePassword');
            exit;
        }
        
        // Verifica que la contraseña actual sea correcta
        $user = $this->user->getUserById($userId);
        if (!password_verify($_POST['current_password'], $user['password'])) {
            $_SESSION['error'] = 'La contraseña actual es incorrecta';
            header('Location: index.php?controller=user&action=changePassword');
            exit;
        }
        
        // Cambia la contraseña
        $updated = $this->user->changePassword($userId, $_POST['new_password']);
        
        if ($updated) {
            $_SESSION['success'] = 'Contraseña actualizada correctamente';
            header('Location: index.php?controller=user&action=profile');
        } else {
            $_SESSION['error'] = 'Error al actualizar la contraseña. Intente nuevamente.';
            header('Location: index.php?controller=user&action=changePassword');
        }
        
        exit;
    }
    
    /**
     * Obtiene departamentos por área (para AJAX)
     */
    public function getDepartmentsByArea() {
        // Verifica que sea una solicitud AJAX
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            echo json_encode(['error' => 'Solicitud no válida']);
            exit;
        }
        
        // Verifica que se proporcione un área
        if (!isset($_GET['area_id']) || !is_numeric($_GET['area_id'])) {
            echo json_encode(['error' => 'Área no válida']);
            exit;
        }
        
        $areaId = (int)$_GET['area_id'];
        $departments = $this->department->getDepartmentsByArea($areaId);
        
        echo json_encode(['departments' => $departments]);
        exit;
    }
}