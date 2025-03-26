<?php
class Task {
    private $db;
    private $table = 'tareas';
    private $historyTable = 'historial_tareas';
    private $commentsTable = 'comentarios_tareas';
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Obtiene todas las tareas (filtradas según permisos)
     */
    public function getTasks($userId = null, $role = null, $areaId = null, $projectId = null) {
        $sql = "SELECT t.*, p.titulo as proyecto_titulo, 
                CONCAT(u.nombre, ' ', u.apellido) as asignado_nombre,
                CONCAT(c.nombre, ' ', c.apellido) as creador_nombre
                FROM {$this->table} t
                LEFT JOIN proyectos p ON t.proyecto_id = p.id
                LEFT JOIN usuarios u ON t.asignado_a = u.id
                LEFT JOIN usuarios c ON t.creado_por = c.id";
        
        $params = [];
        $whereConditions = [];
        
        // Filtra por proyecto si se especifica
        if ($projectId) {
            $whereConditions[] = "t.proyecto_id = ?";
            $params[] = $projectId;
        }
        
        // Aplica filtros según rol
        if ($role == 1 || $role == 2) {
            // Administrador o Gerente General: ve todas las tareas
        } elseif ($role == 3 && $areaId) {
            // Gerente de Área: ve tareas de proyectos de su área
            $whereConditions[] = "p.area_id = ?";
            $params[] = $areaId;
        } elseif ($role == 4) {
            // Jefe de Departamento: ve tareas donde está asignado o es creador
            $whereConditions[] = "(t.asignado_a = ? OR t.creado_por = ?)";
            $params[] = $userId;
            $params[] = $userId;
        } elseif ($role == 5) {
            // Colaborador: solo ve sus tareas asignadas
            $whereConditions[] = "t.asignado_a = ?";
            $params[] = $userId;
        }
        
        // Agrega condiciones WHERE si existen
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        $sql .= " ORDER BY t.fecha_inicio ASC";
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Obtiene una tarea por ID
     */
    public function getTaskById($id) {
        $sql = "SELECT t.*, p.titulo as proyecto_titulo, p.area_id as proyecto_area_id,
                CONCAT(u.nombre, ' ', u.apellido) as asignado_nombre,
                CONCAT(c.nombre, ' ', c.apellido) as creador_nombre
                FROM {$this->table} t
                LEFT JOIN proyectos p ON t.proyecto_id = p.id
                LEFT JOIN usuarios u ON t.asignado_a = u.id
                LEFT JOIN usuarios c ON t.creado_por = c.id
                WHERE t.id = ?";
        $result = $this->db->query($sql, [$id]);
        
        return $result ? $result[0] : null;
    }
    
    /**
     * Crea una nueva tarea
     */
    public function createTask($data) {
        $sql = "INSERT INTO {$this->table} 
                (titulo, descripcion, proyecto_id, asignado_a, creado_por, 
                 fecha_inicio, fecha_fin, estado, prioridad, porcentaje_completado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['titulo'],
            $data['descripcion'],
            $data['proyecto_id'],
            $data['asignado_a'],
            $data['creado_por'],
            $data['fecha_inicio'],
            $data['fecha_fin'],
            $data['estado'],
            $data['prioridad'],
            $data['porcentaje_completado'] ?? 0
        ];
        
        if ($this->db->execute($sql, $params)) {
            $taskId = $this->db->lastInsertId();
            
            // Crea notificación para el usuario asignado
            if (!empty($data['asignado_a'])) {
                $this->createTaskNotification(
                    $data['asignado_a'],
                    "Se te ha asignado una nueva tarea: " . $data['titulo'],
                    $taskId
                );
            }
            
            return $taskId;
        }
        
        return false;
    }
    
    /**
     * Actualiza una tarea existente
     */
    public function updateTask($id, $data, $userId) {
        $oldTask = $this->getTaskById($id);
        
        if (!$oldTask) {
            return false;
        }
        
        // Verificar si el usuario tiene permisos para editar fechas
        if (isset($data['fecha_inicio']) && isset($data['fecha_fin'])) {
            if (!in_array($_SESSION['user_role'], [1, 2])) {
                // Quitar fechas del array de datos si no es Admin o Gerente General
                unset($data['fecha_inicio']);
                unset($data['fecha_fin']);
            }
        }
        
        $fields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                $fields[] = "$key = ?";
                $params[] = $value;
                
                // Registra cambios en el historial
                if (isset($oldTask[$key]) && $oldTask[$key] != $value) {
                    $this->addTaskHistory($id, $userId, $key, $oldTask[$key], $value);
                }
            }
        }
        
        $params[] = $id; // Para la cláusula WHERE
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $updated = $this->db->execute($sql, $params);
        
        // Si se cambió la asignación, notificar al nuevo asignado
        if (isset($data['asignado_a']) && $data['asignado_a'] != $oldTask['asignado_a']) {
            $this->createTaskNotification(
                $data['asignado_a'],
                "Se te ha asignado la tarea: " . $oldTask['titulo'],
                $id
            );
        }
        
        // Si se actualizó el estado, notificar al creador
        if (isset($data['estado']) && $data['estado'] != $oldTask['estado']) {
            $this->createTaskNotification(
                $oldTask['creado_por'],
                "La tarea '" . $oldTask['titulo'] . "' ha cambiado a estado: " . $data['estado'],
                $id
            );
        }
        
        return $updated;
    }
    
    /**
     * Elimina una tarea
     */
    public function deleteTask($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Agrega registro al historial de cambios de la tarea
     */
    public function addTaskHistory($taskId, $userId, $field, $oldValue, $newValue) {
        $sql = "INSERT INTO {$this->historyTable} 
                (tarea_id, usuario_id, campo_modificado, valor_anterior, valor_nuevo) 
                VALUES (?, ?, ?, ?, ?)";
        
        return $this->db->execute($sql, [
            $taskId, $userId, $field, $oldValue, $newValue
        ]);
    }
    
    /**
     * Obtiene el historial de cambios de una tarea
     */
    public function getTaskHistory($taskId) {
        $sql = "SELECT h.*, CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre
                FROM {$this->historyTable} h
                JOIN usuarios u ON h.usuario_id = u.id
                WHERE h.tarea_id = ?
                ORDER BY h.fecha_modificacion DESC";
        
        return $this->db->query($sql, [$taskId]);
    }
    
    /**
     * Agrega un comentario a una tarea
     */
    public function addTaskComment($taskId, $userId, $comment) {
        $sql = "INSERT INTO {$this->commentsTable} 
                (tarea_id, usuario_id, comentario) 
                VALUES (?, ?, ?)";
        
        if ($this->db->execute($sql, [$taskId, $userId, $comment])) {
            // Obtiene datos de la tarea para la notificación
            $task = $this->getTaskById($taskId);
            
            // Notifica al creador y al asignado (si son diferentes al comentarista)
            $notifyUsers = [];
            
            if ($task['creado_por'] != $userId) {
                $notifyUsers[] = $task['creado_por'];
            }
            
            if ($task['asignado_a'] && $task['asignado_a'] != $userId && $task['asignado_a'] != $task['creado_por']) {
                $notifyUsers[] = $task['asignado_a'];
            }
            
            foreach ($notifyUsers as $notifyUserId) {
                $this->createTaskNotification(
                    $notifyUserId,
                    "Nuevo comentario en la tarea: " . $task['titulo'],
                    $taskId
                );
            }
            
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Obtiene los comentarios de una tarea
     */
    public function getTaskComments($taskId) {
        $sql = "SELECT c.*, CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre
                FROM {$this->commentsTable} c
                JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.tarea_id = ?
                ORDER BY c.fecha_creacion ASC";
        
        return $this->db->query($sql, [$taskId]);
    }
    
    /**
     * Verifica si un usuario tiene acceso a una tarea específica
     */
    public function userHasAccessToTask($userId, $taskId, $role = null, $areaId = null) {
        // Administradores y Gerentes Generales tienen acceso a todo
        if ($role == 1 || $role == 2) {
            return true;
        }
        
        $task = $this->getTaskById($taskId);
        
        if (!$task) {
            return false;
        }
        
        // Gerentes de Área: acceso a tareas de proyectos de su área
        if ($role == 3 && $areaId && $task['proyecto_area_id'] == $areaId) {
            return true;
        }
        
        // Jefe de Departamento: acceso a tareas donde está asignado o es creador
        if ($role == 4 && ($task['asignado_a'] == $userId || $task['creado_por'] == $userId)) {
            return true;
        }
        
        // Colaborador: acceso solo a sus tareas asignadas
        if ($role == 5 && $task['asignado_a'] == $userId) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Crea una notificación relacionada con una tarea
     */
    private function createTaskNotification($userId, $message, $taskId) {
        $sql = "INSERT INTO notificaciones (usuario_id, mensaje, tipo, referencia_id) 
                VALUES (?, ?, 'tarea', ?)";
        
        return $this->db->execute($sql, [$userId, $message, $taskId]);
    }
    
    /**
     * Obtiene estadísticas de tareas para el dashboard
     */
    public function getTaskStats($userId = null, $role = null, $areaId = null) {
        $stats = [
            'total' => 0,
            'pendientes' => 0,
            'en_progreso' => 0,
            'completadas' => 0,
            'canceladas' => 0,
            'por_prioridad' => [
                'baja' => 0,
                'media' => 0,
                'alta' => 0,
                'urgente' => 0
            ],
            'proximos_vencimientos' => []
        ];
        
        // Obtiene tareas según filtros de permisos
        $tasks = $this->getTasks($userId, $role, $areaId);
        
        if (!$tasks) {
            return $stats;
        }
        
        $stats['total'] = count($tasks);
        $today = date('Y-m-d');
        $nextWeek = date('Y-m-d', strtotime('+7 days'));
        
        foreach ($tasks as $task) {
            // Conteo por estado
            switch ($task['estado']) {
                case 'Pendiente':
                    $stats['pendientes']++;
                    break;
                case 'En Progreso':
                    $stats['en_progreso']++;
                    break;
                case 'Completado':
                    $stats['completadas']++;
                    break;
                case 'Cancelado':
                    $stats['canceladas']++;
                    break;
            }
            
            // Conteo por prioridad
            switch ($task['prioridad']) {
                case 'Baja':
                    $stats['por_prioridad']['baja']++;
                    break;
                case 'Media':
                    $stats['por_prioridad']['media']++;
                    break;
                case 'Alta':
                    $stats['por_prioridad']['alta']++;
                    break;
                case 'Urgente':
                    $stats['por_prioridad']['urgente']++;
                    break;
            }
            
            // Tareas que vencen próximamente (en la próxima semana)
            if ($task['estado'] != 'Completado' && $task['estado'] != 'Cancelado') {
                if ($task['fecha_fin'] >= $today && $task['fecha_fin'] <= $nextWeek) {
                    $stats['proximos_vencimientos'][] = [
                        'id' => $task['id'],
                        'titulo' => $task['titulo'],
                        'fecha_fin' => $task['fecha_fin'],
                        'prioridad' => $task['prioridad'],
                        'dias_restantes' => floor((strtotime($task['fecha_fin']) - strtotime($today)) / (60 * 60 * 24))
                    ];
                }
            }
        }
        
        // Ordena por fecha de vencimiento ascendente
        usort($stats['proximos_vencimientos'], function($a, $b) {
            return strtotime($a['fecha_fin']) - strtotime($b['fecha_fin']);
        });
        
        return $stats;
    }
}