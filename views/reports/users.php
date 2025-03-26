<?php
// Verificar permisos: solo Administradores
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
                    <h1 class="m-0">Reporte de Usuarios</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item active">Reporte de Usuarios</li>
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
                        <input type="hidden" name="action" value="users">
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="rol">Rol</label>
                                    <select class="form-control" id="rol" name="rol">
                                        <option value="">Todos</option>
                                        <option value="1" <?php echo (isset($_GET['rol']) && $_GET['rol'] == '1') ? 'selected' : ''; ?>>Administrador</option>
                                        <option value="2" <?php echo (isset($_GET['rol']) && $_GET['rol'] == '2') ? 'selected' : ''; ?>>Gerente</option>
                                        <option value="3" <?php echo (isset($_GET['rol']) && $_GET['rol'] == '3') ? 'selected' : ''; ?>>Líder de Proyecto</option>
                                        <option value="4" <?php echo (isset($_GET['rol']) && $_GET['rol'] == '4') ? 'selected' : ''; ?>>Miembro</option>
                                        <option value="5" <?php echo (isset($_GET['rol']) && $_GET['rol'] == '5') ? 'selected' : ''; ?>>Cliente</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="estado">Estado</label>
                                    <select class="form-control" id="estado" name="estado">
                                        <option value="">Todos</option>
                                        <option value="Activo" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                                        <option value="Inactivo" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="departamento">Departamento</label>
                                    <select class="form-control" id="departamento" name="departamento">
                                        <option value="">Todos</option>
                                        <?php if (!empty($departamentos)): ?>
                                            <?php foreach ($departamentos as $departamento): ?>
                                                <option value="<?php echo $departamento['id']; ?>" <?php echo (isset($_GET['departamento']) && $_GET['departamento'] == $departamento['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($departamento['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_registro_desde">Fecha Registro (desde)</label>
                                    <input type="date" class="form-control" id="fecha_registro_desde" name="fecha_registro_desde" value="<?php echo isset($_GET['fecha_registro_desde']) ? $_GET['fecha_registro_desde'] : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="fecha_registro_hasta">Fecha Registro (hasta)</label>
                                    <input type="date" class="form-control" id="fecha_registro_hasta" name="fecha_registro_hasta" value="<?php echo isset($_GET['fecha_registro_hasta']) ? $_GET['fecha_registro_hasta'] : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="buscar">Buscar por nombre o email</label>
                                    <input type="text" class="form-control" id="buscar" name="buscar" placeholder="Nombre, apellido o email" value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter mr-1"></i> Filtrar
                                    </button>
                                    <a href="index.php?controller=report&action=users" class="btn btn-secondary ml-2">
                                        <i class="fas fa-sync-alt mr-1"></i> Limpiar
                                    </a>
                                </div>
                            </div>
                            
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-group">
                                    <a href="index.php?controller=report&action=exportUsersCsv<?php echo isset($_GET['rol']) ? '&rol=' . $_GET['rol'] : ''; ?><?php echo isset($_GET['estado']) ? '&estado=' . $_GET['estado'] : ''; ?><?php echo isset($_GET['departamento']) ? '&departamento=' . $_GET['departamento'] : ''; ?><?php echo isset($_GET['fecha_registro_desde']) ? '&fecha_registro_desde=' . $_GET['fecha_registro_desde'] : ''; ?><?php echo isset($_GET['fecha_registro_hasta']) ? '&fecha_registro_hasta=' . $_GET['fecha_registro_hasta'] : ''; ?><?php echo isset($_GET['buscar']) ? '&buscar=' . urlencode($_GET['buscar']) : ''; ?>" class="btn btn-success">
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
                            <p>Total Usuarios</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?php echo $estadisticas['activos']; ?></h3>
                            <p>Usuarios Activos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?php echo $estadisticas['nuevos_mes']; ?></h3>
                            <p>Nuevos este mes</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?php echo $estadisticas['inactivos']; ?></h3>
                            <p>Usuarios Inactivos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-slash"></i>
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
                                <i class="fas fa-chart-pie mr-1"></i> Usuarios por Rol
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="usersByRoleChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar mr-1"></i> Usuarios por Departamento
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="usersByDepartmentChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actividad reciente -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-clock mr-1"></i> Actividad de Usuarios
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="userActivityChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                </div>
                <div class="card-footer bg-light">
                    <small class="text-muted">Nuevos registros por mes en los últimos 12 meses</small>
                </div>
            </div>
            
            <!-- Tabla de resultados -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-1"></i> Listado de Usuarios (<?php echo count($users); ?> resultados)
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
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Departamento</th>
                                    <th>Estado</th>
                                    <th>Tareas Asignadas</th>
                                    <th>Fecha Registro</th>
                                    <th>Último Acceso</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($users)): ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellido']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <span class="badge <?php 
                                                    switch ($user['rol_id']) {
                                                        case 1:
                                                            echo 'badge-danger';
                                                            break;
                                                        case 2:
                                                            echo 'badge-warning';
                                                            break;
                                                        case 3:
                                                            echo 'badge-info';
                                                            break;
                                                        case 4:
                                                            echo 'badge-primary';
                                                            break;
                                                        case 5:
                                                            echo 'badge-secondary';
                                                            break;
                                                        default:
                                                            echo 'badge-dark';
                                                    }
                                                ?>">
                                                    <?php echo htmlspecialchars($user['rol_nombre']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo !empty($user['departamento_nombre']) ? htmlspecialchars($user['departamento_nombre']) : '<span class="text-muted">No asignado</span>'; ?></td>
                                            <td>
                                                <span class="badge <?php echo ($user['estado'] == 'Activo') ? 'badge-success' : 'badge-danger'; ?>">
                                                    <?php echo htmlspecialchars($user['estado']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?php echo $user['tareas_count']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($user['fecha_registro'])); ?></td>
                                            <td>
                                                <?php if (!empty($user['ultimo_acceso'])): ?>
                                                    <?php echo date('d/m/Y H:i', strtotime($user['ultimo_acceso'])); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Nunca</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="index.php?controller=user&action=view&id=<?php echo $user['id']; ?>" class="btn btn-xs btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="index.php?controller=user&action=edit&id=<?php echo $user['id']; ?>" class="btn btn-xs btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="index.php?controller=report&action=userPerformance&id=<?php echo $user['id']; ?>" class="btn btn-xs btn-primary">
                                                        <i class="fas fa-chart-line"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center">No hay usuarios que cumplan con los filtros seleccionados</td>
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
        // Gráfico de usuarios por rol
        var roleCtx = document.getElementById('usersByRoleChart').getContext('2d');
        var roleChart = new Chart(roleCtx, {
            type: 'pie',
            data: <?php echo json_encode($chartData['roles']); ?>,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'right'
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            var dataset = data.datasets[tooltipItem.datasetIndex];
                            var total = dataset.data.reduce(function(previousValue, currentValue) {
                                return previousValue + currentValue;
                            });
                            var currentValue = dataset.data[tooltipItem.index];
                            var percentage = Math.floor(((currentValue/total) * 100)+0.5);
                            return data.labels[tooltipItem.index] + ': ' + currentValue + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        });
        
        // Gráfico de usuarios por departamento
        var deptCtx = document.getElementById('usersByDepartmentChart').getContext('2d');
        var deptChart = new Chart(deptCtx, {
            type: 'bar',
            data: <?php echo json_encode($chartData['departamentos']); ?>,
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
        
        // Gráfico de actividad de usuarios
        var activityCtx = document.getElementById('userActivityChart').getContext('2d');
        var activityChart = new Chart(activityCtx, {
            type: 'line',
            data: <?php echo json_encode($chartData['actividad']); ?>,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) {if (value % 1 === 0) {return value;}}
                        }
                    }]
                },
                tooltips: {
                    mode: 'index',
                    intersect: false
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
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