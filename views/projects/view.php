<?php
/**
 * Vista de detalles de un proyecto
 */
?>
<?php include 'views/templates/header.php'; ?>
<?php include 'views/templates/sidebar.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Detalles del Proyecto</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?controller=dashboard&action=index">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php?controller=project&action=index">Proyectos</a></li>
                        <li class="breadcrumb-item active">Detalles</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h5><i class="icon fas fa-check"></i> ¡Éxito!</h5>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['errors'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h5><i class="icon fas fa-ban"></i> Error</h5>
                    <ul>
                        <?php foreach ($_SESSION['errors'] as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; unset($_SESSION['errors']); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (isset($proyecto) && is_array($proyecto)): ?>
                <div class="row">
                    <div class="col-md-8">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title"><?php echo htmlspecialchars($proyecto['titulo']); ?></h3>
                                <div class="card-tools">
                                    <?php if (isset($accessControl) && $accessControl->hasPermission('edit_projects')): ?>
                                        <a href="index.php?controller=project&action=edit&id=<?php echo $proyecto['id']; ?>" class="btn btn-tool btn-sm">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                    <?php endif; ?>

                                    <?php if (isset($accessControl) && $accessControl->hasPermission('delete_projects')): ?>
                                        <button type="button" class="btn btn-tool btn-sm" data-toggle="modal" data-target="#modal-delete-project">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fecha de Inicio:</label>
                                            <p><?php echo date('d/m/Y', strtotime($proyecto['fecha_inicio'])); ?></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Fecha de Fin:</label>
                                            <p><?php echo date('d/m/Y', strtotime($proyecto['fecha_fin'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Estado:</label>
                                            <?php 
                                            $estadoClass = '';
                                            switch ($proyecto['estado']) {
                                                case 'Pendiente':
                                                    $estadoClass = 'badge-warning';
                                                    break;
                                                case 'En Progreso':
                                                    $estadoClass = 'badge-primary';
                                                    break;
                                                case 'Completado':
                                                    $estadoClass = 'badge-success';
                                                    break;
                                                case 'Cancelado':
                                                    $estadoClass = 'badge-danger';
                                                    break;
                                            }
                                            ?>
                                            <p><span class="badge <?php echo $estadoClass; ?>"><?php echo $proyecto['estado']; ?></span></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Prioridad:</label>
                                            <?php 
                                            $prioridadClass = '';
                                            switch ($proyecto['prioridad']) {
                                                case 'Baja':
                                                    $prioridadClass = 'badge-success';
                                                    break;
                                                case 'Media':
                                                    $prioridadClass = 'badge-info';
                                                    break;
                                                case 'Alta':
                                                    $prioridadClass = 'badge-warning';
                                                    break;
                                                case 'Urgente':
                                                    $prioridadClass = 'badge-danger';
                                                    break;
                                            }
                                            ?>
                                            <p><span class="badge <?php echo $prioridadClass; ?>"><?php echo $proyecto['prioridad']; ?></span></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Área:</label>
                                    <p><?php echo $proyecto['area_nombre'] ?? 'No asignada'; ?></p>
                                </div>
                                <div class="form-group">
                                    <label>Descripción:</label>
                                    <p><?php echo nl2br(htmlspecialchars($proyecto['descripcion'] ?? 'Sin descripción')); ?></p>
                                </div>
                                <div class="form-group">
                                    <label>Creado por:</label>
                                    <p><?php echo htmlspecialchars($proyecto['creado_por_nombre']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card card-secondary">
                            <div class="card-header">
                                <h3 class="card-title">Equipo del Proyecto</h3>
                                <?php if (isset($accessControl) && $accessControl->hasPermission('assign_project_users')): ?>
                                    <div class="card-tools">
                                        <a href="index.php?controller=project&action=assign&id=<?php echo $proyecto['id']; ?>" class="btn btn-tool btn-sm">
                                            <i class="fas fa-user-plus"></i> Asignar Usuarios
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <?php if (empty($usuarios)): ?>
                                    <p class="text-muted">No hay usuarios asignados a este proyecto.</p>
                                <?php else: ?>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <li class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($usuario['nombre_completo']); ?></strong><br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($usuario['rol']); ?></small>
                                                    </div>
                                                    <div>
                                                        <a href="mailto:<?php echo $usuario['email']; ?>" class="btn btn-sm btn-outline-secondary">
                                                            <i class="fas fa-envelope"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Tareas del Proyecto</h3>
                                <?php if (isset($accessControl) && $accessControl->hasPermission('create_tasks')): ?>
                                    <div class="card-tools">
                                        <a href="index.php?controller=task&action=create&proyecto_id=<?php echo $proyecto['id']; ?>" class="btn btn-tool btn-sm">
                                            <i class="fas fa-plus"></i> Nueva Tarea
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <?php if (empty($tareas)): ?>
                                    <p class="text-muted">No hay tareas asociadas a este proyecto.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Título</th>
                                                    <th>Asignado a</th>
                                                    <th>Fechas</th>
                                                    <th>Estado</th>
                                                    <th>Progreso</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($tareas as $tarea): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($tarea['titulo']); ?></td>
                                                        <td><?php echo htmlspecialchars($tarea['asignado_nombre'] ?? 'Sin asignar'); ?></td>
                                                        <td>
                                                            <small>
                                                                <i class="fas fa-calendar-alt"></i> 
                                                                <?php echo date('d/m/Y', strtotime($tarea['fecha_inicio'])); ?> - 
                                                                <?php echo date('d/m/Y', strtotime($tarea['fecha_fin'])); ?>
                                                            </small>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            $estadoClass = '';
                                                            switch ($tarea['estado']) {
                                                                case 'Pendiente':
                                                                    $estadoClass = 'badge-warning';
                                                                    break;
                                                                case 'En Progreso':
                                                                    $estadoClass = 'badge-primary';
                                                                    break;
                                                                case 'Completado':
                                                                    $estadoClass = 'badge-success';
                                                                    break;
                                                                case 'Cancelado':
                                                                    $estadoClass = 'badge-danger';
                                                                    break;
                                                            }
                                                            ?>
                                                            <span class="badge <?php echo $estadoClass; ?>"><?php echo $tarea['estado']; ?></span>
                                                        </td>
                                                        <td>
                                                            <div class="progress">
                                                                <div class="progress-bar bg-primary" role="progressbar" 
                                                                    style="width: <?php echo $tarea['porcentaje_completado']; ?>%" 
                                                                    aria-valuenow="<?php echo $tarea['porcentaje_completado']; ?>" 
                                                                    aria-valuemin="0" 
                                                                    aria-valuemax="100">
                                                                    <?php echo $tarea['porcentaje_completado']; ?>%
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <a href="index.php?controller=task&action=view&id=<?php echo $tarea['id']; ?>" class="btn btn-sm btn-info">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <?php if (isset($accessControl) && $accessControl->hasPermission('edit_tasks')): ?>
                                                                <a href="index.php?controller=task&action=edit&id=<?php echo $tarea['id']; ?>" class="btn btn-sm btn-primary">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> No se pudo encontrar la información del proyecto.
                    <a href="index.php?controller=project&action=index" class="alert-link">Volver a la lista de proyectos</a>.
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- Modal para confirmar eliminación del proyecto -->
<?php if (isset($proyecto) && is_array($proyecto)): ?>
<div class="modal fade" id="modal-delete-project">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Confirmar Eliminación</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar este proyecto?</p>
                <p class="text-danger">Esta acción eliminará todas las tareas y asignaciones asociadas.</p>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                <form action="index.php?controller=project&action=delete" method="POST">
                    <input type="hidden" name="id" value="<?php echo $proyecto['id']; ?>">
                    <button type="submit" class="btn btn-danger">Eliminar Proyecto</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'views/templates/footer.php'; ?>