<?php
/**
 * DashboardController - Controlador para las vistas del panel de control
 */
class DashboardController {
    private $db;
    private $project;
    private $task;
    private $user;
    private $notification;
    
    public function __construct() {
        require_once 'config/database.php';
        require_once 'models/Project.php';
        require_once 'models/Task.php';
        require_once 'models/User.php';
        require_once 'models/Notification.php';
        
        $this->db = new Database();
        $this->project = new Project($this->db);
        $this->task = new Task($this->db);
        $this->user = new User($this->db);
        $this->notification = new Notification($this->db);
    }
    
    /**
     * Método predeterminado - Redirige según el rol del usuario
     */
    public function index() {
        $userRole = $_SESSION['user_role'];
        
        switch ($userRole) {
            case 1: // Administrador
            case 2: // Gerente General
                $this->admin();
                break;
            case 3: // Gerente de Área
                $this->area();
                break;
            case 4: // Jefe de Departamento
                $this->department();
                break;
            case 5: // Colaborador
                $this->collaborator();
                break;
            default:
                // Si hay un rol no reconocido, redirige al login
                header('Location: index.php?controller=auth&action=logout');
                exit;
        }
    }
    
    /**
     * Dashboard para Administrador y Gerente General
     */
    public function admin() {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        
        // Obtiene estadísticas generales
        $projectStats = $this->project->getProjectStats();
        $taskStats = $this->task->getTaskStats();
        
        // Obtiene proyectos recientes
        $recentProjects = $this->project->getProjects();
        $recentProjects = array_slice($recentProjects, 0, 5); // Limita a 5 proyectos
        
        // Obtiene tareas pendientes
        $pendingTasks = array_filter($this->task->getTasks(), function($task) {
            return $task['estado'] == 'Pendiente' || $task['estado'] == 'En Progreso';
        });
        $pendingTasks = array_slice($pendingTasks, 0, 5); // Limita a 5 tareas
        
        // Cargar la vista del dashboard de administrador
        include 'views/dashboard/admin.php';
    }
    
    /**
     * Dashboard para Gerente de Área
     */
    public function area() {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $userArea = $_SESSION['user_area'] ?? null;
        
        if (!$userArea) {
            $_SESSION['error'] = 'No tiene un área asignada. Contacte al administrador.';
            header('Location: index.php?controller=auth&action=logout');
            exit;
        }
        
        // Obtiene estadísticas del área
        $projectStats = $this->project->getProjectStats($userId, $userRole, $userArea);
        $taskStats = $this->task->getTaskStats($userId, $userRole, $userArea);
        
        // Obtiene proyectos recientes del área
        $recentProjects = $this->project->getProjects($userId, $userRole, $userArea);
        $recentProjects = array_slice($recentProjects, 0, 5); // Limita a 5 proyectos
        
        // Obtiene tareas pendientes del área
        $pendingTasks = array_filter($this->task->getTasks($userId, $userRole, $userArea), function($task) {
            return $task['estado'] == 'Pendiente' || $task['estado'] == 'En Progreso';
        });
        $pendingTasks = array_slice($pendingTasks, 0, 5); // Limita a 5 tareas
        
        // Cargar la vista del dashboard de gerente de área
        include 'views/dashboard/area-manager.php';
    }
    
    /**
     * Dashboard para Jefe de Departamento
     */
    public function department() {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $userDepartment = $_SESSION['user_department'] ?? null;
        
        // Obtiene estadísticas de sus proyectos y tareas
        $projectStats = $this->project->getProjectStats($userId, $userRole);
        $taskStats = $this->task->getTaskStats($userId, $userRole);
        
        // Obtiene proyectos recientes donde está involucrado
        $recentProjects = $this->project->getProjects($userId, $userRole);
        $recentProjects = array_slice($recentProjects, 0, 5); // Limita a 5 proyectos
        
        // Obtiene tareas pendientes asignadas a él o creadas por él
        $pendingTasks = array_filter($this->task->getTasks($userId, $userRole), function($task) {
            return $task['estado'] == 'Pendiente' || $task['estado'] == 'En Progreso';
        });
        $pendingTasks = array_slice($pendingTasks, 0, 5); // Limita a 5 tareas
        
        // Cargar la vista del dashboard de jefe de departamento
        include 'views/dashboard/department-head.php';
    }
    
    /**
     * Dashboard para Colaborador
     */
    public function collaborator() {
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        
        // Obtiene estadísticas de sus tareas asignadas
        $taskStats = $this->task->getTaskStats($userId, $userRole);
        
        // Obtiene tareas pendientes asignadas
        $pendingTasks = array_filter($this->task->getTasks($userId, $userRole), function($task) {
            return $task['estado'] == 'Pendiente' || $task['estado'] == 'En Progreso';
        });
        
        // Obtiene proyectos en los que está involucrado
        $projects = $this->project->getProjects($userId, $userRole);
        
        // Cargar la vista del dashboard de colaborador
        include 'views/dashboard/collaborator.php';
    }
}