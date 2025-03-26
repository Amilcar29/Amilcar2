<!-- Barra lateral principal -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Logo y título del sitio -->
    <a href="index.php" class="brand-link">
        <img src="<?php echo BASE_URL; ?>/assets/img/logo.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light"><?php echo APP_NAME; ?></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Usuario en sidebar -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="<?php echo BASE_URL; ?>/assets/img/avatar.png" class="img-circle elevation-2" alt="Usuario">
            </div>
            <div class="info">
                <a href="index.php?controller=user&action=profile" class="d-block">
                    <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                </a>
                <small class="text-muted d-block">
                    <?php 
                    $roles = [
                        1 => 'Administrador',
                        2 => 'Gerente General',
                        3 => 'Gerente de Área',
                        4 => 'Jefe de Departamento',
                        5 => 'Colaborador'
                    ];
                    echo $roles[$_SESSION['user_role']] ?? 'Usuario';
                    ?>
                </small>
            </div>
        </div>

        <!-- Menú de navegación -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="index.php" class="nav-link <?php echo (!isset($_GET['controller']) || $_GET['controller'] == 'dashboard') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                
                <!-- Proyectos -->
                <li class="nav-item <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'project') ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'project') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-project-diagram"></i>
                        <p>
                            Proyectos
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="index.php?controller=project&action=index" class="nav-link <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'project' && isset($_GET['action']) && $_GET['action'] == 'index') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Lista de Proyectos</p>
                            </a>
                        </li>
                        <?php if ($_SESSION['user_role'] <= 4): // Hasta Jefe de Departamento puede crear ?>
                        <li class="nav-item">
                            <a href="index.php?controller=project&action=create" class="nav-link <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'project' && isset($_GET['action']) && $_GET['action'] == 'create') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Nuevo Proyecto</p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                
                <!-- Tareas -->
                <li class="nav-item <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'task') ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'task') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-tasks"></i>
                        <p>
                            Tareas
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="index.php?controller=task&action=index" class="nav-link <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'task' && isset($_GET['action']) && $_GET['action'] == 'index') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Lista de Tareas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?controller=task&action=create" class="nav-link <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'task' && isset($_GET['action']) && $_GET['action'] == 'create') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Nueva Tarea</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?controller=task&action=calendar" class="nav-link <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'task' && isset($_GET['action']) && $_GET['action'] == 'calendar') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Calendario</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <?php if ($_SESSION['user_role'] <= 2): // Solo Admin y Gerente General ?>
                <!-- Usuarios -->
                <li class="nav-item <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'user' && !in_array($_GET['action'], ['profile', 'changePassword'])) ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'user' && !in_array($_GET['action'], ['profile', 'changePassword'])) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Usuarios
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="index.php?controller=user&action=index" class="nav-link <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'user' && isset($_GET['action']) && $_GET['action'] == 'index') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Lista de Usuarios</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?controller=user&action=create" class="nav-link <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'user' && isset($_GET['action']) && $_GET['action'] == 'create') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Nuevo Usuario</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Configuración -->
                <li class="nav-item <?php echo (isset($_GET['controller']) && ($_GET['controller'] == 'area' || $_GET['controller'] == 'department')) ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (isset($_GET['controller']) && ($_GET['controller'] == 'area' || $_GET['controller'] == 'department')) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>
                            Configuración
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="index.php?controller=area&action=index" class="nav-link <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'area') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Áreas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?controller=department&action=index" class="nav-link <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'department') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Departamentos</p>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Reportes -->
                <li class="nav-item <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'report') ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'report') ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>
                            Reportes
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="index.php?controller=report&action=projects" class="nav-link <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'report' && isset($_GET['action']) && $_GET['action'] == 'projects') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Reporte de Proyectos</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?controller=report&action=tasks" class="nav-link <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'report' && isset($_GET['action']) && $_GET['action'] == 'tasks') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Reporte de Tareas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?controller=report&action=users" class="nav-link <?php echo (isset($_GET['controller']) && $_GET['controller'] == 'report' && isset($_GET['action']) && $_GET['action'] == 'users') ? 'active' : ''; ?>">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Reporte de Usuarios</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <!-- Cerrar Sesión -->
                <li class="nav-item">
                    <a href="index.php?controller=auth&action=logout" class="nav-link">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Cerrar Sesión</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>