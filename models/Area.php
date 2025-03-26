<?php
/**
 * Area - Modelo para la gestión de áreas de la organización
 */
class Area {
    private $db;
    private $table = 'areas';
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Obtiene todas las áreas
     */
    public function getAllAreas() {
        $sql = "SELECT a.*, CONCAT(u.nombre, ' ', u.apellido) as gerente_nombre
                FROM {$this->table} a
                LEFT JOIN usuarios u ON a.gerente_id = u.id
                ORDER BY a.nombre";
        
        return $this->db->query($sql);
    }
    
    /**
     * Obtiene un área por su ID
     */
    public function getAreaById($id) {
        $sql = "SELECT a.*, CONCAT(u.nombre, ' ', u.apellido) as gerente_nombre
                FROM {$this->table} a
                LEFT JOIN usuarios u ON a.gerente_id = u.id
                WHERE a.id = ?";
        
        $result = $this->db->query($sql, [$id]);
        
        return $result ? $result[0] : null;
    }
    
    /**
     * Crea una nueva área
     */
    public function createArea($data) {
        $sql = "INSERT INTO {$this->table} (nombre, descripcion, gerente_id) 
                VALUES (?, ?, ?)";
        
        $params = [
            $data['nombre'],
            $data['descripcion'] ?? null,
            $data['gerente_id'] ?? null
        ];
        
        if ($this->db->execute($sql, $params)) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Actualiza un área existente
     */
    public function updateArea($id, $data) {
        $fields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        $params[] = $id; // Para la cláusula WHERE
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Elimina un área
     */
    public function deleteArea($id) {
        // Primero verificar si hay departamentos asociados
        $sqlCheck = "SELECT COUNT(*) as count FROM departamentos WHERE area_id = ?";
        $result = $this->db->query($sqlCheck, [$id]);
        
        if ($result && $result[0]['count'] > 0) {
            return false; // No se puede eliminar si hay departamentos asociados
        }
        
        // Verificar si hay proyectos asociados
        $sqlCheckProjects = "SELECT COUNT(*) as count FROM proyectos WHERE area_id = ?";
        $resultProjects = $this->db->query($sqlCheckProjects, [$id]);
        
        if ($resultProjects && $resultProjects[0]['count'] > 0) {
            return false; // No se puede eliminar si hay proyectos asociados
        }
        
        // Si no hay dependencias, eliminar el área
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Asigna un gerente a un área
     */
    public function assignManager($areaId, $userId) {
        $sql = "UPDATE {$this->table} SET gerente_id = ? WHERE id = ?";
        
        return $this->db->execute($sql, [$userId, $areaId]);
    }
    
    /**
     * Obtiene los departamentos de un área
     */
    public function getAreaDepartments($areaId) {
        $sql = "SELECT d.*, CONCAT(u.nombre, ' ', u.apellido) as jefe_nombre
                FROM departamentos d
                LEFT JOIN usuarios u ON d.jefe_id = u.id
                WHERE d.area_id = ?
                ORDER BY d.nombre";
        
        return $this->db->query($sql, [$areaId]);
    }
    
    /**
     * Obtiene los usuarios de un área
     */
    public function getAreaUsers($areaId) {
        $sql = "SELECT u.*, r.nombre as rol_nombre
                FROM usuarios u
                LEFT JOIN roles r ON u.rol_id = r.id
                WHERE u.area_id = ? AND u.activo = 1
                ORDER BY u.nombre, u.apellido";
        
        return $this->db->query($sql, [$areaId]);
    }
    
    /**
     * Obtiene los proyectos de un área
     */
    public function getAreaProjects($areaId) {
        $sql = "SELECT p.*, CONCAT(u.nombre, ' ', u.apellido) as creador_nombre
                FROM proyectos p
                LEFT JOIN usuarios u ON p.creado_por = u.id
                WHERE p.area_id = ?
                ORDER BY p.fecha_inicio DESC";
        
        return $this->db->query($sql, [$areaId]);
    }
    
    /**
     * Verifica si un área tiene gerente asignado
     */
    public function hasManager($areaId) {
        $sql = "SELECT gerente_id FROM {$this->table} WHERE id = ?";
        
        $result = $this->db->query($sql, [$areaId]);
        
        return $result && !empty($result[0]['gerente_id']);
    }
    
    /**
     * Obtiene estadísticas de un área
     */
    public function getAreaStats($areaId) {
        $stats = [
            'total_departamentos' => 0,
            'total_usuarios' => 0,
            'total_proyectos' => 0,
            'proyectos_activos' => 0
        ];
        
        // Contar departamentos
        $sqlDept = "SELECT COUNT(*) as count FROM departamentos WHERE area_id = ?";
        $resultDept = $this->db->query($sqlDept, [$areaId]);
        if ($resultDept) {
            $stats['total_departamentos'] = $resultDept[0]['count'];
        }
        
        // Contar usuarios
        $sqlUsers = "SELECT COUNT(*) as count FROM usuarios WHERE area_id = ? AND activo = 1";
        $resultUsers = $this->db->query($sqlUsers, [$areaId]);
        if ($resultUsers) {
            $stats['total_usuarios'] = $resultUsers[0]['count'];
        }
        
        // Contar proyectos
        $sqlProjects = "SELECT COUNT(*) as count FROM proyectos WHERE area_id = ?";
        $resultProjects = $this->db->query($sqlProjects, [$areaId]);
        if ($resultProjects) {
            $stats['total_proyectos'] = $resultProjects[0]['count'];
        }
        
        // Contar proyectos activos
        $sqlActiveProjects = "SELECT COUNT(*) as count FROM proyectos 
                            WHERE area_id = ? 
                            AND estado IN ('Pendiente', 'En Progreso')";
        $resultActiveProjects = $this->db->query($sqlActiveProjects, [$areaId]);
        if ($resultActiveProjects) {
            $stats['proyectos_activos'] = $resultActiveProjects[0]['count'];
        }
        
        return $stats;
    }
}