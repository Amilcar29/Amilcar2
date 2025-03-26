<?php
/**
 * Department - Modelo para la gestión de departamentos
 */
class Department {
    private $db;
    private $table = 'departamentos';
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Obtiene todos los departamentos
     */
    public function getAllDepartments() {
        $sql = "SELECT d.*, a.nombre as area_nombre, 
                CONCAT(u.nombre, ' ', u.apellido) as jefe_nombre
                FROM {$this->table} d
                LEFT JOIN areas a ON d.area_id = a.id
                LEFT JOIN usuarios u ON d.jefe_id = u.id
                ORDER BY a.nombre, d.nombre";
        
        return $this->db->query($sql);
    }
    
    /**
     * Obtiene los departamentos por área
     */
    public function getDepartmentsByArea($areaId) {
        $sql = "SELECT d.*, a.nombre as area_nombre, 
                CONCAT(u.nombre, ' ', u.apellido) as jefe_nombre
                FROM {$this->table} d
                LEFT JOIN areas a ON d.area_id = a.id
                LEFT JOIN usuarios u ON d.jefe_id = u.id
                WHERE d.area_id = ?
                ORDER BY d.nombre";
        
        return $this->db->query($sql, [$areaId]);
    }
    
    /**
     * Obtiene un departamento por su ID
     */
    public function getDepartmentById($id) {
        $sql = "SELECT d.*, a.nombre as area_nombre, 
                CONCAT(u.nombre, ' ', u.apellido) as jefe_nombre
                FROM {$this->table} d
                LEFT JOIN areas a ON d.area_id = a.id
                LEFT JOIN usuarios u ON d.jefe_id = u.id
                WHERE d.id = ?";
        
        $result = $this->db->query($sql, [$id]);
        
        return $result ? $result[0] : null;
    }
    
    /**
     * Crea un nuevo departamento
     */
    public function createDepartment($data) {
        $sql = "INSERT INTO {$this->table} (nombre, descripcion, area_id, jefe_id) 
                VALUES (?, ?, ?, ?)";
        
        $params = [
            $data['nombre'],
            $data['descripcion'] ?? null,
            $data['area_id'],
            $data['jefe_id'] ?? null
        ];
        
        if ($this->db->execute($sql, $params)) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Actualiza un departamento existente
     */
    public function updateDepartment($id, $data) {
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
     * Elimina un departamento
     */
    public function deleteDepartment($id) {
        // Primero verificar si hay usuarios asociados
        $sqlCheck = "SELECT COUNT(*) as count FROM usuarios WHERE departamento_id = ?";
        $result = $this->db->query($sqlCheck, [$id]);
        
        if ($result && $result[0]['count'] > 0) {
            return false; // No se puede eliminar si hay usuarios asociados
        }
        
        // Si no hay dependencias, eliminar el departamento
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Asigna un jefe a un departamento
     */
    public function assignHead($departmentId, $userId) {
        $sql = "UPDATE {$this->table} SET jefe_id = ? WHERE id = ?";
        
        return $this->db->execute($sql, [$userId, $departmentId]);
    }
    
    /**
     * Obtiene los usuarios de un departamento
     */
    public function getDepartmentUsers($departmentId) {
        $sql = "SELECT u.*, r.nombre as rol_nombre
                FROM usuarios u
                LEFT JOIN roles r ON u.rol_id = r.id
                WHERE u.departamento_id = ? AND u.activo = 1
                ORDER BY u.nombre, u.apellido";
        
        return $this->db->query($sql, [$departmentId]);
    }
    
    /**
     * Verifica si un departamento tiene jefe asignado
     */
    public function hasHead($departmentId) {
        $sql = "SELECT jefe_id FROM {$this->table} WHERE id = ?";
        
        $result = $this->db->query($sql, [$departmentId]);
        
        return $result && !empty($result[0]['jefe_id']);
    }
    
    /**
     * Obtiene estadísticas de un departamento
     */
    public function getDepartmentStats($departmentId) {
        $stats = [
            'total_usuarios' => 0,
            'usuarios_activos' => 0
        ];
        
        // Contar usuarios
        $sqlUsers = "SELECT COUNT(*) as count FROM usuarios WHERE departamento_id = ?";
        $resultUsers = $this->db->query($sqlUsers, [$departmentId]);
        if ($resultUsers) {
            $stats['total_usuarios'] = $resultUsers[0]['count'];
        }
        
        // Contar usuarios activos
        $sqlActiveUsers = "SELECT COUNT(*) as count FROM usuarios 
                          WHERE departamento_id = ? AND activo = 1";
        $resultActiveUsers = $this->db->query($sqlActiveUsers, [$departmentId]);
        if ($resultActiveUsers) {
            $stats['usuarios_activos'] = $resultActiveUsers[0]['count'];
        }
        
        return $stats;
    }
    
    /**
     * Cambia el área de un departamento
     */
    public function changeArea($departmentId, $areaId) {
        $sql = "UPDATE {$this->table} SET area_id = ? WHERE id = ?";
        
        return $this->db->execute($sql, [$areaId, $departmentId]);
    }
    
    /**
     * Obtiene todos los departamentos para un selector
     */
    public function getDepartmentsForSelect() {
        $sql = "SELECT d.id, d.nombre, a.nombre as area_nombre
                FROM {$this->table} d
                LEFT JOIN areas a ON d.area_id = a.id
                ORDER BY a.nombre, d.nombre";
        
        $departments = $this->db->query($sql);
        
        if (!$departments) {
            return [];
        }
        
        // Formato para un selector agrupado por área
        $result = [];
        foreach ($departments as $department) {
            if (!isset($result[$department['area_nombre']])) {
                $result[$department['area_nombre']] = [];
            }
            
            $result[$department['area_nombre']][] = [
                'id' => $department['id'],
                'nombre' => $department['nombre']
            ];
        }
        
        return $result;
    }
    
    /**
     * Verifica si un departamento pertenece a un área determinada
     */
    public function isInArea($departmentId, $areaId) {
        $sql = "SELECT area_id FROM {$this->table} WHERE id = ?";
        
        $result = $this->db->query($sql, [$departmentId]);
        
        return $result && $result[0]['area_id'] == $areaId;
    }
}