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
                    <h1 class="m-0">Reporte de Proyectos</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item active">Reporte de Proyectos</li>
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
                        <input type="hidden" name="action" value="projects">
                        
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
                            
                            <?php if ($_SESSION['user_role'] <= 2 && !empty($areas)): ?>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="area_id">Área</label>
                                    <select class="form-control" id="area_id" name="area_id">
                                        <option value="">Todas</option>
                                        <?php foreach ($areas as $area): ?>
                                            <option value="<?php echo $area['id']; ?>" <?php echo (isset($_GET['area_id']) && $_GET['area_id'] == $area['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($area['nombre']); ?>
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
                                    <a href="index.php?controller=report&action=projects" class="btn btn-secondary ml-2">
                                        <i class="fas fa-sync-alt mr-1"></i> Limpiar
                                    </a>
                                </div>
                            </div>
                            
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-group">
                                    <a href="index.php?controller=report&action=exportProjectsCsv<?php echo isset($_GET['estado']) ? '&estado=' . $_GET['estado'] : ''; ?><?php echo isset($_GET['prioridad']) ? '&prioridad=' . $_GET['prioridad'] : ''; ?><?php echo isset($_GET['area_id']) ? '&area_id=' . $_GET['area_id'] : ''; ?><?php echo isset($_GET['fecha_inicio']) ? '&fecha_inicio=' . $_GET['fecha_inicio'] : ''; ?><?php echo isset($_GET['fecha_fin']) ? '&fecha_fin=' . $_GET['fecha_fin'] : ''; ?>" class="btn btn-success">
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
                            <p>Total Proyectos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-project-diagram"></i>
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
                            <p>Completados</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo $estadisticas['por_estado']['Cancelado']; ?></h3>
                            <p>Cancelados</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-ban"></i>
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
                                <i class="fas fa-chart-pie mr-1"></i> Proyectos por Estado
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="projectsByStatusChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar mr-1"></i> Proyectos por Prioridad
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="projectsByPriorityChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabla de resultados -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-1"></i> Listado de Proyectos (<?php echo count($projects); ?> resultados)
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
                                    <th>Área</th>
                                    <th>Estado</th>
                                    <th>Prioridad</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Fin</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($projects)): ?>
                                    <?php foreach ($projects as $project): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($project['titulo']); ?></td>
                                            <td><?php echo htmlspecialchars($project['area_nombre'] ?? 'Sin asignar'); ?></td>
                                            <td>
                                                <span class="badge <?php 
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
                                            <td>
                                                <span class="badge <?php 
                                                    switch ($project['prioridad']) {
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
                                                    <?php echo htmlspecialchars($project['prioridad']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($project['fecha_inicio'])); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($project['fecha_fin'])); ?></td>
                                            <td>
                                                <a href="index.php?controller=project&action=view&id=<?php echo $project['id']; ?>" class="btn btn-xs btn-info">
                                                    <i class="fas fa-eye"></i> Ver
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No hay proyectos que cumplan con los filtros seleccionados</td>
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
        // Gráfico de proyectos por estado
        var statusCtx = document.getElementById('projectsByStatusChart').getContext('2d');
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
        
        // Gráfico de proyectos por prioridad
        var priorityCtx = document.getElementById('projectsByPriorityChart').getContext('2d');
        var priorityChart = new Chart(priorityCtx, {
            type: 'bar',
            data: <?php echo json_encode($chartData['prioridades']); ?>,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            precision: 0
                        }
                    }]
                }
            }
        });
    });
</script>

<?php require_once 'views/templates/footer.php'; ?>