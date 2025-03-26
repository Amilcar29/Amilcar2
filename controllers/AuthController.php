<?php
class AuthController {
    private $db;
    private $user;
    
    public function __construct() {
        require_once 'config/database.php';
        require_once 'models/User.php';
        
        $this->db = new Database();
        $this->user = new User($this->db);
    }
    
    /**
     * Maneja el inicio de sesión
     */
    public function login() {
        // Verifica si ya hay una sesión activa
        if (isset($_SESSION['user_id'])) {
            $this->redirectBasedOnRole();
            exit;
        }
        
        // Procesa el formulario cuando se envía
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];
            
            if (empty($email) || empty($password)) {
                $_SESSION['error'] = 'Por favor, complete todos los campos.';
                include 'views/auth/login.php';
                exit;
            }
            
            // Verifica las credenciales
            $user = $this->user->getUserByEmail($email);
            
            if ($user && password_verify($password, $user['password'])) {
                // Inicia sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre'] . ' ' . $user['apellido'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['rol_id'];
                $_SESSION['user_area'] = $user['area_id'];
                $_SESSION['user_department'] = $user['departamento_id'];
                
                // Registra el inicio de sesión
                $this->user->logLogin($user['id']);
                
                // Redirige según el rol
                $this->redirectBasedOnRole();
                exit;
            } else {
                $_SESSION['error'] = 'Email o contraseña incorrectos.';
                include 'views/auth/login.php';
                exit;
            }
        }
        
        // Muestra el formulario de inicio de sesión
        include 'views/auth/login.php';
    }
    
    /**
     * Redirige al usuario según su rol
     */
    private function redirectBasedOnRole() {
        switch ($_SESSION['user_role']) {
            case 1: // Administrador
            case 2: // Gerente General
                header('Location: index.php?controller=dashboard&action=admin');
                break;
            case 3: // Gerente de Área
                header('Location: index.php?controller=dashboard&action=area');
                break;
            case 4: // Jefe de Departamento
                header('Location: index.php?controller=dashboard&action=department');
                break;
            case 5: // Colaborador
                header('Location: index.php?controller=dashboard&action=collaborator');
                break;
            default:
                header('Location: index.php');
                break;
        }
    }
    
    /**
     * Cierra la sesión
     */
    public function logout() {
        // Destruye la sesión
        session_start();
        session_unset();
        session_destroy();
        
        // Redirige al login
        header('Location: index.php?controller=auth&action=login');
        exit;
    }
    
    /**
     * Verifica si el usuario tiene el permiso necesario
     */
    public static function checkPermission($requiredRoles = []) {
        // Verifica si hay sesión iniciada
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
        // Si no se especifican roles, cualquier usuario autenticado puede acceder
        if (empty($requiredRoles)) {
            return true;
        }
        
        // Verifica si el rol del usuario está entre los roles permitidos
        if (in_array($_SESSION['user_role'], $requiredRoles)) {
            return true;
        }
        
        // Si no tiene permiso, muestra error 403
        header('Location: index.php?controller=error&action=forbidden');
        exit;
    }
    
    /**
     * Proceso de recuperación de contraseña
     */
    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            
            if (empty($email)) {
                $_SESSION['error'] = 'Por favor, ingrese su email.';
                include 'views/auth/reset-password.php';
                exit;
            }
            
            $user = $this->user->getUserByEmail($email);
            
            if ($user) {
                // Genera token de recuperación
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                $this->user->saveResetToken($user['id'], $token, $expires);
                
                // Envía email con enlace de recuperación
                $resetLink = 'http://' . $_SERVER['HTTP_HOST'] . '/index.php?controller=auth&action=newPassword&token=' . $token;
                $this->sendResetEmail($user['email'], $user['nombre'], $resetLink);
                
                $_SESSION['success'] = 'Se ha enviado un enlace de recuperación a su email.';
                include 'views/auth/reset-password.php';
                exit;
            } else {
                $_SESSION['error'] = 'No existe un usuario con ese email.';
                include 'views/auth/reset-password.php';
                exit;
            }
        }
        
        include 'views/auth/reset-password.php';
    }
    
    /**
     * Envía email con enlace de recuperación
     */
    private function sendResetEmail($email, $name, $resetLink) {
        $subject = 'Recuperación de contraseña - Sistema de Proyectos';
        $message = "
        <html>
        <head>
            <title>Recuperación de contraseña</title>
        </head>
        <body>
            <h2>Hola $name,</h2>
            <p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace para crear una nueva contraseña:</p>
            <p><a href='$resetLink'>Restablecer mi contraseña</a></p>
            <p>Este enlace expirará en 1 hora.</p>
            <p>Si no has solicitado este cambio, puedes ignorar este mensaje.</p>
            <p>Saludos,<br>Sistema de Gestión de Proyectos</p>
        </body>
        </html>
        ";
        
        // Cabeceras para email HTML
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: noreply@sistemaproyectos.com\r\n";
        
        // Envía el email
        mail($email, $subject, $message, $headers);
    }
}