<?php
/**
 * TaskController - Controlador para la gestión de tareas
 */
class TaskController {
    private $db;
    private $task;
    private $project;
    private $user;
    private $notification;
    
    public function __construct() {
        require_once 'config/database.php';
        require_once 'models/Task.php';
        require_once 'models/Project.php';
        require_once 'models/User.php';
        require_once 'models/Notification.php';
        require_once 'utils/AccessControl.php';
        
        $this->db = new Database();
        $this->task = new Task($this->db);
        $this->project = new Project($this->db);
        $this->user = new User($this->db);
        $this->notification = new Notification($this->db);
    }
    
    /**
     * Muestra la lista de tareas según permisos del usuario
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $userArea = $_SESSION['user_area'] ?? null;
        
        // Obtiene parámetros de filtro
        $projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : null;
        $estado = isset($_GET['estado']) ? $_GET['estado'] : null;
        $prioridad = isset($_GET['prioridad']) ? $_GET['prioridad'] : null;
        
        // Verificar acceso al proyecto si se especifica
        if ($projectId) {
            $project = $this->project->getProjectById($projectId);
            
            if (!$project) {
                header('Location: index.php?controller=error&action=not_found');
                exit;
            }
            
            // Verifica si puede ver este proyecto
            $projectUsers = $this->project->getProjectUsers($projectId);
            if (!AccessControl::canViewProject($project, $userId, $userRole, $userArea, $projectUsers)) {
                header('Location: index.php?controller=error&action=forbidden');
                exit;
            }
        }
        
        // Obtiene tareas según permisos
        $tasks = $this->task->getTasks($userId, $userRole, $userArea, $projectId);
        
        // Aplica filtros adicionales si existen
        if ($estado || $prioridad) {
            $tasks = array_filter($tasks, function($task) use ($estado, $prioridad) {
                $matchEstado = true;
                $matchPrioridad = true;
                
                if ($estado) {
                    $matchEstado = $task['estado'] == $estado;
                }
                
                if ($prioridad) {
                    $matchPrioridad = $task['prioridad'] == $prioridad;
                }
                
                return $matchEstado && $matchPrioridad;
            });
        }
        
        // Obtiene proyectos para el filtro
        $projects = $this->project->getProjects($userId, $userRole, $userArea);
        
        // Carga la vista
        //include 'views/tasks/index.php';
		include dirname(__FILE__) . '/../views/tasks/index.php';
    }
    
    /**
     * Muestra el formulario para crear una nueva tarea
     */
    public function create() {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $userArea = $_SESSION['user_area'] ?? null;
        
        // Verifica permisos mínimos (todos pueden crear tareas)
        
        // Si viene de un proyecto específico, verifica acceso
        $projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : null;
        
        if ($projectId) {
            $project = $this->project->getProjectById($projectId);
            
            if (!$project) {
                header('Location: index.php?controller=error&action=not_found');
                exit;
            }
            
            // Verifica si puede ver este proyecto
            $projectUsers = $this->project->getProjectUsers($projectId);
            if (!AccessControl::canViewProject($project, $userId, $userRole, $userArea, $projectUsers)) {
                header('Location: index.php?controller=error&action=forbidden');
                exit;
            }
            
            $selectedProject = $project;
        } else {
            $selectedProject = null;
        }
        
        // Obtiene lista de proyectos para el formulario (filtrado según permisos)
        $projects = $this->project->getProjects($userId, $userRole, $userArea);
        
        // Obtiene usuarios para asignación
        $usuarios = $this->user->getAllActiveUsers();
        
        // Carga la vista del formulario
        //include 'views/tasks/create.php';
		include dirname(__FILE__) . '/../views/tasks/create.php';
    }
    
    /**
     * Guarda una nueva tarea en la base de datos
     */
    public function store() {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $userArea = $_SESSION['user_area'] ?? null;
        
        // Valida que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=task&action=create');
            exit;
        }
        
        // Verifica campos obligatorios
        $requiredFields = ['titulo', 'proyecto_id', 'fecha_inicio', 'fecha_fin', 'estado', 'prioridad'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $_SESSION['error'] = 'Todos los campos marcados con * son obligatorios.';
                header('Location: index.php?controller=task&action=create');
                exit;
            }
        }
        
        // Verifica permiso para acceder al proyecto
        $projectId = (int)$_POST['proyecto_id'];
        $project = $this->project->getProjectById($projectId);
        
        if (!$project) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Verifica si puede ver este proyecto
        $projectUsers = $this->project->getProjectUsers($projectId);
        if (!AccessControl::canViewProject($project, $userId, $userRole, $userArea, $projectUsers)) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Valida fechas
        if (strtotime($_POST['fecha_inicio']) > strtotime($_POST['fecha_fin'])) {
            $_SESSION['error'] = 'La fecha de finalización debe ser posterior a la fecha de inicio.';
            header('Location: index.php?controller=task&action=create');
            exit;
        }
        
        // Verifica que las fechas estén dentro del rango del proyecto
        if (strtotime($_POST['fecha_inicio']) < strtotime($project['fecha_inicio']) ||
            strtotime($_POST['fecha_fin']) > strtotime($project['fecha_fin'])) {
            $_SESSION['error'] = 'Las fechas de la tarea deben estar dentro del período del proyecto.';
            header('Location: index.php?controller=task&action=create');
            exit;
        }
        
        // Prepara datos para crear la tarea
        $taskData = [
            'titulo' => htmlspecialchars($_POST['titulo']),
            'descripcion' => htmlspecialchars($_POST['descripcion'] ?? ''),
            'proyecto_id' => $projectId,
            'asignado_a' => !empty($_POST['asignado_a']) ? $_POST['asignado_a'] : null,
            'creado_por' => $userId,
            'fecha_inicio' => $_POST['fecha_inicio'],
            'fecha_fin' => $_POST['fecha_fin'],
            'estado' => $_POST['estado'],
            'prioridad' => $_POST['prioridad'],
            'porcentaje_completado' => $_POST['porcentaje_completado'] ?? 0
        ];
        
        // Crea la tarea en la base de datos
        $taskId = $this->task->createTask($taskData);
        
        if ($taskId) {
            // Notifica al usuario asignado si existe
            if (!empty($taskData['asignado_a'])) {
                $this->notification->notifyTaskChange(
                    ['id' => $taskId, 'titulo' => $taskData['titulo']], 
                    'assign', 
                    [$taskData['asignado_a']]
                );
            }
            
            $_SESSION['success'] = 'Tarea creada correctamente.';
            header('Location: index.php?controller=task&action=view&id=' . $taskId);
        } else {
            $_SESSION['error'] = 'Error al crear la tarea. Intente nuevamente.';
            header('Location: index.php?controller=task&action=create');
        }
        
        exit;
    }
    
    /**
     * Muestra los detalles de una tarea específica
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
        
        $taskId = (int)$_GET['id'];
        $task = $this->task->getTaskById($taskId);
        
        // Verifica que la tarea exista
        if (!$task) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Verifica permisos de acceso
        if (!$this->task->userHasAccessToTask($userId, $taskId, $userRole, $userArea)) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Obtiene comentarios e historial
        $comments = $this->task->getTaskComments($taskId);
        $history = $this->task->getTaskHistory($taskId);
        
        // Carga la vista
        include 'views/tasks/view.php';
    }
    
    /**
     * Muestra el formulario para editar una tarea
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
        
        $taskId = (int)$_GET['id'];
        $task = $this->task->getTaskById($taskId);
        
        // Verifica que la tarea exista
        if (!$task) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Verifica permisos de edición
        if (!AccessControl::canEditTask($task, $userId, $userRole, $userArea)) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Obtiene lista de proyectos para el formulario (filtrado según permisos)
        $projects = $this->project->getProjects($userId, $userRole, $userArea);
        
        // Obtiene usuarios para asignación
        $usuarios = $this->user->getAllActiveUsers();
        
        // Carga la vista del formulario
        include 'views/tasks/edit.php';
    }
    
    /**
     * Actualiza una tarea existente
     */
    public function update() {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $userArea = $_SESSION['user_area'] ?? null;
        
        // Valida que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=task&action=index');
            exit;
        }
        
        // Verifica que se proporcione un ID válido
        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        $taskId = (int)$_POST['id'];
        $task = $this->task->getTaskById($taskId);
        
        // Verifica que la tarea exista
        if (!$task) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Verifica permisos de edición
        if (!AccessControl::canEditTask($task, $userId, $userRole, $userArea)) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Limita los campos que puede editar un colaborador
        $isCollaborator = ($userRole == AccessControl::ROLE_COLLABORATOR);
        
        // Verifica campos obligatorios
        $requiredFields = ['titulo', 'estado', 'prioridad'];
        
        // Añade más campos obligatorios si no es colaborador
        if (!$isCollaborator) {
            $requiredFields = array_merge($requiredFields, ['proyecto_id']);
        }
        
        foreach ($requiredFields as $field) {
            if (isset($_POST[$field]) && empty($_POST[$field])) {
                $_SESSION['error'] = 'Todos los campos marcados con * son obligatorios.';
                header('Location: index.php?controller=task&action=edit&id=' . $taskId);
                exit;
            }
        }
        
        // Prepara datos para actualizar la tarea
        $taskData = [];
        
        // Los colaboradores solo pueden actualizar estado y porcentaje
        if ($isCollaborator) {
            $taskData = [
                'estado' => $_POST['estado'],
                'porcentaje_completado' => $_POST['porcentaje_completado'] ?? 0
            ];
        } else {
            // Datos completos para roles con más permisos
            $taskData = [
                'titulo' => htmlspecialchars($_POST['titulo']),
                'descripcion' => htmlspecialchars($_POST['descripcion'] ?? ''),
                'proyecto_id' => $_POST['proyecto_id'],
                'asignado_a' => !empty($_POST['asignado_a']) ? $_POST['asignado_a'] : null,
                'estado' => $_POST['estado'],
                'prioridad' => $_POST['prioridad'],
                'porcentaje_completado' => $_POST['porcentaje_completado'] ?? 0
            ];
            
            // Solo administradores y gerentes generales pueden cambiar fechas
            if (AccessControl::canManageDates($userRole)) {
                // Valida fechas
                if (empty($_POST['fecha_inicio']) || empty($_POST['fecha_fin'])) {
                    $_SESSION['error'] = 'Las fechas son obligatorias.';
                    header('Location: index.php?controller=task&action=edit&id=' . $taskId);
                    exit;
                }
                
                if (strtotime($_POST['fecha_inicio']) > strtotime($_POST['fecha_fin'])) {
                    $_SESSION['error'] = 'La fecha de finalización debe ser posterior a la fecha de inicio.';
                    header('Location: index.php?controller=task&action=edit&id=' . $taskId);
                    exit;
                }
                
                $taskData['fecha_inicio'] = $_POST['fecha_inicio'];
                $taskData['fecha_fin'] = $_POST['fecha_fin'];
            }
        }
        
        // Actualiza la tarea en la base de datos
        $updated = $this->task->updateTask($taskId, $taskData, $userId);
        
        if ($updated) {
            // Notifica al usuario asignado si ha cambiado
            if (isset($taskData['asignado_a']) && $taskData['asignado_a'] != $task['asignado_a']) {
                $this->notification->notifyTaskChange(
                    ['id' => $taskId, 'titulo' => $_POST['titulo']], 
                    'assign', 
                    [$taskData['asignado_a']]
                );
            }
            
            // Notifica al creador si el estado ha cambiado
            if ($taskData['estado'] != $task['estado'] && $userId != $task['creado_por']) {
                $this->notification->notifyTaskChange(
                    ['id' => $taskId, 'titulo' => $_POST['titulo'], 'estado' => $taskData['estado']], 
                    'status', 
                    [$task['creado_por']]
                );
            }
            
            $_SESSION['success'] = 'Tarea actualizada correctamente.';
        } else {
            $_SESSION['error'] = 'Error al actualizar la tarea. Intente nuevamente.';
        }
        
        header('Location: index.php?controller=task&action=view&id=' . $taskId);
        exit;
    }
    
    /**
     * Actualiza solo el estado y porcentaje de una tarea (para actualizaciones rápidas)
     */
    public function updateStatus() {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $userArea = $_SESSION['user_area'] ?? null;
        
        // Valida que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=task&action=index');
            exit;
        }
        
        // Verifica que se proporcione un ID válido
        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        $taskId = (int)$_POST['id'];
        $task = $this->task->getTaskById($taskId);
        
        // Verifica que la tarea exista
        if (!$task) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Verifica permisos (permitido para el asignado o roles superiores)
        $canUpdate = ($task['asignado_a'] == $userId) || 
                     ($userRole <= AccessControl::ROLE_DEPARTMENT_HEAD) ||
                     ($userRole == AccessControl::ROLE_AREA_MANAGER && $task['proyecto_area_id'] == $userArea);
        
        if (!$canUpdate) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Verifica campos obligatorios
        if (empty($_POST['estado']) || !isset($_POST['porcentaje_completado'])) {
            $_SESSION['error'] = 'El estado y porcentaje son obligatorios.';
            header('Location: index.php?controller=task&action=view&id=' . $taskId);
            exit;
        }
        
        // Prepara datos para actualizar la tarea
        $taskData = [
            'estado' => $_POST['estado'],
            'porcentaje_completado' => (int)$_POST['porcentaje_completado']
        ];
        
        // Si se marca como completado, establece porcentaje en 100%
        if ($taskData['estado'] == 'Completado' && $taskData['porcentaje_completado'] < 100) {
            $taskData['porcentaje_completado'] = 100;
        }
        
        // Actualiza la tarea en la base de datos
        $updated = $this->task->updateTask($taskId, $taskData, $userId);
        
        if ($updated) {
            // Notifica al creador si el estado ha cambiado y no es el mismo usuario
            if ($taskData['estado'] != $task['estado'] && $userId != $task['creado_por']) {
                $this->notification->notifyTaskChange(
                    ['id' => $taskId, 'titulo' => $task['titulo'], 'estado' => $taskData['estado']], 
                    'status', 
                    [$task['creado_por']]
                );
            }
            
            $_SESSION['success'] = 'Estado de la tarea actualizado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al actualizar el estado. Intente nuevamente.';
        }
        
        header('Location: index.php?controller=task&action=view&id=' . $taskId);
        exit;
    }
    
    /**
     * Elimina una tarea
     */
    public function delete() {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        
        // Valida que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=task&action=index');
            exit;
        }
        
        // Verifica que se proporcione un ID válido
        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        $taskId = (int)$_POST['id'];
        $task = $this->task->getTaskById($taskId);
        
        // Verifica que la tarea exista
        if (!$task) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Verifica permisos (solo admin, gerente general o creador de la tarea)
        $canDelete = AccessControl::canDelete($userRole) || 
                    ($task['creado_por'] == $userId && $userRole <= AccessControl::ROLE_DEPARTMENT_HEAD);
        
        if (!$canDelete) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Elimina la tarea
        $deleted = $this->task->deleteTask($taskId);
        
        if ($deleted) {
            // Notifica al usuario asignado si existe
            if (!empty($task['asignado_a']) && $task['asignado_a'] != $userId) {
                $this->notification->create(
                    $task['asignado_a'],
                    "La tarea '" . $task['titulo'] . "' ha sido eliminada.",
                    'tarea'
                );
            }
            
            $_SESSION['success'] = 'Tarea eliminada correctamente.';
            
            // Redirige a la lista de tareas o al proyecto
            if (isset($_POST['redirect_project']) && $_POST['redirect_project'] == 1) {
                header('Location: index.php?controller=project&action=view&id=' . $task['proyecto_id']);
            } else {
                header('Location: index.php?controller=task&action=index');
            }
        } else {
            $_SESSION['error'] = 'Error al eliminar la tarea. Intente nuevamente.';
            header('Location: index.php?controller=task&action=view&id=' . $taskId);
        }
        
        exit;
    }
    
    /**
     * Agrega un comentario a una tarea
     */
    public function addComment() {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $userArea = $_SESSION['user_area'] ?? null;
        
        // Valida que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?controller=task&action=index');
            exit;
        }
        
        // Verifica datos obligatorios
        if (empty($_POST['tarea_id']) || empty($_POST['comentario'])) {
            $_SESSION['error'] = 'Todos los campos son obligatorios.';
            header('Location: index.php?controller=task&action=index');
            exit;
        }
        
        $taskId = (int)$_POST['tarea_id'];
        $task = $this->task->getTaskById($taskId);
        
        // Verifica que la tarea exista
        if (!$task) {
            header('Location: index.php?controller=error&action=not_found');
            exit;
        }
        
        // Verifica permisos de acceso
        if (!$this->task->userHasAccessToTask($userId, $taskId, $userRole, $userArea)) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Sanitiza el comentario
        $comentario = htmlspecialchars($_POST['comentario']);
        
        // Agrega el comentario
        $commentId = $this->task->addTaskComment($taskId, $userId, $comentario);
        
        if ($commentId) {
            $_SESSION['success'] = 'Comentario agregado correctamente.';
        } else {
            $_SESSION['error'] = 'Error al agregar el comentario. Intente nuevamente.';
        }
        
        header('Location: index.php?controller=task&action=view&id=' . $taskId);
        exit;
    }
    
    /**
     * Muestra el calendario de tareas
     */
    public function calendar() {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $userArea = $_SESSION['user_area'] ?? null;
        
        // Obtiene tareas según permisos
        $tasks = $this->task->getTasks($userId, $userRole, $userArea);
        
        // Prepara datos para el calendario
        $calendarTasks = [];
        
        foreach ($tasks as $task) {
            // Define el color según el estado
            $color = '#6c757d'; // Gris para pendiente por defecto
            
            switch ($task['estado']) {
                case 'En Progreso':
                    $color = '#007bff'; // Azul
                    break;
                case 'Completado':
                    $color = '#28a745'; // Verde
                    break;
                case 'Cancelado':
                    $color = '#dc3545'; // Rojo
                    break;
            }
            
            $calendarTasks[] = [
                'id' => $task['id'],
                'title' => $task['titulo'],
                'start' => $task['fecha_inicio'],
                'end' => date('Y-m-d', strtotime($task['fecha_fin'] . ' +1 day')), // Ajuste para que incluya el día de fin
                'color' => $color,
                'url' => 'index.php?controller=task&action=view&id=' . $task['id']
            ];
        }
        
        // Convierte a JSON para el calendario
        $calendarData = json_encode($calendarTasks);
        
        // Carga la vista
        //include __DIR__ . '/../views/tasks/calendar.php';
		include dirname(__FILE__) . '/../views/tasks/calendar.php';
    }
}