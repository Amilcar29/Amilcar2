<?php
/**
 * AccessControl - Gestiona el control de acceso basado en roles
 * 
 * Esta clase implementa la lógica para controlar el acceso 
 * de usuarios a diferentes recursos según su rol en el sistema
 */
class AccessControl {
    // Constantes para roles
    const ROLE_ADMIN = 1;
    const ROLE_GENERAL_MANAGER = 2;
    const ROLE_AREA_MANAGER = 3;
    const ROLE_DEPARTMENT_HEAD = 4;
    const ROLE_COLLABORATOR = 5;
    
    /**
     * Verifica si el usuario tiene permiso para acceder a un recurso
     * 
     * @param int $requiredRole El rol mínimo requerido para acceder
     * @param int $userRole El rol del usuario actual
     * @return bool True si tiene acceso, False si no
     */
    public static function hasAccess($requiredRole, $userRole) {
        return $userRole <= $requiredRole;
    }
    
    /**
     * Verifica si el usuario tiene permisos de administración
     * (Administrador o Gerente General)
     * 
     * @param int $userRole El rol del usuario
     * @return bool True si tiene permisos de administración
     */
    public static function isAdmin($userRole) {
        return $userRole == self::ROLE_ADMIN || $userRole == self::ROLE_GENERAL_MANAGER;
    }
    
    /**
     * Verifica si un usuario puede gestionar fechas de proyectos y tareas
     * 
     * @param int $userRole El rol del usuario
     * @return bool True si puede gestionar fechas
     */
    public static function canManageDates($userRole) {
        return self::isAdmin($userRole);
    }
    
    /**
     * Verifica si un usuario puede eliminar proyectos o tareas
     * 
     * @param int $userRole El rol del usuario
     * @return bool True si puede eliminar
     */
    public static function canDelete($userRole) {
        return self::isAdmin($userRole);
    }
    
    /**
     * Determina si un usuario puede ver un proyecto específico
     * 
     * @param array $project Datos del proyecto
     * @param int $userId ID del usuario
     * @param int $userRole Rol del usuario
     * @param int|null $userAreaId ID del área del usuario
     * @param array $projectUsers Lista de usuarios asignados al proyecto
     * @return bool True si puede ver el proyecto
     */
    public static function canViewProject($project, $userId, $userRole, $userAreaId = null, $projectUsers = []) {
        // Administrador o Gerente General: acceso total
        if (self::isAdmin($userRole)) {
            return true;
        }
        
        // Gerente de Área: acceso a proyectos de su área
        if ($userRole == self::ROLE_AREA_MANAGER && $userAreaId == $project['area_id']) {
            return true;
        }
        
        // Jefe de Departamento o Colaborador: acceso si es creador o está asignado
        if ($project['creado_por'] == $userId) {
            return true;
        }
        
        // Verificar si está asignado al proyecto
        foreach ($projectUsers as $projectUser) {
            if ($projectUser['id'] == $userId) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Determina si un usuario puede ver una tarea específica
     * 
     * @param array $task Datos de la tarea
     * @param int $userId ID del usuario
     * @param int $userRole Rol del usuario
     * @param int|null $userAreaId ID del área del usuario
     * @return bool True si puede ver la tarea
     */
    public static function canViewTask($task, $userId, $userRole, $userAreaId = null) {
        // Administrador o Gerente General: acceso total
        if (self::isAdmin($userRole)) {
            return true;
        }
        
        // Gerente de Área: acceso a tareas de proyectos de su área
        if ($userRole == self::ROLE_AREA_MANAGER && $userAreaId == $task['proyecto_area_id']) {
            return true;
        }
        
        // Jefe de Departamento: acceso a tareas creadas por él o asignadas a él
        if ($userRole == self::ROLE_DEPARTMENT_HEAD && 
            ($task['creado_por'] == $userId || $task['asignado_a'] == $userId)) {
            return true;
        }
        
        // Colaborador: acceso solo a sus tareas asignadas
        if ($userRole == self::ROLE_COLLABORATOR && $task['asignado_a'] == $userId) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Determina si un usuario puede editar un proyecto
     * 
     * @param array $project Datos del proyecto
     * @param int $userId ID del usuario
     * @param int $userRole Rol del usuario
     * @param int|null $userAreaId ID del área del usuario
     * @return bool True si puede editar el proyecto
     */
    public static function canEditProject($project, $userId, $userRole, $userAreaId = null) {
        // Administrador o Gerente General: acceso total
        if (self::isAdmin($userRole)) {
            return true;
        }
        
        // Gerente de Área: solo proyectos de su área
        if ($userRole == self::ROLE_AREA_MANAGER && $userAreaId == $project['area_id']) {
            return true;
        }
        
        // Jefe de Departamento: solo proyectos creados por él
        if ($userRole == self::ROLE_DEPARTMENT_HEAD && $project['creado_por'] == $userId) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Determina si un usuario puede editar una tarea
     * 
     * @param array $task Datos de la tarea
     * @param int $userId ID del usuario
     * @param int $userRole Rol del usuario
     * @param int|null $userAreaId ID del área del usuario
     * @return bool True si puede editar la tarea
     */
    public static function canEditTask($task, $userId, $userRole, $userAreaId = null) {
        // Administrador o Gerente General: acceso total
        if (self::isAdmin($userRole)) {
            return true;
        }
        
        // Gerente de Área: tareas de proyectos de su área
        if ($userRole == self::ROLE_AREA_MANAGER && $userAreaId == $task['proyecto_area_id']) {
            return true;
        }
        
        // Jefe de Departamento: tareas creadas por él
        if ($userRole == self::ROLE_DEPARTMENT_HEAD && $task['creado_por'] == $userId) {
            return true;
        }
        
        // Colaborador: solo puede modificar estado y porcentaje de tareas asignadas a él
        if ($userRole == self::ROLE_COLLABORATOR && $task['asignado_a'] == $userId) {
            return true; // Nota: en controladores, limitar qué campos puede editar
        }
        
        return false;
    }
    
    /**
     * Filtra una lista de proyectos según los permisos del usuario
     * 
     * @param array $projects Lista de proyectos
     * @param int $userId ID del usuario
     * @param int $userRole Rol del usuario
     * @param int|null $userAreaId ID del área del usuario
     * @return array Lista filtrada de proyectos
     */
    public static function filterProjectsByPermission($projects, $userId, $userRole, $userAreaId = null) {
        if (self::isAdmin($userRole)) {
            return $projects; // Acceso total para admin y gerente general
        }
        
        $filteredProjects = [];
        
        foreach ($projects as $project) {
            // Gerente de Área: proyectos de su área
            if ($userRole == self::ROLE_AREA_MANAGER && $project['area_id'] == $userAreaId) {
                $filteredProjects[] = $project;
                continue;
            }
            
            // Si es creador del proyecto
            if ($project['creado_por'] == $userId) {
                $filteredProjects[] = $project;
                continue;
            }
            
            // Si está asignado al proyecto (esto requiere verificar en la tabla usuarios_proyectos)
            // Esta parte normalmente se manejaría en el modelo
        }
        
        return $filteredProjects;
    }
    
    /**
     * Filtra una lista de tareas según los permisos del usuario
     * 
     * @param array $tasks Lista de tareas
     * @param int $userId ID del usuario
     * @param int $userRole Rol del usuario
     * @param int|null $userAreaId ID del área del usuario
     * @return array Lista filtrada de tareas
     */
    public static function filterTasksByPermission($tasks, $userId, $userRole, $userAreaId = null) {
        if (self::isAdmin($userRole)) {
            return $tasks; // Acceso total para admin y gerente general
        }
        
        $filteredTasks = [];
        
        foreach ($tasks as $task) {
            // Gerente de Área: tareas de proyectos de su área
            if ($userRole == self::ROLE_AREA_MANAGER && $task['proyecto_area_id'] == $userAreaId) {
                $filteredTasks[] = $task;
                continue;
            }
            
            // Jefe de Departamento: tareas creadas por él o asignadas a él
            if ($userRole == self::ROLE_DEPARTMENT_HEAD && 
                ($task['creado_por'] == $userId || $task['asignado_a'] == $userId)) {
                $filteredTasks[] = $task;
                continue;
            }
            
            // Colaborador: solo sus tareas asignadas
            if ($userRole == self::ROLE_COLLABORATOR && $task['asignado_a'] == $userId) {
                $filteredTasks[] = $task;
                continue;
            }
        }
        
        return $filteredTasks;
    }
}