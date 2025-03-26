<?php
// Verificar permisos: solo Administradores y Gerentes
if ($_SESSION['user_role'] > 3) {
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
                    <h1 class="m-0">Reporte de Tareas</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item active">Reporte de Tareas</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Filtros del reporte -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter mr-1"></i> Filtros
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="get" action="index.php">
                        <input type="hidden" name="controller" value="report">
                        <input type="hidden" name="action" value="tasks">
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="estado">Estado</label>
                                    <select class="form-control" id="estado" name="estado">
                                        <option value="">Todos</option>
                                        <option value="Pendiente" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                        <option value="En Progreso" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'En Progreso') ? 'selected' : ''; ?>>En Progreso</option>
                                        <option value="Completado" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Completado') ? 'selected' : ''; ?>>Completado</option>
                                        <option value="Cancelado" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="prioridad">Prioridad</label>
                                    <select class="form-control" id="prioridad" name="prioridad">
                                        <option value="">Todas</option>
                                        <option value="Baja" <?php echo (isset($_GET['prioridad']) && $_GET['prioridad'] == 'Baja') ? 'selected' : ''; ?>>Baja</option>
                                        <option value="Media" <?php echo (isset($_GET['prioridad']) && $_GET['prioridad'] == 'Media') ? 'selected' : ''; ?>>Media</option>
                                        <option value="Alta" <?php echo (isset($_GET['prioridad']) && $_GET['prioridad'] == 'Alta') ? 'selected' : ''; ?>>Alta</option>
                                        <option value="Urgente" <?php echo (isset($_GET['prioridad']) && $_GET['prioridad'] == 'Urgente') ? 'selected' : ''; ?>>Urgente</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="proyecto_id">Proyecto</label>
                                    <select class="form-control" id="proyecto_id" name="proyecto_id">
                                        <option value="">Todos</option>
                                        <?php if (!empty($projects)): ?>
                                            <?php foreach ($projects as $project): ?>
                                                <option value="<?php echo $project['id']; ?>" <?php echo (isset($_GET['proyecto_id']) && $_GET['proyecto_id'] == $project['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($project['titulo']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <?php if ($_SESSION['user_role'] <= 2 && !empty($usuarios)): ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="asignado_a">Asignado a</label>
                                    <select class="form-control" id="asignado_a" name="asignado_a">
                                        <option value="">Todos</option>
                                        <option value="sin_asignar" <?php echo (isset($_GET['asignado_a']) && $_GET['asignado_a'] == 'sin_asignar') ? 'selected' : ''; ?>>Sin asignar</option>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <option value="<?php echo $usuario['id']; ?>" <?php echo (isset($_GET['asignado_a']) && $_GET['asignado_a'] == $usuario['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_inicio">Fecha Inicio (desde)</label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_fin">Fecha Fin (hasta)</label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?php echo isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter mr-1"></i> Filtrar
                                    </button>
                                    <a href="index.php?controller=report&action=tasks" class="btn btn-secondary ml-2">
                                        <i class="fas fa-sync-alt mr-1"></i> Limpiar
                                    </a>
                                </div>
                            </div>
                            
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-group">
                                    <a href="index.php?controller=report&action=exportTasksCsv<?php echo isset($_GET['estado']) ? '&estado=' . $_GET['estado'] : ''; ?><?php echo isset($_GET['prioridad']) ? '&prioridad=' . $_GET['prioridad'] : ''; ?><?php echo isset($_GET['proyecto_id']) ? '&proyecto_id=' . $_GET['proyecto_id'] : ''; ?><?php echo isset($_GET['asignado_a']) ? '&asignado_a=' . $_GET['asignado_a'] : ''; ?><?php echo isset($_GET['fecha_inicio']) ? '&fecha_inicio=' . $_GET['fecha_inicio'] : ''; ?><?php echo isset($_GET['fecha_fin']) ? '&fecha_fin=' . $_GET['fecha_fin'] : ''; ?>" class="btn btn-success">
                                        <i class="fas fa-file-csv mr-1"></i> Exportar a CSV
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Resumen estadístico -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3><?php echo $estadisticas['total']; ?></h3>
                            <p>Total Tareas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3><?php echo $estadisticas['por_estado']['En Progreso']; ?></h3>
                            <p>En Progreso</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-spinner"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo $estadisticas['por_estado']['Completado']; ?></h3>
                            <p>Completadas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo $estadisticas['proximos_vencimientos']; ?></h3>
                            <p>Por Vencer (7 días)</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gráficos -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1"></i> Tareas por Estado
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="tasksByStatusChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar mr-1"></i> Tareas por Prioridad
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="tasksByPriorityChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Progreso general -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-percentage mr-1"></i> Progreso Promedio: <?php echo $estadisticas['progreso_promedio']; ?>%
                    </h3>
                </div>
                <div class="card-body">
                    <div class="progress">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $estadisticas['progreso_promedio']; ?>%" aria-valuenow="<?php echo $estadisticas['progreso_promedio']; ?>" aria-valuemin="0" aria-valuemax="100">
                            <?php echo $estadisticas['progreso_promedio']; ?>%
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabla de resultados -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-1"></i> Listado de Tareas (<?php echo count($tasks); ?> resultados)
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Título</th>
                                    <th>Proyecto</th>
                                    <th>Asignado a</th>
                                    <th>Estado</th>
                                    <th>Prioridad</th>
                                    <th>Progreso</th>
                                    <th>Fecha Fin</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($tasks)): ?>
                                    <?php foreach ($tasks as $task): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($task['titulo']); ?></td>
                                            <td>
                                                <?php if (!empty($task['proyecto_titulo'])): ?>
                                                    <a href="index.php?controller=project&action=view&id=<?php echo $task['proyecto_id']; ?>">
                                                        <?php echo htmlspecialchars($task['proyecto_titulo']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Sin proyecto</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo !empty($task['asignado_nombre']) ? htmlspecialchars($task['asignado_nombre']) : '<span class="text-muted">Sin asignar</span>'; ?></td>
                                            <td>
                                                <span class="badge <?php 
                                                    switch ($task['estado']) {
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
                                                    <?php echo htmlspecialchars($task['estado']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php 
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
                                                <div class="progress">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $task['porcentaje_completado']; ?>%" aria-valuenow="<?php echo $task['porcentaje_completado']; ?>" aria-valuemin="0" aria-valuemax="100">
                                                        <?php echo $task['porcentaje_completado']; ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="<?php echo (strtotime($task['fecha_fin']) < time() && $task['estado'] != 'Completado' && $task['estado'] != 'Cancelado') ? 'text-danger font-weight-bold' : ''; ?>">
                                                    <?php echo date('d/m/Y', strtotime($task['fecha_fin'])); ?>
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
                                        <td colspan="8" class="text-center">No hay tareas que cumplan con los filtros seleccionados</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    $(document).ready(function() {
        // Gráfico de tareas por estado
        var statusCtx = document.getElementById('tasksByStatusChart').getContext('2d');
        var statusChart = new Chart(statusCtx, {
            type: 'pie',
            data: <?php echo json_encode($chartData['estados']); ?>,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'right'
                }
            }
        });
        
        // Gráfico de tareas por prioridad
      //  var priorityCtx = document.getElementById('tasksByPriorityChart').getContext('2d');
        //var priorityChart = new Chart(priorityCtx, {
          //  type: 'bar',
            //data: <?php echo json_encode($chartData['prioridades']); ?>,
            //options: {
              //  responsive: true,
                //maintainAspectRatio: false,
                //scales: {
                  //  yAxes: [{
					  
		 // Gráfico de tareas por prioridad
        var priorityCtx = document.getElementById('tasksByPriorityChart').getContext('2d');
        var priorityChart = new Chart(priorityCtx, {
            type: 'bar',
            data: <?php echo json_encode($chartData['prioridades']); ?>,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                },
                legend: {
                    display: false
                }
            }
        });
        
        // Inicializar datepickers con formato español si es necesario
        if($.fn.datepicker) {
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                language: 'es'
            });
        }
        
        // Inicializar select2 para mejores selects si está disponible
        if($.fn.select2) {
            $('.select2').select2({
                theme: 'bootstrap4',
                language: 'es'
            });
        }
    });
</script>

<?php require_once 'views/templates/footer.php'; ?>