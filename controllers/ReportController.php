<?php
/**
 * ReportController - Controlador para la generación de reportes
 */
class ReportController {
    private $db;
    private $project;
    private $task;
    private $user;
    private $area;
    private $department;
    
    public function __construct() {
        require_once 'config/database.php';
        require_once 'models/Project.php';
        require_once 'models/Task.php';
        require_once 'models/User.php';
        require_once 'models/Area.php';
        require_once 'models/Department.php';
        require_once 'utils/AccessControl.php';
        
        $this->db = new Database();
        $this->project = new Project($this->db);
        $this->task = new Task($this->db);
        $this->user = new User($this->db);
        $this->area = new Area($this->db);
        $this->department = new Department($this->db);
    }
    
    /**
     * Reporte de Proyectos
     */
    public function projects() {
        // Verificar permisos: solo Administradores y Gerentes
        if ($_SESSION['user_role'] > 3) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $userArea = $_SESSION['user_area'] ?? null;
        
        // Obtener filtros
        $estado = isset($_GET['estado']) ? $_GET['estado'] : null;
        $prioridad = isset($_GET['prioridad']) ? $_GET['prioridad'] : null;
        $area_id = isset($_GET['area_id']) ? (int)$_GET['area_id'] : null;
        $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
        $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
        
        // Obtener proyectos según permisos
        $projects = $this->project->getProjects($userId, $userRole, $userArea);
        
        // Filtrar proyectos
        if (!empty($projects)) {
            if ($estado) {
                $projects = array_filter($projects, function($project) use ($estado) {
                    return $project['estado'] === $estado;
                });
            }
            
            if ($prioridad) {
                $projects = array_filter($projects, function($project) use ($prioridad) {
                    return $project['prioridad'] === $prioridad;
                });
            }
            
            if ($area_id && ($userRole <= 2 || $userArea == $area_id)) { // Solo admin o gerentes ven todas las áreas
                $projects = array_filter($projects, function($project) use ($area_id) {
                    return $project['area_id'] == $area_id;
                });
            }
            
            if ($fecha_inicio) {
                $projects = array_filter($projects, function($project) use ($fecha_inicio) {
                    return $project['fecha_inicio'] >= $fecha_inicio;
                });
            }
            
            if ($fecha_fin) {
                $projects = array_filter($projects, function($project) use ($fecha_fin) {
                    return $project['fecha_fin'] <= $fecha_fin;
                });
            }
        }
        
        // Calcular estadísticas para el reporte
        $totalProyectos = count($projects);
        $estadisticas = [
            'total' => $totalProyectos,
            'por_estado' => [
                'Pendiente' => 0,
                'En Progreso' => 0,
                'Completado' => 0,
                'Cancelado' => 0
            ],
            'por_prioridad' => [
                'Baja' => 0,
                'Media' => 0, 
                'Alta' => 0,
                'Urgente' => 0
            ]
        ];
        
        foreach ($projects as $project) {
            // Contar por estado
            if (isset($estadisticas['por_estado'][$project['estado']])) {
                $estadisticas['por_estado'][$project['estado']]++;
            }
            
            // Contar por prioridad
            if (isset($estadisticas['por_prioridad'][$project['prioridad']])) {
                $estadisticas['por_prioridad'][$project['prioridad']]++;
            }
        }
        
        // Obtener áreas para filtros
        $areas = ($userRole <= 2) ? $this->area->getAllAreas() : [];
        
        // Generar datos para el gráfico de estados
        $chartData = [
            'estados' => [
                'labels' => ['Pendiente', 'En Progreso', 'Completado', 'Cancelado'],
                'datasets' => [
                    [
                        'data' => [
                            $estadisticas['por_estado']['Pendiente'],
                            $estadisticas['por_estado']['En Progreso'],
                            $estadisticas['por_estado']['Completado'],
                            $estadisticas['por_estado']['Cancelado']
                        ],
                        'backgroundColor' => ['#f8f9fa', '#007bff', '#28a745', '#dc3545']
                    ]
                ]
            ],
            'prioridades' => [
                'labels' => ['Baja', 'Media', 'Alta', 'Urgente'],
                'datasets' => [
                    [
                        'label' => 'Proyectos por prioridad',
                        'data' => [
                            $estadisticas['por_prioridad']['Baja'],
                            $estadisticas['por_prioridad']['Media'],
                            $estadisticas['por_prioridad']['Alta'],
                            $estadisticas['por_prioridad']['Urgente']
                        ],
                        'backgroundColor' => ['#17a2b8', '#6c757d', '#ffc107', '#dc3545']
                    ]
                ]
            ]
        ];
        
        // Cargar la vista
        include 'views/reports/projects.php';
    }
    
    /**
     * Reporte de Tareas
     */
    public function tasks() {
        // Verificar permisos: solo Administradores y Gerentes
        if ($_SESSION['user_role'] > 3) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $userArea = $_SESSION['user_area'] ?? null;
        
        // Obtener filtros
        $estado = isset($_GET['estado']) ? $_GET['estado'] : null;
        $prioridad = isset($_GET['prioridad']) ? $_GET['prioridad'] : null;
        $proyecto_id = isset($_GET['proyecto_id']) ? (int)$_GET['proyecto_id'] : null;
        $asignado_a = isset($_GET['asignado_a']) ? $_GET['asignado_a'] : null;
        $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
        $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
        
        // Obtener tareas según permisos
        $tasks = $this->task->getTasks($userId, $userRole, $userArea);
        
        // Filtrar tareas
        if (!empty($tasks)) {
            if ($estado) {
                $tasks = array_filter($tasks, function($task) use ($estado) {
                    return $task['estado'] === $estado;
                });
            }
            
            if ($prioridad) {
                $tasks = array_filter($tasks, function($task) use ($prioridad) {
                    return $task['prioridad'] === $prioridad;
                });
            }
            
            if ($proyecto_id) {
                $tasks = array_filter($tasks, function($task) use ($proyecto_id) {
                    return $task['proyecto_id'] == $proyecto_id;
                });
            }
            
            if ($asignado_a === 'sin_asignar') {
                $tasks = array_filter($tasks, function($task) {
                    return empty($task['asignado_a']);
                });
            } elseif ($asignado_a) {
                $tasks = array_filter($tasks, function($task) use ($asignado_a) {
                    return $task['asignado_a'] == $asignado_a;
                });
            }
            
            if ($fecha_inicio) {
                $tasks = array_filter($tasks, function($task) use ($fecha_inicio) {
                    return $task['fecha_inicio'] >= $fecha_inicio;
                });
            }
            
            if ($fecha_fin) {
                $tasks = array_filter($tasks, function($task) use ($fecha_fin) {
                    return $task['fecha_fin'] <= $fecha_fin;
                });
            }
        }
        
        // Calcular estadísticas para el reporte
        $totalTareas = count($tasks);
        $estadisticas = [
            'total' => $totalTareas,
            'por_estado' => [
                'Pendiente' => 0,
                'En Progreso' => 0,
                'Completado' => 0,
                'Cancelado' => 0
            ],
            'por_prioridad' => [
                'Baja' => 0,
                'Media' => 0, 
                'Alta' => 0,
                'Urgente' => 0
            ],
            'progreso_promedio' => 0,
            'proximos_vencimientos' => 0
        ];
        
        $sumaProgreso = 0;
        $hoy = date('Y-m-d');
        $proximaSemana = date('Y-m-d', strtotime('+7 days'));
        
        foreach ($tasks as $task) {
            // Contar por estado
            if (isset($estadisticas['por_estado'][$task['estado']])) {
                $estadisticas['por_estado'][$task['estado']]++;
            }
            
            // Contar por prioridad
            if (isset($estadisticas['por_prioridad'][$task['prioridad']])) {
                $estadisticas['por_prioridad'][$task['prioridad']]++;
            }
            
            // Sumar progreso
            $sumaProgreso += $task['porcentaje_completado'];
            
            // Contar próximos vencimientos
            if ($task['estado'] != 'Completado' && $task['estado'] != 'Cancelado' &&
                $task['fecha_fin'] >= $hoy && $task['fecha_fin'] <= $proximaSemana) {
                $estadisticas['proximos_vencimientos']++;
            }
        }
        
        // Calcular progreso promedio
        $estadisticas['progreso_promedio'] = $totalTareas > 0 ? round($sumaProgreso / $totalTareas) : 0;
        
        // Obtener proyectos para filtros
        $projects = $this->project->getProjects($userId, $userRole, $userArea);
        
        // Obtener usuarios para filtros
        $usuarios = ($userRole <= 2) ? $this->user->getAllActiveUsers() : [];
        
        // Generar datos para los gráficos
        $chartData = [
            'estados' => [
                'labels' => ['Pendiente', 'En Progreso', 'Completado', 'Cancelado'],
                'datasets' => [
                    [
                        'data' => [
                            $estadisticas['por_estado']['Pendiente'],
                            $estadisticas['por_estado']['En Progreso'],
                            $estadisticas['por_estado']['Completado'],
                            $estadisticas['por_estado']['Cancelado']
                        ],
                        'backgroundColor' => ['#f8f9fa', '#007bff', '#28a745', '#dc3545']
                    ]
                ]
            ],
            'prioridades' => [
                'labels' => ['Baja', 'Media', 'Alta', 'Urgente'],
                'datasets' => [
                    [
                        'label' => 'Tareas por prioridad',
                        'data' => [
                            $estadisticas['por_prioridad']['Baja'],
                            $estadisticas['por_prioridad']['Media'],
                            $estadisticas['por_prioridad']['Alta'],
                            $estadisticas['por_prioridad']['Urgente']
                        ],
                        'backgroundColor' => ['#17a2b8', '#6c757d', '#ffc107', '#dc3545']
                    ]
                ]
            ]
        ];
        
        // Cargar la vista
        include 'views/reports/tasks.php';
    }
    
    /**
     * Reporte de Usuarios
     */
    public function users() {
        // Verificar permisos: solo Administradores y Gerentes Generales
        if ($_SESSION['user_role'] > 2) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Obtener filtros
        $rol_id = isset($_GET['rol_id']) ? (int)$_GET['rol_id'] : null;
        $area_id = isset($_GET['area_id']) ? (int)$_GET['area_id'] : null;
        $department_id = isset($_GET['department_id']) ? (int)$_GET['department_id'] : null;
        $activo = isset($_GET['activo']) ? $_GET['activo'] : null;
        
        // Obtener todos los usuarios
        $users = $this->user->getAllUsers();
        
        // Filtrar usuarios
        if (!empty($users)) {
            if ($rol_id) {
                $users = array_filter($users, function($user) use ($rol_id) {
                    return $user['rol_id'] == $rol_id;
                });
            }
            
            if ($area_id) {
                $users = array_filter($users, function($user) use ($area_id) {
                    return $user['area_id'] == $area_id;
                });
            }
            
            if ($department_id) {
                $users = array_filter($users, function($user) use ($department_id) {
                    return $user['departamento_id'] == $department_id;
                });
            }
            
            if ($activo !== null) {
                $users = array_filter($users, function($user) use ($activo) {
                    return $user['activo'] == $activo;
                });
            }
        }
        
        // Calcular estadísticas para el reporte
        $totalUsuarios = count($users);
        $estadisticas = [
            'total' => $totalUsuarios,
            'por_rol' => [
                'Administrador' => 0,
                'Gerente General' => 0,
                'Gerente de Área' => 0,
                'Jefe de Departamento' => 0,
                'Colaborador' => 0
            ],
            'por_area' => [],
            'activos' => 0,
            'inactivos' => 0
        ];
        
        // Obtener áreas y departamentos para filtros y estadísticas
        $areas = $this->area->getAllAreas();
        $departments = $this->department->getAllDepartments();
        
        // Inicializar conteo por área
        foreach ($areas as $area) {
            $estadisticas['por_area'][$area['id']] = [
                'nombre' => $area['nombre'],
                'cantidad' => 0
            ];
        }
        
        foreach ($users as $user) {
            // Contar por rol
            if (isset($user['rol_nombre']) && isset($estadisticas['por_rol'][$user['rol_nombre']])) {
                $estadisticas['por_rol'][$user['rol_nombre']]++;
            }
            
            // Contar por área
            if (!empty($user['area_id']) && isset($estadisticas['por_area'][$user['area_id']])) {
                $estadisticas['por_area'][$user['area_id']]['cantidad']++;
            }
            
            // Contar activos/inactivos
            if ($user['activo']) {
                $estadisticas['activos']++;
            } else {
                $estadisticas['inactivos']++;
            }
        }
        
        // Preparar datos para gráficos
        $chartData = [
            'roles' => [
                'labels' => array_keys($estadisticas['por_rol']),
                'datasets' => [
                    [
                        'data' => array_values($estadisticas['por_rol']),
                        'backgroundColor' => ['#dc3545', '#fd7e14', '#28a745', '#17a2b8', '#6c757d']
                    ]
                ]
            ],
            'areas' => [
                'labels' => array_map(function($area) { 
                    return $area['nombre']; 
                }, $estadisticas['por_area']),
                'datasets' => [
                    [
                        'label' => 'Usuarios por área',
                        'data' => array_map(function($area) { 
                            return $area['cantidad']; 
                        }, $estadisticas['por_area']),
                        'backgroundColor' => '#007bff'
                    ]
                ]
            ],
            'estado' => [
                'labels' => ['Activos', 'Inactivos'],
                'datasets' => [
                    [
                        'data' => [$estadisticas['activos'], $estadisticas['inactivos']],
                        'backgroundColor' => ['#28a745', '#dc3545']
                    ]
                ]
            ]
        ];
        
        // Cargar la vista
        include 'views/reports/users.php';
    }
    
    /**
     * Exportar reporte de proyectos a CSV
     */
    public function exportProjectsCsv() {
        // Verificar permisos: solo Administradores y Gerentes
        if ($_SESSION['user_role'] > 3) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $userArea = $_SESSION['user_area'] ?? null;
        
        // Obtener proyectos según permisos
        $projects = $this->project->getProjects($userId, $userRole, $userArea);
        
        // Encabezados CSV
        $headers = [
            'ID', 'Título', 'Descripción', 'Área', 'Estado', 'Prioridad', 
            'Fecha Inicio', 'Fecha Fin', 'Creado Por', 'Fecha Creación'
        ];
        
        // Configurar la respuesta HTTP
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=reporte_proyectos_' . date('Y-m-d') . '.csv');
        
        // Crear archivo CSV
        $output = fopen('php://output', 'w');
        
        // Escribir BOM UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Escribir encabezados
        fputcsv($output, $headers);
        
        // Escribir datos
        foreach ($projects as $project) {
            $row = [
                $project['id'],
                $project['titulo'],
                $project['descripcion'],
                $project['area_nombre'] ?? 'Sin asignar',
                $project['estado'],
                $project['prioridad'],
                $project['fecha_inicio'],
                $project['fecha_fin'],
                $project['creador_nombre'],
                $project['fecha_creacion']
            ];
            
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Exportar reporte de tareas a CSV
     */
    public function exportTasksCsv() {
        // Verificar permisos: solo Administradores y Gerentes
        if ($_SESSION['user_role'] > 3) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $userArea = $_SESSION['user_area'] ?? null;
        
        // Obtener tareas según permisos
        $tasks = $this->task->getTasks($userId, $userRole, $userArea);
        
        // Encabezados CSV
        $headers = [
            'ID', 'Título', 'Descripción', 'Proyecto', 'Asignado a', 'Estado', 'Prioridad', 
            'Fecha Inicio', 'Fecha Fin', 'Porcentaje Completado', 'Creado Por', 'Fecha Creación'
        ];
        
        // Configurar la respuesta HTTP
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=reporte_tareas_' . date('Y-m-d') . '.csv');
        
        // Crear archivo CSV
        $output = fopen('php://output', 'w');
        
        // Escribir BOM UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Escribir encabezados
        fputcsv($output, $headers);
        
        // Escribir datos
        foreach ($tasks as $task) {
            $row = [
                $task['id'],
                $task['titulo'],
                $task['descripcion'],
                $task['proyecto_titulo'] ?? 'Sin proyecto',
                $task['asignado_nombre'] ?? 'Sin asignar',
                $task['estado'],
                $task['prioridad'],
                $task['fecha_inicio'],
                $task['fecha_fin'],
                $task['porcentaje_completado'] . '%',
                $task['creador_nombre'],
                $task['fecha_creacion']
            ];
            
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Exportar reporte de usuarios a CSV
     */
    public function exportUsersCsv() {
        // Verificar permisos: solo Administradores y Gerentes Generales
        if ($_SESSION['user_role'] > 2) {
            header('Location: index.php?controller=error&action=forbidden');
            exit;
        }
        
        // Obtener todos los usuarios
        $users = $this->user->getAllUsers();
        
        // Encabezados CSV
        $headers = [
            'ID', 'Nombre', 'Apellido', 'Email', 'Rol', 'Área', 'Departamento',
            'Estado', 'Fecha Creación', 'Última Actualización'
        ];
        
        // Configurar la respuesta HTTP
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=reporte_usuarios_' . date('Y-m-d') . '.csv');
        
        // Crear archivo CSV
        $output = fopen('php://output', 'w');
        
        // Escribir BOM UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Escribir encabezados
        fputcsv($output, $headers);
        
        // Escribir datos
        foreach ($users as $user) {
            $row = [
                $user['id'],
                $user['nombre'],
                $user['apellido'],
                $user['email'],
                $user['rol_nombre'],
                $user['area_nombre'] ?? 'Sin asignar',
                $user['departamento_nombre'] ?? 'Sin asignar',
                $user['activo'] ? 'Activo' : 'Inactivo',
                $user['fecha_creacion'],
                $user['ultima_actualizacion']
            ];
            
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
}