<?php
class Notification {
    private $db;
    private $table = 'notificaciones';
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Crea una nueva notificación
     * 
     * @param int $userId ID del usuario destinatario
     * @param string $message Mensaje de la notificación
     * @param string $type Tipo de notificación (proyecto, tarea, sistema)
     * @param int|null $referenceId ID de referencia (proyecto o tarea)
     * @return int|bool ID de la notificación creada o false si falla
     */
    public function create($userId, $message, $type = 'sistema', $referenceId = null) {
        $sql = "INSERT INTO {$this->table} (usuario_id, mensaje, tipo, referencia_id) 
                VALUES (?, ?, ?, ?)";
        
        if ($this->db->execute($sql, [$userId, $message, $type, $referenceId])) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Obtiene notificaciones de un usuario
     * 
     * @param int $userId ID del usuario
     * @param int $limit Límite de notificaciones a obtener
     * @param bool $unreadOnly Obtener solo notificaciones no leídas
     * @return array Lista de notificaciones
     */
    public function getUserNotifications($userId, $limit = 10, $unreadOnly = false) {
        $sql = "SELECT * FROM {$this->table} WHERE usuario_id = ?";
        
        if ($unreadOnly) {
            $sql .= " AND leida = 0";
        }
        
        $sql .= " ORDER BY fecha_creacion DESC LIMIT ?";
        
        return $this->db->query($sql, [$userId, $limit]);
    }
    
    /**
     * Marca una notificación como leída
     * 
     * @param int $notificationId ID de la notificación
     * @param int $userId ID del usuario (para seguridad)
     * @return bool True si se actualiza correctamente
     */
    public function markAsRead($notificationId, $userId) {
        $sql = "UPDATE {$this->table} SET leida = 1 
                WHERE id = ? AND usuario_id = ?";
        
        return $this->db->execute($sql, [$notificationId, $userId]);
    }
    
    /**
     * Marca todas las notificaciones de un usuario como leídas
     * 
     * @param int $userId ID del usuario
     * @return bool True si se actualiza correctamente
     */
    public function markAllAsRead($userId) {
        $sql = "UPDATE {$this->table} SET leida = 1 
                WHERE usuario_id = ? AND leida = 0";
        
        return $this->db->execute($sql, [$userId]);
    }
    
    /**
     * Elimina una notificación
     * 
     * @param int $notificationId ID de la notificación
     * @param int $userId ID del usuario (para seguridad)
     * @return bool True si se elimina correctamente
     */
    public function delete($notificationId, $userId) {
        $sql = "DELETE FROM {$this->table} 
                WHERE id = ? AND usuario_id = ?";
        
        return $this->db->execute($sql, [$notificationId, $userId]);
    }
    
    /**
     * Cuenta las notificaciones no leídas de un usuario
     * 
     * @param int $userId ID del usuario
     * @return int Número de notificaciones no leídas
     */
    public function countUnread($userId) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE usuario_id = ? AND leida = 0";
        
        $result = $this->db->query($sql, [$userId]);
        
        return $result ? $result[0]['count'] : 0;
    }
    
    /**
     * Crea notificaciones para cambios en proyectos
     * 
     * @param array $project Datos del proyecto
     * @param string $action Acción realizada (crear, actualizar, eliminar)
     * @param array $users Usuarios a notificar
     */
    public function notifyProjectChange($project, $action, $users) {
        $message = '';
        $type = 'proyecto';
        
        switch ($action) {
            case 'create':
                $message = "Se ha creado un nuevo proyecto: {$project['titulo']}";
                break;
            case 'update':
                $message = "Se ha actualizado el proyecto: {$project['titulo']}";
                break;
            case 'delete':
                $message = "Se ha eliminado el proyecto: {$project['titulo']}";
                break;
            case 'assign':
                $message = "Has sido asignado al proyecto: {$project['titulo']}";
                break;
        }
        
        foreach ($users as $userId) {
            $this->create($userId, $message, $type, $project['id']);
        }
    }
    
    /**
     * Crea notificaciones para cambios en tareas
     * 
     * @param array $task Datos de la tarea
     * @param string $action Acción realizada (crear, actualizar, completar, etc.)
     * @param array $users Usuarios a notificar
     */
    public function notifyTaskChange($task, $action, $users) {
        $message = '';
        $type = 'tarea';
        
        switch ($action) {
            case 'create':
                $message = "Se ha creado una nueva tarea: {$task['titulo']}";
                break;
            case 'update':
                $message = "Se ha actualizado la tarea: {$task['titulo']}";
                break;
            case 'status':
                $message = "La tarea '{$task['titulo']}' ha cambiado a estado: {$task['estado']}";
                break;
            case 'assign':
                $message = "Te han asignado la tarea: {$task['titulo']}";
                break;
            case 'comment':
                $message = "Nuevo comentario en la tarea: {$task['titulo']}";
                break;
            case 'complete':
                $message = "La tarea '{$task['titulo']}' ha sido marcada como completada";
                break;
            case 'duedate':
                $message = "La tarea '{$task['titulo']}' vence pronto";
                break;
        }
        
        foreach ($users as $userId) {
            $this->create($userId, $message, $type, $task['id']);
        }
    }
    
    /**
     * Envía notificaciones por correo electrónico (opcional)
     * 
     * @param string $email Correo del destinatario
     * @param string $subject Asunto del correo
     * @param string $message Mensaje del correo
     * @return bool True si se envía correctamente
     */
    public function sendEmailNotification($email, $subject, $message) {
        // Cabeceras para email HTML
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: noreply@sistemaproyectos.com\r\n";
        
        // Envía el email
        return mail($email, $subject, $message, $headers);
    }
}