<?php
/**
 * Database - Clase para la conexión y operaciones con la base de datos
 */
class Database {
    // Configuración de la conexión
    private $host = 'sdb-84.hosting.stackcp.net';
    private $db_name = 'sistema_proyectos-353039365e0e';
    private $username = 'nempresa';
    private $password = 'Nempresa$#2q';
    private $conn;
    
    /**
     * Constructor - Establece la conexión a la base de datos
     */
    public function __construct() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->db_name,
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                )
            );
        } catch(PDOException $e) {
            echo 'Error de conexión: ' . $e->getMessage();
        }
    }
    
    /**
     * Ejecuta una consulta SQL con parámetros
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parámetros para la consulta
     * @return array|false Resultados de la consulta o false en caso de error
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            
            // Si es una consulta SELECT, devuelve los resultados
            if (stripos($sql, 'SELECT') === 0) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return true;
        } catch(PDOException $e) {
            error_log('Error en la consulta: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ejecuta una consulta SQL sin devolver resultados
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parámetros para la consulta
     * @return bool True si se ejecuta correctamente, False en caso de error
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($params);
        } catch(PDOException $e) {
            error_log('Error en la ejecución: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene el ID del último registro insertado
     * 
     * @return int ID del último registro insertado
     */
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
    
    /**
     * Inicia una transacción
     * 
     * @return bool True si se inicia correctamente
     */
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }
    
    /**
     * Confirma una transacción
     * 
     * @return bool True si se confirma correctamente
     */
    public function commit() {
        return $this->conn->commit();
    }
    
    /**
     * Revierte una transacción
     * 
     * @return bool True si se revierte correctamente
     */
    public function rollback() {
        return $this->conn->rollBack();
    }
    
    /**
     * Verifica si una tabla existe en la base de datos
     * 
     * @param string $table Nombre de la tabla
     * @return bool True si la tabla existe
     */
    public function tableExists($table) {
        $result = $this->query("SHOW TABLES LIKE ?", [$table]);
        return !empty($result);
    }
}