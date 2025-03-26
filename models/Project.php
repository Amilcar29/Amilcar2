<?php
class Project {
    private $db;
    private $table = 'proyectos';
    private $usersProjectsTable = 'usuarios_proyectos';
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Obtiene todos los proyectos (filtrados según permisos)
     */
    public function getProjects($userId = null, $role = null, $areaId = null) {
        $sql = "SELECT p.*, a.nombre as area_nombre, 
                CONCAT(u.nombre, ' ', u.apellido) as creador_nombre
                FROM {$this->table} p
                LEFT JOIN areas a ON p.area_id = a.id
                LEFT JOIN usuarios u ON p.creado_por = u.id";
        
        // Aplica filtros según rol
        if ($role == 1 || $role == 2) {
            // Administrador o Gerente General: ve todos los proyectos
            $sql .= " ORDER BY p.fecha_inicio DESC";
        } elseif ($role == 3 && $areaId) {
            // Gerente de Área: ve proyectos de su área
            $sql .= " WHERE p.area_id = ?
                     ORDER BY p.fecha_inicio DESC";
            return $this->db->query($sql, [$areaId]);
        } elseif ($userId) {
            // Jefe o Colaborador: ve proyectos donde está asignado o es creador
            $sql .= " WHERE p.creado_por = ? 
                     OR p.id IN (
                         SELECT proyecto_id FROM {$this->usersProjectsTable} WHERE usuario_id = ?
                     )
                     ORDER BY p.fecha_inicio DESC";
            return $this->db->query($sql, [$userId, $userId]);
        }
        
        return $this->db->query($sql);
    }
    
    /**
     * Obtiene un proyecto por ID
     */
    public function getProjectById($id) {
        $sql = "SELECT p.*, a.nombre as area_nombre, 
                CONCAT(u.nombre, ' ', u.apellido) as creador_nombre
                FROM {$this->table} p
                LEFT JOIN areas a ON p.area_id = a.id
                LEFT JOIN usuarios u ON p.creado_por = u.id
                WHERE p.id = ?";
        $result = $this->db->query($sql, [$id]);
        
        return $result ? $result[0] : null;
    }
    
    /**
     * Crea un nuevo proyecto
     */
    public function createProject($data) {
        $sql = "INSERT INTO {$this->table} 
                (titulo, descripcion, fecha_inicio, fecha_fin, area_id, creado_por, estado, prioridad) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['titulo'],
            $data['descripcion'],
            $data['fecha_inicio'],
            $data['fecha_fin'],
            $data['area_id'],
            $data['creado_por'],
            $data['estado'],
            $data['prioridad']
        ];
        
        if ($this->db->execute($sql, $params)) {
            $projectId = $this->db->lastInsertId();
            
            // Asigna el creador al proyecto automáticamente
            $this->assignUserToProject($data['creado_por'], $projectId);
            
            // Si hay otros usuarios para asignar
            if (isset($data['usuarios']) && is_array($data['usuarios'])) {
                foreach ($data['usuarios'] as $userId) {
                    if ($userId != $data['creado_por']) { // Evita duplicar al creador
                        $this->assignUserToProject($userId, $projectId);
                    }
                }
            }
            
            return $projectId;
        }
        
        return false;
    }
    
    /**
     * Actualiza un proyecto existente
     */
    public function updateProject($id, $data) {
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
            if ($key !== 'id' && $key !== 'usuarios') {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        $params[] = $id; // Para la cláusula WHERE
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Elimina un proyecto
     */
    public function deleteProject($id) {
        // Verificar si el usuario tiene permisos para eliminar
        if (!in_array($_SESSION['user_role'], [1, 2])) {
            return false;
        }
        
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Asigna un usuario a un proyecto
     */
    public function assignUserToProject($userId, $projectId) {
        // Verifica si ya está asignado
        $checkSql = "SELECT id FROM {$this->usersProjectsTable} 
                    WHERE usuario_id = ? AND proyecto_id = ?";
        $exists = $this->db->query($checkSql, [$userId, $projectId]);
        
        if (!$exists) {
            $sql = "INSERT INTO {$this->usersProjectsTable} 
                    (usuario_id, proyecto_id) VALUES (?, ?)";
            return $this->db->execute($sql, [$userId, $projectId]);
        }
        
        return true; // Ya estaba asignado
    }
    
    /**
     * Elimina la asignación de un usuario a un proyecto
     */
    public function removeUserFromProject($userId, $projectId) {
        $sql = "DELETE FROM {$this->usersProjectsTable} 
                WHERE usuario_id = ? AND proyecto_id = ?";
        return $this->db->execute($sql, [$userId, $projectId]);
    }
    
    /**
     * Obtiene todos los usuarios asignados a un proyecto
     */
    public function getProjectUsers($projectId) {
        $sql = "SELECT u.id, u.nombre, u.apellido, u.email, r.nombre as rol, 
                a.nombre as area, d.nombre as departamento
                FROM usuarios u
                INNER JOIN {$this->usersProjectsTable} up ON u.id = up.usuario_id
                LEFT JOIN roles r ON u.rol_id = r.id
                LEFT JOIN areas a ON u.area_id = a.id
                LEFT JOIN departamentos d ON u.departamento_id = d.id
                WHERE up.proyecto_id = ?
                ORDER BY u.nombre, u.apellido";
        
        return $this->db->query($sql, [$projectId]);
    }
    
    /**
     * Verifica si un usuario tiene acceso a un proyecto específico
     */
    public function userHasAccessToProject($userId, $projectId, $role = null, $areaId = null) {
        // Administradores y Gerentes Generales tienen acceso a todo
        if ($role == 1 || $role == 2) {
            return true;
        }
        
        // Gerentes de Área: acceso a proyectos de su área
        if ($role == 3 && $areaId) {
            $sql = "SELECT id FROM {$this->table} 
                    WHERE id = ? AND area_id = ?";
            $result = $this->db->query($sql, [$projectId, $areaId]);
            
            if ($result) {
                return true;
            }
        }
        
        // Verificar si es creador del proyecto
        $sql = "SELECT id FROM {$this->table} 
                WHERE id = ? AND creado_por = ?";
        $result = $this->db->query($sql, [$projectId, $userId]);
        
        if ($result) {
            return true;
        }
        
        // Verificar si está asignado al proyecto
        $sql = "SELECT id FROM {$this->usersProjectsTable} 
                WHERE proyecto_id = ? AND usuario_id = ?";
        $result = $this->db->query($sql, [$projectId, $userId]);
        
        return $result ? true : false;
    }
    
    /**
     * Obtiene estadísticas de proyectos para el dashboard
     */
    public function getProjectStats($userId = null, $role = null, $areaId = null) {
        $stats = [
            'total' => 0,
            'pendientes' => 0,
            'en_progreso' => 0,
            'completados' => 0,
            'cancelados' => 0,
            'por_prioridad' => [
                'baja' => 0,
                'media' => 0,
                'alta' => 0,
                'urgente' => 0
            ]
        ];
        
        // Obtiene proyectos según filtros de permisos
        $projects = $this->getProjects($userId, $role, $areaId);
        
        if (!$projects) {
            return $stats;
        }
        
        $stats['total'] = count($projects);
        
        foreach ($projects as $project) {
            // Conteo por estado
            switch ($project['estado']) {
                case 'Pendiente':
                    $stats['pendientes']++;
                    break;
                case 'En Progreso':
                    $stats['en_progreso']++;
                    break;
                case 'Completado':
                    $stats['completados']++;
                    break;
                case 'Cancelado':
                    $stats['cancelados']++;
                    break;
            }
            
            // Conteo por prioridad
            switch ($project['prioridad']) {
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
        }
        
        return $stats;
    }
}