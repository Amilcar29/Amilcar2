<?php
// Verifica permisos: solo Gerentes de Área
if ($_SESSION['user_role'] != 3) {
    header('Location: index.php?controller=error&action=forbidden');
    exit;
}

require_once 'views/templates/header.php';
require_once 'views/templates/sidebar.php';
?>

<div class="content-wrapper">
    <!-- Encabezado -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Dashboard - Gerente de Área</h1>
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

    <!-- Contenido principal -->
    <section class="content">
        <div class="container-fluid">
            <!-- Tarjetas de resumen -->
            <div class="row">
                <div class="col-lg-3 col-6">
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
                
                <div class="col-lg-3 col-6">
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
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo $taskStats['en_progreso']; ?></h3>
                            <p>Tareas en Progreso</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-spinner"></i>
                        </div>
                        <a href="index.php?controller=task&action=index&estado=En Progreso" class="small-box-footer">
                            Ver detalles <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo count($taskStats['proximos_vencimientos']); ?></h3>
                            <p>Próximas a Vencer</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <a href="#" class="small-box-footer" data-toggle="modal" data-target="#proximasVencimientoModal">
                            Ver detalles <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Gráficos de estadísticas -->
            <div class="row">
                <!-- Gráfico de estado de proyectos -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Estado de Proyectos</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="projectStatusChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Gráfico de estado de tareas -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Estado de Tareas</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="taskStatusChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filtros y listados -->
            <div class="row">
                <!-- Proyectos recientes -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Proyectos Recientes</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Estado</th>
                                        <th>Fecha Fin</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($recentProjects) && !empty($recentProjects)): ?>
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
                                                <td><?php echo date('d/m/Y', strtotime($project['fecha_fin'])); ?></td>
                                                <td>
                                                    <a href="index.php?controller=project&action=view&id=<?php echo $project['id']; ?>" class="btn btn-xs btn-info">
                                                        <i class="fas fa-eye"></i>
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
                        <div class="card-footer text-center">
                            <a href="index.php?controller=project&action=index" class="btn btn-sm btn-secondary">
                                Ver todos los proyectos
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Tareas pendientes -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Tareas Pendientes</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Asignado a</th>
                                        <th>Prioridad</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($pendingTasks) && !empty($pendingTasks)): ?>
                                        <?php foreach ($pendingTasks as $task): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($task['titulo']); ?></td>
                                                <td><?php echo htmlspecialchars($task['asignado_nombre']); ?></td>
                                                <td>
                                                    <span class="badge 
                                                        <?php 
                                                        switch ($task['prioridad']) {
                                                            case 'Baja':
                                                                echo 'badge-info';
                                                                break;
                                                            case 'Media':
                                                                echo 'badge-secondary';
                                                                break;
                                                            case 'Alta':
                                                                echo 'badge-warning';
                                                                break;
                                                            case 'Urgente':
                                                                echo 'badge-danger';
                                                                break;
                                                            default:
                                                                echo 'badge-info';
                                                        }
                                                        ?>">
                                                        <?php echo htmlspecialchars($task['prioridad']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="index.php?controller=task&action=view&id=<?php echo $task['id']; ?>" class="btn btn-xs btn-info">
                                                        <i class="fas fa-eye"></i>
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
                        <div class="card-footer text-center">
                            <a href="index.php?controller=task&action=index" class="btn btn-sm btn-secondary">
                                Ver todas las tareas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal de tareas próximas a vencer -->
<div class="modal fade" id="proximasVencimientoModal" tabindex="-1" role="dialog" aria-labelledby="proximasVencimientoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="proximasVencimientoModalLabel">Tareas Próximas a Vencer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Fecha Límite</th>
                            <th>Días Restantes</th>
                            <th>Prioridad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($taskStats['proximos_vencimientos']) && !empty($taskStats['proximos_vencimientos'])): ?>
                            <?php foreach ($taskStats['proximos_vencimientos'] as $task): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($task['titulo']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($task['fecha_fin'])); ?></td>
                                    <td>
                                        <span class="badge <?php echo ($task['dias_restantes'] <= 2) ? 'badge-danger' : 'badge-warning'; ?>">
                                            <?php echo $task['dias_restantes']; ?> días
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge 
                                            <?php 
                                            switch ($task['prioridad']) {
                                                case 'Baja':
                                                    echo 'badge-info';
                                                    break;
                                                case 'Media':
                                                    echo 'badge-secondary';
                                                    break;
                                                case 'Alta':
                                                    echo 'badge-warning';
                                                    break;
                                                case 'Urgente':
                                                    echo 'badge-danger';
                                                    break;
                                                default:
                                                    echo 'badge-info';
                                            }
                                            ?>">
                                            <?php echo htmlspecialchars($task['prioridad']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="index.php?controller=task&action=view&id=<?php echo $task['id']; ?>" class="btn btn-xs btn-info">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay tareas próximas a vencer</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para los gráficos -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de estado de proyectos
    var projectCtx = document.getElementById('projectStatusChart').getContext('2d');
    var projectChart = new Chart(projectCtx, {
        type: 'pie',
        data: {
            labels: ['Pendientes', 'En Progreso', 'Completados', 'Cancelados'],
            datasets: [{
                data: [
                    <?php echo $projectStats['pendientes']; ?>,
                    <?php echo $projectStats['en_progreso']; ?>,
                    <?php echo $projectStats['completados']; ?>,
                    <?php echo $projectStats['cancelados']; ?>
                ],
                backgroundColor: [
                    '#f8f9fa',  // Pendientes (gris claro)
                    '#007bff',  // En Progreso (azul)
                    '#28a745',  // Completados (verde)
                    '#dc3545'   // Cancelados (rojo)
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'right'
            }
        }
    });
    
    // Gráfico de estado de tareas
    var taskCtx = document.getElementById('taskStatusChart').getContext('2d');
    var taskChart = new Chart(taskCtx, {
        type: 'pie',
        data: {
            labels: ['Pendientes', 'En Progreso', 'Completadas', 'Canceladas'],
            datasets: [{
                data: [
                    <?php echo $taskStats['pendientes']; ?>,
                    <?php echo $taskStats['en_progreso']; ?>,
                    <?php echo $taskStats['completadas']; ?>,
                    <?php echo $taskStats['canceladas']; ?>
                ],
                backgroundColor: [
                    '#f8f9fa',  // Pendientes (gris claro)
                    '#007bff',  // En Progreso (azul)
                    '#28a745',  // Completadas (verde)
                    '#dc3545'   // Canceladas (rojo)
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'right'
            }
        }
    });
});
</script>

<?php require_once 'views/templates/footer.php'; ?>