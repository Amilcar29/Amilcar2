<?php
/**
 * Role - Modelo para la gestión de roles
 */
class Role {
    private $db;
    private $table = 'roles';
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Obtiene todos los roles
     */
    public function getAllRoles() {
        $sql = "SELECT * FROM {$this->table} ORDER BY id";
        
        return $this->db->query($sql);
    }
    
    /**
     * Obtiene un rol por su ID
     */
    public function getRoleById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        
        $result = $this->db->query($sql, [$id]);
        
        return $result ? $result[0] : null;
    }
    
    /**
     * Crea un nuevo rol
     */
    public function createRole($data) {
        $sql = "INSERT INTO {$this->table} (nombre, descripcion) VALUES (?, ?)";
        
        $params = [
            $data['nombre'],
            $data['descripcion'] ?? null
        ];
        
        if ($this->db->execute($sql, $params)) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Actualiza un rol existente
     */
    public function updateRole($id, $data) {
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
     * Elimina un rol
     */
    public function deleteRole($id) {
        // Verificar si hay usuarios con este rol
        $sql = "SELECT COUNT(*) as count FROM usuarios WHERE rol_id = ?";
        $result = $this->db->query($sql, [$id]);
        
        if ($result && $result[0]['count'] > 0) {
            return false; // No se puede eliminar si hay usuarios con este rol
        }
        
        // Si no hay usuarios con este rol, eliminarlo
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        
        return $this->db->execute($sql, [$id]);
    }
}