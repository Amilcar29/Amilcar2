<?php
/**
 * ProjectController - Controlador para la gestión de proyectos
 */
class ProjectController {
    private $db;
    private $project;
    private $user;
    private $area;
    private $notification;
    
    public function __construct() {
        require_once 'config/database.php';
        require_once 'models/Project.php';
        require_once 'models/User.php';
        require_once 'models/Area.php';
        require_once 'models/Notification.php';
        require_once 'utils/AccessControl.php';
        
        $this->db = new Database();
        $this->project = new Project($this->db);
        $this->user = new User($this->db);
        $this->area = new Area($this->db);
        $this->notification = new Notification($this->db);
    }
    
    /**
     * Muestra los detalles de un proyecto específico
     */
    public function view() {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $userArea = $_SESSION['user_area'] ?? null;
        
        // Verifica que se proporcione un ID válido
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        $projectId = (int)$_GET['id'];
        $project = $this->project->getProjectById($projectId);
        
        // Verifica que el proyecto exista
        if (!$project) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Obtiene usuarios asignados al proyecto
        $projectUsers = $this->project->getProjectUsers($projectId);
        
        // Verifica permisos de acceso
        if (!AccessControl::canViewProject($project, $userId, $userRole, $userArea, $projectUsers)) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Obtiene tareas del proyecto
        require_once 'models/Task.php';
        $task = new Task($this->db);
        $tasks = $task->getTasks($userId, $userRole, $userArea, $projectId);
        
        // Calcula estadísticas del proyecto (tareas completadas, en progreso, etc.)
        $totalTasks = count($tasks);
        $completedTasks = 0;
        $inProgressTasks = 0;
        
        foreach ($tasks as $taskItem) {
            if ($taskItem['estado'] == 'Completado') {
                $completedTasks++;
            } elseif ($taskItem['estado'] == 'En Progreso') {
                $inProgressTasks++;
            }
        }
        
        // Calcula porcentaje de progreso del proyecto
        $projectProgress = ($totalTasks > 0) 
            ? round(($completedTasks / $totalTasks) * 100) 
            : 0;
        
        // Carga la vista
        include 'views/projects/view.php';
    }
    
    /**
     * Muestra la lista de proyectos según permisos del usuario
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $userArea = $_SESSION['user_area'] ?? null;
        
        // Obtiene proyectos según permisos
        $projects = $this->project->getProjects($userId, $userRole, $userArea);
        
        // Obtiene áreas para filtros (si tiene permisos)
        $areas = ($userRole <= AccessControl::ROLE_GENERAL_MANAGER) 
            ? $this->area->getAllAreas() 
            : [];
        
        // Carga la vista
        include 'views/projects/index.php';
    }
    
    /**
     * Muestra el formulario para crear un nuevo proyecto
     */
    public function create() {
        $userRole = $_SESSION['user_role'];
        
        // Verifica permisos mínimos (hasta jefe de departamento puede crear)
        if ($userRole > AccessControl::ROLE_DEPARTMENT_HEAD) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Obtiene lista de áreas y usuarios para el formulario
        $areas = $this->area->getAllAreas();
        
        // Si es gerente de área, solo muestra su área
        if ($userRole == AccessControl::ROLE_AREA_MANAGER) {
            $areaId = $_SESSION['user_area'];
            $areas = array_filter($areas, function($area) use ($areaId) {
                return $area['id'] == $areaId;
            });
        }
        
        // Obtiene todos los usuarios para asignación
        $usuarios = $this->user->getAllActiveUsers();
        
        // Carga la vista del formulario
        include 'views/projects/create.php';
    }
    
    /**
     * Guarda un nuevo proyecto en la base de datos
     */
    public function store() {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        
        // Verifica permisos mínimos
        if ($userRole > AccessControl::ROLE_DEPARTMENT_HEAD) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Valida que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=project&action=create');
            exit;
        }
        
        // Verifica campos obligatorios
        $requiredFields = ['titulo', 'fecha_inicio', 'fecha_fin', 'estado', 'prioridad'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = 'Todos los campos marcados con * son obligatorios.';
                header('Location: index.php?controller=project&action=create');
                exit;
            }
        }
        
        // Si es gerente de área, solo puede crear proyectos en su área
        if ($userRole == AccessControl::ROLE_AREA_MANAGER && $_POST['area_id'] != $_SESSION['user_area']) {
            $_SESSION['error'] = 'Solo puede crear proyectos en su área asignada.';
            header('Location: index.php?controller=project&action=create');
            exit;
        }
        
        // Valida fechas
        if (strtotime($_POST['fecha_inicio']) > strtotime($_POST['fecha_fin'])) {
            $_SESSION['error'] = 'La fecha de finalización debe ser posterior a la fecha de inicio.';
            header('Location: index.php?controller=project&action=create');
            exit;
        }
        
        // Prepara datos para crear el proyecto
        $projectData = [
            'titulo' => htmlspecialchars($_POST['titulo']),
            'descripcion' => htmlspecialchars($_POST['descripcion'] ?? ''),
            'fecha_inicio' => $_POST['fecha_inicio'],
            'fecha_fin' => $_POST['fecha_fin'],
            'area_id' => $_POST['area_id'],
            'creado_por' => $userId,
            'estado' => $_POST['estado'],
            'prioridad' => $_POST['prioridad'],
            'usuarios' => isset($_POST['usuarios']) ? $_POST['usuarios'] : []
        ];
        
        // Crea el proyecto en la base de datos
        $projectId = $this->project->createProject($projectData);
        
        if ($projectId) {
            // Notifica a los usuarios asignados
            if (!empty($projectData['usuarios'])) {
                $this->notification->notifyProjectChange(
                    ['id' => $projectId, 'titulo' => $projectData['titulo']], 
                    'assign', 
                    $projectData['usuarios']
                );
            }
            
            $_SESSION['success'] = 'Proyecto creado correctamente.';
            header('Location: index.php?controller=project&action=view&id=' . $projectId);
        } else {
            $_SESSION['error'] = 'Error al crear el proyecto. Intente nuevamente.';
            header('Location: index.php?controller=project&action=create');
        }
        
        exit;
    }
    
    /**
     * Muestra el formulario para editar un proyecto
     */
    public function edit() {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $userArea = $_SESSION['user_area'] ?? null;
        
        // Verifica que se proporcione un ID válido
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        $projectId = (int)$_GET['id'];
        $project = $this->project->getProjectById($projectId);
        
        // Verifica que el proyecto exista
        if (!$project) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Verifica permisos de edición
        if (!AccessControl::canEditProject($project, $userId, $userRole, $userArea)) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Obtiene listas para el formulario
        $areas = $this->area->getAllAreas();
        
        // Si es gerente de área, solo muestra su área
        if ($userRole == AccessControl::ROLE_AREA_MANAGER) {
            $areaId = $_SESSION['user_area'];
            $areas = array_filter($areas, function($area) use ($areaId) {
                return $area['id'] == $areaId;
            });
        }
        
        // Obtiene usuarios asignados al proyecto
        $projectUsers = $this->project->getProjectUsers($projectId);
        $projectUserIds = array_map(function($user) {
            return $user['id'];
        }, $projectUsers);
        
        // Obtiene todos los usuarios para asignación
        $usuarios = $this->user->getAllActiveUsers();
        
        // Carga la vista del formulario
        include 'views/projects/edit.php';
    }
    
    /**
     * Actualiza un proyecto existente
     */
    public function update() {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $userArea = $_SESSION['user_area'] ?? null;
        
        // Valida que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=project&action=index');
            exit;
        }
        
        // Verifica que se proporcione un ID válido
        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        $projectId = (int)$_POST['id'];
        $project = $this->project->getProjectById($projectId);
        
        // Verifica que el proyecto exista
        if (!$project) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Verifica permisos de edición
        if (!AccessControl::canEditProject($project, $userId, $userRole, $userArea)) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Verifica campos obligatorios
        $requiredFields = ['titulo', 'estado', 'prioridad'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = 'Todos los campos marcados con * son obligatorios.';
                header('Location: index.php?controller=project&action=edit&id=' . $projectId);
                exit;
            }
        }
        
        // Verificaciones adicionales para administradores y gerentes generales (fechas)
        if (AccessControl::canManageDates($userRole)) {
            // Si puede gestionar fechas, esos campos son obligatorios
            if (empty($_POST['fecha_inicio']) || empty($_POST['fecha_fin'])) {
                $_SESSION['error'] = 'Las fechas son obligatorias.';
                header('Location: index.php?controller=project&action=edit&id=' . $projectId);
                exit;
            }
            
            // Valida fechas
            if (strtotime($_POST['fecha_inicio']) > strtotime($_POST['fecha_fin'])) {
                $_SESSION['error'] = 'La fecha de finalización debe ser posterior a la fecha de inicio.';
                header('Location: index.php?controller=project&action=edit&id=' . $projectId);
                exit;
            }
        }
        
        // Si es gerente de área, solo puede asignar a su área
        if ($userRole == AccessControl::ROLE_AREA_MANAGER && $_POST['area_id'] != $_SESSION['user_area']) {
            $_SESSION['error'] = 'Solo puede asignar proyectos a su área.';
            header('Location: index.php?controller=project&action=edit&id=' . $projectId);
            exit;
        }
        
        // Prepara datos para actualizar el proyecto
        $projectData = [
            'titulo' => htmlspecialchars($_POST['titulo']),
            'descripcion' => htmlspecialchars($_POST['descripcion'] ?? ''),
            'estado' => $_POST['estado'],
            'prioridad' => $_POST['prioridad'],
            'area_id' => $_POST['area_id']
        ];
        
        // Solo agrega fechas si el usuario tiene permisos
        if (AccessControl::canManageDates($userRole)) {
            $projectData['fecha_inicio'] = $_POST['fecha_inicio'];
            $projectData['fecha_fin'] = $_POST['fecha_fin'];
        }
        
        // Actualiza el proyecto en la base de datos
        $updated = $this->project->updateProject($projectId, $projectData);
        
        if ($updated) {
            // Gestiona asignaciones de usuarios si se han enviado
            if (isset($_POST['usuarios'])) {
                // Obtiene usuarios actuales
                $currentUsers = $this->project->getProjectUsers($projectId);
                $currentUserIds = array_map(function($user) {
                    return $user['id'];
                }, $currentUsers);
                
                // Nuevos usuarios a asignar
                $newUserIds = array_diff($_POST['usuarios'], $currentUserIds);
                
                // Usuarios a eliminar
                $removeUserIds = array_diff($currentUserIds, $_POST['usuarios']);
                
                // Asigna nuevos usuarios
                foreach ($newUserIds as $newUserId) {
                    $this->project->assignUserToProject($newUserId, $projectId);
                }
                
                // Elimina asignaciones
                foreach ($removeUserIds as $removeUserId) {
                    // No eliminar al creador del proyecto
                    if ($removeUserId != $project['creado_por']) {
                        $this->project->removeUserFromProject($removeUserId, $projectId);
                    }
                }
                
                // Notifica a los nuevos usuarios asignados
                if (!empty($newUserIds)) {
                    $this->notification->notifyProjectChange(
                        ['id' => $projectId, 'titulo' => $projectData['titulo']], 
                        'assign', 
                        $newUserIds
                    );
                }
            }
            
            $_SESSION['success'] = 'Proyecto actualizado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al actualizar el proyecto. Intente nuevamente.';
        }
        
        header('Location: index.php?controller=project&action=view&id=' . $projectId);
        exit;
    }
    
    /**
     * Elimina un proyecto
     */
    public function delete() {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        
        // Valida que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=project&action=index');
            exit;
        }
        
        // Verifica permisos (solo admin y gerente general pueden eliminar)
        if (!AccessControl::canDelete($userRole)) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Verifica que se proporcione un ID válido
        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        $projectId = (int)$_POST['id'];
        $project = $this->project->getProjectById($projectId);
        
        // Verifica que el proyecto exista
        if (!$project) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Obtiene usuarios asignados para notificarles después
        $projectUsers = $this->project->getProjectUsers($projectId);
        $userIds = array_map(function($user) {
            return $user['id'];
        }, $projectUsers);
        
        // Elimina el proyecto
        $deleted = $this->project->deleteProject($projectId);
        
        if ($deleted) {
            // Notifica a los usuarios asignados
            if (!empty($userIds)) {
                $this->notification->notifyProjectChange(
                    ['id' => $projectId, 'titulo' => $project['titulo']], 
                    'delete', 
                    $userIds
                );
            }
            
            $_SESSION['success'] = 'Proyecto eliminado correctamente.';
            header('Location: index.php?controller=project&action=index');
        } else {
            $_SESSION['error'] = 'Error al eliminar el proyecto. Intente nuevamente.';
            header('Location: index.php?controller=project&action=view&id=' . $projectId);
        }
        
        exit;
    }
    
    /**
     * Muestra el formulario para asignar usuarios a un proyecto
     */
    public function assign() {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $userArea = $_SESSION['user_area'] ?? null;
        
        // Verifica que se proporcione un ID válido
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        $projectId = (int)$_GET['id'];
        $project = $this->project->getProjectById($projectId);
        
        // Verifica que el proyecto exista
        if (!$project) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Verifica permisos de edición
        if (!AccessControl::canEditProject($project, $userId, $userRole, $userArea)) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Obtiene usuarios asignados al proyecto
        $projectUsers = $this->project->getProjectUsers($projectId);
        $projectUserIds = array_map(function($user) {
            return $user['id'];
        }, $projectUsers);
        
        // Obtiene todos los usuarios para asignación
        $usuarios = $this->user->getAllActiveUsers();
        
        // Carga la vista del formulario
        include 'views/projects/assign.php';
    }
    
    /**
     * Procesa la asignación de usuarios a un proyecto
     */
    public function saveAssignments() {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $userArea = $_SESSION['user_area'] ?? null;
        
        // Valida que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=project&action=index');
            exit;
        }
        
        // Verifica que se proporcione un ID válido
        if (!isset($_POST['project_id']) || !is_numeric($_POST['project_id'])) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        $projectId = (int)$_POST['project_id'];
        $project = $this->project->getProjectById($projectId);
        
        // Verifica que el proyecto exista
        if (!$project) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Verifica permisos de edición
        if (!AccessControl::canEditProject($project, $userId, $userRole, $userArea)) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Obtiene usuarios actuales
        $currentUsers = $this->project->getProjectUsers($projectId);
        $currentUserIds = array_map(function($user) {
            return $user['id'];
        }, $currentUsers);
        
        // Nuevos usuarios a asignar
        $selectedUsers = isset($_POST['usuarios']) ? $_POST['usuarios'] : [];
        
        // Asegura que el creador siempre esté asignado
        if (!in_array($project['creado_por'], $selectedUsers)) {
            $selectedUsers[] = $project['creado_por'];
        }
        
        // Nuevos usuarios a asignar
        $newUserIds = array_diff($selectedUsers, $currentUserIds);
        
        // Usuarios a eliminar
        $removeUserIds = array_diff($currentUserIds, $selectedUsers);
        
        // Asigna nuevos usuarios
        foreach ($newUserIds as $newUserId) {
            $this->project->assignUserToProject($newUserId, $projectId);
        }
        
        // Elimina asignaciones
        foreach ($removeUserIds as $removeUserId) {
            // No eliminar al creador del proyecto
            if ($removeUserId != $project['creado_por']) {
                $this->project->removeUserFromProject($removeUserId, $projectId);
            }
        }
        
        // Notifica a los nuevos usuarios asignados
        if (!empty($newUserIds)) {
            $this->notification->notifyProjectChange(
                ['id' => $projectId, 'titulo' => $project['titulo']], 
                'assign', 
                $newUserIds
            );
        }
        
        $_SESSION['success'] = 'Asignaciones actualizadas correctamente.';
        header('Location: index.php?controller=project&action=view&id=' . $projectId);
        exit;
    }
}