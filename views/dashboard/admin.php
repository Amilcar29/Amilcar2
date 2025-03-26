<?php
// Verifica permisos: solo Administradores y Gerentes Generales
if ($_SESSION['user_role'] > 2) {
    header('Location: index.php?controller=error&action=forbidden');
    exit;
}

require_once 'views/templates/header.php';
require_once 'views/templates/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard - Administrador</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <!-- Indicadores rápidos -->
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo $projectStats['total']; ?></h3>
                            <p>Proyectos Totales</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        <a href="index.php?controller=project&action=index" class="small-box-footer">
                            Ver proyectos <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo $taskStats['total']; ?></h3>
                            <p>Tareas Totales</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <a href="index.php?controller=task&action=index" class="small-box-footer">
                            Ver tareas <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo $projectStats['en_progreso']; ?></h3>
                            <p>Proyectos en Progreso</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-spinner"></i>
                        </div>
                        <a href="index.php?controller=project&action=index&estado=En Progreso" class="small-box-footer">
                            Ver detalles <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo count($taskStats['proximos_vencimientos']); ?></h3>
                            <p>Tareas por Vencer</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <a href="index.php?controller=task&action=index" class="small-box-footer">
                            Ver detalles <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Proyectos recientes -->
                <div class="col-12 col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Proyectos Recientes</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped responsive-table">
                                    <thead>
                                        <tr>
                                            <th>Título</th>
                                            <th>Estado</th>
                                            <th class="d-none d-md-table-cell">Fecha Fin</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($recentProjects)): ?>
                                            <?php foreach ($recentProjects as $project): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($project['titulo']); ?></td>
                                                    <td>
                                                        <span class="badge 
                                                            <?php 
                                                            switch ($project['estado']) {
                                                                case 'Pendiente':
                                                                    echo 'badge-secondary';
                                                                    break;
                                                                case 'En Progreso':
                                                                    echo 'badge-primary';
                                                                    break;
                                                                case 'Completado':
                                                                    echo 'badge-success';
                                                                    break;
                                                                case 'Cancelado':
                                                                    echo 'badge-danger';
                                                                    break;
                                                                default:
                                                                    echo 'badge-info';
                                                            }
                                                            ?>">
                                                            <?php echo htmlspecialchars($project['estado']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="d-none d-md-table-cell"><?php echo date('d/m/Y', strtotime($project['fecha_fin'])); ?></td>
                                                    <td>
                                                        <a href="index.php?controller=project&action=view&id=<?php echo $project['id']; ?>" class="btn btn-xs btn-info">
                                                            <i class="fas fa-eye"></i> <span class="d-none d-sm-inline">Ver</span>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No hay proyectos recientes</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer text-center">
                            <a href="index.php?controller=project&action=index" class="btn btn-sm btn-secondary">
                                Ver todos los proyectos
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Tareas pendientes -->
                <div class="col-12 col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Tareas Pendientes</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped responsive-table">
                                    <thead>
                                        <tr>
                                            <th>Título</th>
                                            <th class="d-none d-md-table-cell">Asignado a</th>
                                            <th>Fecha Fin</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($pendingTasks)): ?>
                                            <?php foreach ($pendingTasks as $task): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($task['titulo']); ?></td>
                                                    <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($task['asignado_nombre'] ?? 'Sin asignar'); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($task['fecha_fin'])); ?></td>
                                                    <td>
                                                        <a href="index.php?controller=task&action=view&id=<?php echo $task['id']; ?>" class="btn btn-xs btn-info">
                                                            <i class="fas fa-eye"></i> <span class="d-none d-sm-inline">Ver</span>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No hay tareas pendientes</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer text-center">
                            <a href="index.php?controller=task&action=index" class="btn btn-sm btn-secondary">
                                Ver todas las tareas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/templates/footer.php'; ?>