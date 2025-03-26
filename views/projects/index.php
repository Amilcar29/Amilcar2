<?php
require_once 'views/templates/header.php';
require_once 'views/templates/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Proyectos</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item active">Proyectos</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Mensajes de alerta -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h5><i class="icon fas fa-check"></i> Éxito</h5>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h5><i class="icon fas fa-ban"></i> Error</h5>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <h3 class="card-title">
                            <i class="fas fa-project-diagram mr-1"></i> Lista de Proyectos
                        </h3>
                        <div class="card-tools">
                            <?php if ($_SESSION['user_role'] <= 4): // Hasta Jefe de Departamento puede crear ?>
                                <a href="index.php?controller=project&action=create" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> <span class="d-none d-sm-inline-block">Nuevo Proyecto</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <form method="get" action="index.php" class="form-inline flex-wrap">
                                <input type="hidden" name="controller" value="project">
                                <input type="hidden" name="action" value="index">
                                
                                <div class="form-group mr-2 mb-2">
                                    <label for="estado" class="mr-2">Estado:</label>
                                    <select class="form-control form-control-sm" id="estado" name="estado">
                                        <option value="">Todos</option>
                                        <option value="Pendiente" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                        <option value="En Progreso" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'En Progreso') ? 'selected' : ''; ?>>En Progreso</option>
                                        <option value="Completado" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Completado') ? 'selected' : ''; ?>>Completado</option>
                                        <option value="Cancelado" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mr-2 mb-2">
                                    <label for="prioridad" class="mr-2">Prioridad:</label>
                                    <select class="form-control form-control-sm" id="prioridad" name="prioridad">
                                        <option value="">Todas</option>
                                        <option value="Baja" <?php echo (isset($_GET['prioridad']) && $_GET['prioridad'] == 'Baja') ? 'selected' : ''; ?>>Baja</option>
                                        <option value="Media" <?php echo (isset($_GET['prioridad']) && $_GET['prioridad'] == 'Media') ? 'selected' : ''; ?>>Media</option>
                                        <option value="Alta" <?php echo (isset($_GET['prioridad']) && $_GET['prioridad'] == 'Alta') ? 'selected' : ''; ?>>Alta</option>
                                        <option value="Urgente" <?php echo (isset($_GET['prioridad']) && $_GET['prioridad'] == 'Urgente') ? 'selected' : ''; ?>>Urgente</option>
                                    </select>
                                </div>
                                
                                <?php if ($_SESSION['user_role'] <= 2 && !empty($areas)): // Solo admin y gerente general ven todas las áreas ?>
                                <div class="form-group mr-2 mb-2">
                                    <label for="area_id" class="mr-2">Área:</label>
                                    <select class="form-control form-control-sm" id="area_id" name="area_id">
                                        <option value="">Todas</option>
                                        <?php foreach ($areas as $area): ?>
                                            <option value="<?php echo $area['id']; ?>" <?php echo (isset($_GET['area_id']) && $_GET['area_id'] == $area['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($area['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                                
                                <div class="form-group mr-2 mb-2">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-filter"></i> Filtrar
                                    </button>
                                    <a href="index.php?controller=project&action=index" class="btn btn-sm btn-secondary ml-1">
                                        <i class="fas fa-sync-alt"></i> Limpiar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Tabla de proyectos -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 30%">Título</th>
                                    <th class="d-none d-md-table-cell">Área</th>
                                    <th>Estado</th>
                                    <th class="d-none d-md-table-cell">Prioridad</th>
                                    <th class="d-none d-md-table-cell">Fecha Fin</th>
                                    <th style="width: 15%">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($projects)): ?>
                                    <?php foreach ($projects as $project): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($project['titulo']); ?></td>
                                            <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($project['area_nombre'] ?? 'Sin asignar'); ?></td>
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
                                            <td class="d-none d-md-table-cell">
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
                                            <td class="d-none d-md-table-cell"><?php echo date('d/m/Y', strtotime($project['fecha_fin'])); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="index.php?controller=project&action=view&id=<?php echo $project['id']; ?>" class="btn btn-xs btn-info" title="Ver detalle">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    <?php if ($_SESSION['user_role'] <= 3 || $_SESSION['user_id'] == $project['creado_por']): ?>
                                                        <a href="index.php?controller=project&action=edit&id=<?php echo $project['id']; ?>" class="btn btn-xs btn-warning" title="Editar proyecto">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($_SESSION['user_role'] <= 2): // Solo admin y gerente general pueden eliminar ?>
                                                        <button type="button" class="btn btn-xs btn-danger btn-delete" data-toggle="modal" data-target="#deleteModal" data-id="<?php echo $project['id']; ?>" title="Eliminar proyecto">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No hay proyectos disponibles</td>
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

<!-- Modal de eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea eliminar este proyecto? Esta acción no se puede deshacer y eliminará todas las tareas asociadas.
            </div>
            <div class="modal-footer">
                <form action="index.php?controller=project&action=delete" method="post">
                    <input type="hidden" name="id" id="deleteId">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Configurar el modal de eliminación
        $('#deleteModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            $('#deleteId').val(id);
        });
    });
</script>

<?php require_once 'views/templates/footer.php'; ?>