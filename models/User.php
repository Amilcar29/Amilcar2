<?php
/**
 * User - Modelo para la gestión de usuarios
 */
class User {
    private $db;
    private $table = 'usuarios';
    private $resetTokensTable = 'reset_tokens';
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Obtiene un usuario por su ID
     */
    public function getUserById($id) {
        $sql = "SELECT u.*, r.nombre as rol_nombre, 
                a.nombre as area_nombre, d.nombre as departamento_nombre
                FROM {$this->table} u
                LEFT JOIN roles r ON u.rol_id = r.id
                LEFT JOIN areas a ON u.area_id = a.id
                LEFT JOIN departamentos d ON u.departamento_id = d.id
                WHERE u.id = ?";
        
        $result = $this->db->query($sql, [$id]);
        
        return $result ? $result[0] : null;
    }
    
    /**
     * Obtiene un usuario por su email
     */
    public function getUserByEmail($email) {
        $sql = "SELECT u.*, r.nombre as rol_nombre, 
                a.nombre as area_nombre, d.nombre as departamento_nombre
                FROM {$this->table} u
                LEFT JOIN roles r ON u.rol_id = r.id
                LEFT JOIN areas a ON u.area_id = a.id
                LEFT JOIN departamentos d ON u.departamento_id = d.id
                WHERE u.email = ?";
        
        $result = $this->db->query($sql, [$email]);
        
        return $result ? $result[0] : null;
    }
    
    /**
     * Obtiene todos los usuarios
     */
    public function getAllUsers() {
        $sql = "SELECT u.*, r.nombre as rol_nombre, 
                a.nombre as area_nombre, d.nombre as departamento_nombre
                FROM {$this->table} u
                LEFT JOIN roles r ON u.rol_id = r.id
                LEFT JOIN areas a ON u.area_id = a.id
                LEFT JOIN departamentos d ON u.departamento_id = d.id
                ORDER BY u.nombre, u.apellido";
        
        return $this->db->query($sql);
    }
    
    /**
     * Obtiene todos los usuarios activos
     */
    public function getAllActiveUsers() {
        $sql = "SELECT u.*, r.nombre as rol_nombre, 
                a.nombre as area_nombre, d.nombre as departamento_nombre
                FROM {$this->table} u
                LEFT JOIN roles r ON u.rol_id = r.id
                LEFT JOIN areas a ON u.area_id = a.id
                LEFT JOIN departamentos d ON u.departamento_id = d.id
                WHERE u.activo = 1
                ORDER BY u.nombre, u.apellido";
        
        return $this->db->query($sql);
    }
    
    /**
     * Obtiene usuarios por rol
     */
    public function getUsersByRole($roleId) {
        $sql = "SELECT u.*, r.nombre as rol_nombre, 
                a.nombre as area_nombre, d.nombre as departamento_nombre
                FROM {$this->table} u
                LEFT JOIN roles r ON u.rol_id = r.id
                LEFT JOIN areas a ON u.area_id = a.id
                LEFT JOIN departamentos d ON u.departamento_id = d.id
                WHERE u.rol_id = ? AND u.activo = 1
                ORDER BY u.nombre, u.apellido";
        
        return $this->db->query($sql, [$roleId]);
    }
    
    /**
     * Obtiene usuarios por área
     */
    public function getUsersByArea($areaId) {
        $sql = "SELECT u.*, r.nombre as rol_nombre, 
                a.nombre as area_nombre, d.nombre as departamento_nombre
                FROM {$this->table} u
                LEFT JOIN roles r ON u.rol_id = r.id
                LEFT JOIN areas a ON u.area_id = a.id
                LEFT JOIN departamentos d ON u.departamento_id = d.id
                WHERE u.area_id = ? AND u.activo = 1
                ORDER BY u.nombre, u.apellido";
        
        return $this->db->query($sql, [$areaId]);
    }
    
    /**
     * Obtiene usuarios por departamento
     */
    public function getUsersByDepartment($departmentId) {
        $sql = "SELECT u.*, r.nombre as rol_nombre, 
                a.nombre as area_nombre, d.nombre as departamento_nombre
                FROM {$this->table} u
                LEFT JOIN roles r ON u.rol_id = r.id
                LEFT JOIN areas a ON u.area_id = a.id
                LEFT JOIN departamentos d ON u.departamento_id = d.id
                WHERE u.departamento_id = ? AND u.activo = 1
                ORDER BY u.nombre, u.apellido";
        
        return $this->db->query($sql, [$departmentId]);
    }
    
    /**
     * Crea un nuevo usuario
     */
    public function createUser($data) {
        // Hash de la contraseña
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO {$this->table} 
                (nombre, apellido, email, password, rol_id, area_id, departamento_id, activo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['nombre'],
            $data['apellido'],
            $data['email'],
            $passwordHash,
            $data['rol_id'],
            $data['area_id'] ?? null,
            $data['departamento_id'] ?? null,
            isset($data['activo']) ? $data['activo'] : 1
        ];
        
        if ($this->db->execute($sql, $params)) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Actualiza un usuario existente
     */
    public function updateUser($id, $data) {
        $fields = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            // No actualizar el password por este método
            if ($key !== 'id' && $key !== 'password') {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        $params[] = $id; // Para la cláusula WHERE
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Cambia la contraseña de un usuario
     */
    public function changePassword($id, $newPassword) {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $sql = "UPDATE {$this->table} SET password = ? WHERE id = ?";
        
        return $this->db->execute($sql, [$passwordHash, $id]);
    }
    
    /**
     * Activa o desactiva un usuario
     */
    public function toggleStatus($id, $active = true) {
        $sql = "UPDATE {$this->table} SET activo = ? WHERE id = ?";
        
        return $this->db->execute($sql, [$active ? 1 : 0, $id]);
    }
    
    /**
     * Registra el último login del usuario
     */
    public function logLogin($id) {
        $sql = "UPDATE {$this->table} SET ultima_conexion = NOW() WHERE id = ?";
        
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Guarda un token de restablecimiento de contraseña
     */
    public function saveResetToken($userId, $token, $expires) {
        // Verificar si la tabla existe, si no, crearla
        if (!$this->db->tableExists($this->resetTokensTable)) {
            $this->createResetTokensTable();
        }
        
        // Eliminar tokens anteriores del usuario
        $sql = "DELETE FROM {$this->resetTokensTable} WHERE usuario_id = ?";
        $this->db->execute($sql, [$userId]);
        
        // Guardar nuevo token
        $sql = "INSERT INTO {$this->resetTokensTable} 
                (usuario_id, token, fecha_expiracion) 
                VALUES (?, ?, ?)";
        
        return $this->db->execute($sql, [$userId, $token, $expires]);
    }
    
    /**
     * Verifica un token de restablecimiento
     */
    public function verifyResetToken($token) {
        // Verificar si la tabla existe
        if (!$this->db->tableExists($this->resetTokensTable)) {
            return false;
        }
        
        $sql = "SELECT rt.*, u.email 
                FROM {$this->resetTokensTable} rt
                JOIN {$this->table} u ON rt.usuario_id = u.id
                WHERE rt.token = ? AND rt.fecha_expiracion > NOW()";
        
        $result = $this->db->query($sql, [$token]);
        
        return $result ? $result[0] : null;
    }
    
    /**
     * Elimina un token de restablecimiento
     */
    public function deleteResetToken($token) {
        // Verificar si la tabla existe
        if (!$this->db->tableExists($this->resetTokensTable)) {
            return false;
        }
        
        $sql = "DELETE FROM {$this->resetTokensTable} WHERE token = ?";
        
        return $this->db->execute($sql, [$token]);
    }
    
    /**
     * Crea la tabla de tokens de restablecimiento si no existe
     */
    private function createResetTokensTable() {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->resetTokensTable} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL,
                token VARCHAR(255) NOT NULL,
                fecha_expiracion DATETIME NOT NULL,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (usuario_id) REFERENCES {$this->table}(id) ON DELETE CASCADE
            )";
        
        return $this->db->execute($sql);
    }
    
    /**
     * Obtiene usuarios filtrados con paginación
     * 
     * @param array $filters Condiciones de filtrado
     * @param array $params Parámetros para las condiciones
     * @param int $offset Inicio de la paginación
     * @param int $limit Límite de registros
     * @return array Lista de usuarios filtrados
     */
    public function getFilteredUsers($filters = [], $params = [], $offset = 0, $limit = 10) {
        $sql = "SELECT u.*, r.nombre as rol_nombre, 
                a.nombre as area_nombre, d.nombre as departamento_nombre
                FROM {$this->table} u
                LEFT JOIN roles r ON u.rol_id = r.id
                LEFT JOIN areas a ON u.area_id = a.id
                LEFT JOIN departamentos d ON u.departamento_id = d.id";
        
        // Agrega condiciones si existen
        if (!empty($filters)) {
            $sql .= " WHERE " . implode(' AND ', $filters);
        }
        
        $sql .= " ORDER BY u.nombre, u.apellido";
        
        // Agrega paginación
        $sql .= " LIMIT ?, ?";
        
        // Añade parámetros de paginación
        $params[] = $offset;
        $params[] = $limit;
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Cuenta el número total de usuarios que cumplen con los filtros
     * 
     * @param array $filters Condiciones de filtrado
     * @param array $params Parámetros para las condiciones
     * @return int Número total de usuarios
     */
    public function countFilteredUsers($filters = [], $params = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} u";
        
        // Agrega condiciones si existen
        if (!empty($filters)) {
            $sql .= " WHERE " . implode(' AND ', $filters);
        }
        
        $result = $this->db->query($sql, $params);
        
        return $result ? $result[0]['total'] : 0;
    }
    
    /**
     * Obtiene todos los roles disponibles
     * 
     * @return array Lista de roles
     */
    public function getRoles() {
        $sql = "SELECT * FROM roles ORDER BY id";
        
        return $this->db->query($sql);
    }
}