<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1.0/dist/css/adminlte.min.css">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/styles.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar Superior -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Enlaces de navegación izquierdos -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="index.php" class="nav-link">Inicio</a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="index.php?controller=project&action=index" class="nav-link">Proyectos</a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="index.php?controller=task&action=index" class="nav-link">Tareas</a>
                </li>
            </ul>

            <!-- Enlaces de navegación derechos -->
            <ul class="navbar-nav ml-auto">
                <!-- Notificaciones Dropdown -->
                <li class="nav-item dropdown">
                    <?php
                    require_once 'models/Notification.php';
                    $db = new Database();
                    $notificationModel = new Notification($db);
                    $unreadCount = $notificationModel->countUnread($_SESSION['user_id']);
                    $notifications = $notificationModel->getUserNotifications($_SESSION['user_id'], 5);
                    ?>
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-bell"></i>
                        <?php if ($unreadCount > 0): ?>
                            <span class="badge badge-warning navbar-badge"><?php echo $unreadCount; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-item dropdown-header"><?php echo $unreadCount; ?> Notificaciones</span>
                        <div class="dropdown-divider"></div>
                        
                        <?php if (!empty($notifications)): ?>
                            <?php foreach ($notifications as $notification): ?>
                                <a href="<?php echo $notification['referencia_id'] ? 'index.php?controller=' . ($notification['tipo'] == 'proyecto' ? 'project' : 'task') . '&action=view&id=' . $notification['referencia_id'] : '#'; ?>" class="dropdown-item <?php echo $notification['leida'] ? '' : 'bg-light'; ?>">
                                    <i class="fas <?php echo $notification['tipo'] == 'proyecto' ? 'fa-project-diagram' : 'fa-tasks'; ?> mr-2"></i> <?php echo htmlspecialchars($notification['mensaje']); ?>
                                    <span class="float-right text-muted text-sm"><?php echo date('d/m H:i', strtotime($notification['fecha_creacion'])); ?></span>
                                </a>
                                <div class="dropdown-divider"></div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-info-circle mr-2"></i> No hay notificaciones
                            </a>
                            <div class="dropdown-divider"></div>
                        <?php endif; ?>
                        
                        <a href="index.php?controller=notification&action=index" class="dropdown-item dropdown-footer">Ver todas las notificaciones</a>
                    </div>
                </li>
                
                <!-- Perfil de usuario -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-user"></i> <span class="d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="index.php?controller=user&action=profile" class="dropdown-item">
                            <i class="fas fa-user mr-2"></i> Mi Perfil
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="index.php?controller=user&action=changePassword" class="dropdown-item">
                            <i class="fas fa-key mr-2"></i> Cambiar Contraseña
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="index.php?controller=auth&action=logout" class="dropdown-item">
                            <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                        </a>
                    </div>
                </li>
            </ul>
        </nav>