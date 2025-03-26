<?php
require_once 'views/templates/header.php';
require_once 'views/templates/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Tareas</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item active">Tareas</li>
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
                            <i class="fas fa-tasks mr-1"></i> Lista de Tareas
                        </h3>
                        <div class="card-tools">
                            <a href="index.php?controller=task&action=create" class="btn btn-primary">
                                <i class="fas fa-plus"></i> <span class="d-none d-sm-inline-block">Nueva Tarea</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <form method="get" action="index.php" class="form-inline flex-wrap">
                                <input type="hidden" name="controller" value="task">
                                <input type="hidden" name="action" value="index">
                                
                                <div class="form-group mr-2 mb-2">
                                    <label for="proyecto_id" class="mr-2">Proyecto:</label>
                                    <select class="form-control form-control-sm" id="proyecto_id" name="proyecto_id">
                                        <option value="">Todos</option>
                                        <?php foreach ($projects as $project): ?>
                                            <option value="<?php echo $project['id']; ?>" <?php echo (isset($_GET['proyecto_id']) && $_GET['proyecto_id'] == $project['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($project['titulo']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
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
                                
                                <?php if ($_SESSION['user_role'] <= 3): // Admin, Gerente General o Gerente de Área ?>
                                <div class="form-group mr-2 mb-2">
                                    <label for="asignado_a" class="mr-2">Asignado a:</label>
                                    <select class="form-control form-control-sm" id="asignado_a" name="asignado_a">
                                        <option value="">Todos</option>
                                        <option value="sin_asignar" <?php echo (isset($_GET['asignado_a']) && $_GET['asignado_a'] == 'sin_asignar') ? 'selected' : ''; ?>>Sin asignar</option>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <option value="<?php echo $usuario['id']; ?>" <?php echo (isset($_GET['asignado_a']) && $_GET['asignado_a'] == $usuario['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php endif; ?>
                                
                                <div class="form-group mr-2 mb-2">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-filter"></i> Filtrar
                                    </button>
                                    <a href="index.php?controller=task&action=index" class="btn btn-sm btn-secondary ml-1">
                                        <i class="fas fa-sync-alt"></i> Limpiar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Tabla de tareas -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 25%">Título</th>
                                    <th class="d-none d-md-table-cell">Proyecto</th>
                                    <th>Asignado a</th>
                                    <th class="d-none d-md-table-cell">Estado</th>
                                    <th>Fecha Fin</th>
                                    <th style="width: 15%">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($tasks)): ?>
                                    <?php foreach ($tasks as $task): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($task['titulo']); ?></td>
                                            <td class="d-none d-md-table-cell">
                                                <?php if (!empty($task['proyecto_titulo'])): ?>
                                                    <a href="index.php?controller=project&action=view&id=<?php echo $task['proyecto_id']; ?>">
                                                        <?php echo htmlspecialchars($task['proyecto_titulo']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Sin proyecto</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($task['asignado_nombre'])): ?>
                                                    <?php echo htmlspecialchars($task['asignado_nombre']); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Sin asignar</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="d-none d-md-table-cell">
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
                                                <span class="<?php echo (strtotime($task['fecha_fin']) < time() && $task['estado'] != 'Completado' && $task['estado'] != 'Cancelado') ? 'text-danger font-weight-bold' : ''; ?>">
                                                    <?php echo date('d/m/Y', strtotime($task['fecha_fin'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="index.php?controller=task&action=view&id=<?php echo $task['id']; ?>" class="btn btn-xs btn-info" title="Ver detalle">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    <?php if ($_SESSION['user_role'] <= 4 || $task['asignado_a'] == $_SESSION['user_id'] || $task['creado_por'] == $_SESSION['user_id']): ?>
                                                        <a href="index.php?controller=task&action=edit&id=<?php echo $task['id']; ?>" class="btn btn-xs btn-warning" title="Editar tarea">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($_SESSION['user_role'] <= 2 || $task['creado_por'] == $_SESSION['user_id']): ?>
                                                        <button type="button" class="btn btn-xs btn-danger btn-delete" data-toggle="modal" data-target="#deleteModal" data-id="<?php echo $task['id']; ?>" title="Eliminar tarea">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No hay tareas disponibles</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación si es necesaria -->
                    <?php if (isset($totalPages) && $totalPages > 1): ?>
                    <div class="mt-3">
                        <nav aria-label="Navegación de páginas">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo $currentPage <= 1 ? '#' : 'index.php?controller=task&action=index&page=' . ($currentPage - 1) . $queryParams; ?>">Anterior</a>
                                </li>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $currentPage == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="index.php?controller=task&action=index&page=<?php echo $i . $queryParams; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo $currentPage >= $totalPages ? '#' : 'index.php?controller=task&action=index&page=' . ($currentPage + 1) . $queryParams; ?>">Siguiente</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
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
                ¿Está seguro de que desea eliminar esta tarea? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <form action="index.php?controller=task&action=delete" method="post">
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