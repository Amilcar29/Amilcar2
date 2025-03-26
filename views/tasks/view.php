<?php
// Verificar que exista la tarea y que el usuario tenga permisos para verla
if (!isset($task) || !$task) {
    header('Location: index.php?controller=error&action=not_found');
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
                    <h1 class="m-0">Detalle de Tarea</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php?controller=task&action=index">Tareas</a></li>
                        <li class="breadcrumb-item active">Detalle</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido principal -->
    <section class="content">
        <div class="container-fluid">
            <!-- Mensajes de error o éxito -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h5><i class="icon fas fa-ban"></i> Error</h5>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h5><i class="icon fas fa-check"></i> Éxito</h5>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Información principal de la tarea -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-tasks mr-2"></i> <?php echo htmlspecialchars($task['titulo']); ?>
                            </h3>
                            <div class="card-tools">
                                <?php if ($_SESSION['user_role'] <= 2 || $task['creado_por'] == $_SESSION['user_id']): ?>
                                    <a href="index.php?controller=task&action=edit&id=<?php echo $task['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <h5>Descripción</h5>
                                    <p class="text-justify">
                                        <?php echo nl2br(htmlspecialchars($task['descripcion'] ?? 'Sin descripción')); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <h5>Detalles</h5>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Estado</th>
                                            <td>
                                                <span class="badge 
                                                    <?php 
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
                                        </tr>
                                        <tr>
                                            <th>Prioridad</th>
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
                                        </tr>
                                        <tr>
                                            <th>Fecha de Inicio</th>
                                            <td><?php echo date('d/m/Y', strtotime($task['fecha_inicio'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Fecha de Finalización</th>
                                            <td><?php echo date('d/m/Y', strtotime($task['fecha_fin'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Porcentaje Completado</th>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $task['porcentaje_completado']; ?>%" aria-valuenow="<?php echo $task['porcentaje_completado']; ?>" aria-valuemin="0" aria-valuemax="100">
                                                        <?php echo $task['porcentaje_completado']; ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                
                                <div class="col-md-6">
                                    <h5>Asignación</h5>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Proyecto</th>
                                            <td>
                                                <a href="index.php?controller=project&action=view&id=<?php echo $task['proyecto_id']; ?>">
                                                    <?php echo htmlspecialchars($task['proyecto_titulo']); ?>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Asignado a</th>
                                            <td><?php echo htmlspecialchars($task['asignado_nombre'] ?? 'Sin asignar'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Creado por</th>
                                            <td><?php echo htmlspecialchars($task['creador_nombre']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Fecha de Creación</th>
                                            <td><?php echo date('d/m/Y H:i', strtotime($task['fecha_creacion'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Última Actualización</th>
                                            <td><?php echo date('d/m/Y H:i', strtotime($task['ultima_actualizacion'])); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Si el usuario es asignado o tiene permisos para actualizar -->
                            <?php if ($task['asignado_a'] == $_SESSION['user_id'] || $_SESSION['user_role'] <= 4): ?>
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <h5>Actualizar Estado</h5>
                                        <form action="index.php?controller=task&action=updateStatus" method="post" class="form-inline">
                                            <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                                            
                                            <div class="form-group mr-3">
                                                <label for="estado" class="mr-2">Estado:</label>
                                                <select class="form-control" id="estado" name="estado">
                                                    <option value="Pendiente" <?php echo ($task['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                                    <option value="En Progreso" <?php echo ($task['estado'] == 'En Progreso') ? 'selected' : ''; ?>>En Progreso</option>
                                                    <option value="Completado" <?php echo ($task['estado'] == 'Completado') ? 'selected' : ''; ?>>Completado</option>
                                                    <option value="Cancelado" <?php echo ($task['estado'] == 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                                                </select>
                                            </div>
                                            
                                            <div class="form-group mr-3">
                                                <label for="porcentaje_completado" class="mr-2">Porcentaje:</label>
                                                <input type="number" class="form-control" id="porcentaje_completado" name="porcentaje_completado" min="0" max="100" value="<?php echo $task['porcentaje_completado']; ?>">
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">Actualizar</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Comentarios -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-comments mr-2"></i> Comentarios
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="direct-chat-messages" style="height: 400px;">
                                <?php if (isset($comments) && !empty($comments)): ?>
                                    <?php foreach ($comments as $comment): ?>
                                        <div class="direct-chat-msg <?php echo ($comment['usuario_id'] == $_SESSION['user_id']) ? 'right' : ''; ?>">
                                            <div class="direct-chat-infos clearfix">
                                                <span class="direct-chat-name <?php echo ($comment['usuario_id'] == $_SESSION['user_id']) ? 'float-right' : 'float-left'; ?>">
                                                    <?php echo htmlspecialchars($comment['usuario_nombre']); ?>
                                                </span>
                                                <span class="direct-chat-timestamp <?php echo ($comment['usuario_id'] == $_SESSION['user_id']) ? 'float-left' : 'float-right'; ?>">
                                                    <?php echo date('d/m/Y H:i', strtotime($comment['fecha_creacion'])); ?>
                                                </span>
                                            </div>
                                            <img class="direct-chat-img" src="assets/img/avatar.png" alt="Avatar">
                                            <div class="direct-chat-text">
                                                <?php echo nl2br(htmlspecialchars($comment['comentario'])); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-center text-muted">No hay comentarios aún.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer">
                            <form action="index.php?controller=task&action=addComment" method="post">
                                <input type="hidden" name="tarea_id" value="<?php echo $task['id']; ?>">
                                <div class="input-group">
                                    <textarea name="comentario" placeholder="Escribir un comentario..." class="form-control" rows="2" required></textarea>
                                    <span class="input-group-append">
                                        <button type="submit" class="btn btn-primary">Enviar</button>
                                    </span>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar de información adicional -->
                <div class="col-md-4">
                    <!-- Historial de cambios -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-history mr-2"></i> Historial de Cambios
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="timeline timeline-inverse">
                                <?php if (isset($history) && !empty($history)): ?>
                                    <?php foreach ($history as $entry): ?>
                                        <li>
                                            <i class="fas fa-edit bg-primary"></i>
                                            <div class="timeline-item">
                                                <span class="time">
                                                    <i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($entry['fecha_modificacion'])); ?>
                                                </span>
                                                <h3 class="timeline-header">
                                                    <a href="#"><?php echo htmlspecialchars($entry['usuario_nombre']); ?></a> modificó la tarea
                                                </h3>
                                                <div class="timeline-body">
                                                    <strong>Campo:</strong> 
                                                    <?php 
                                                    switch ($entry['campo_modificado']) {
                                                        case 'estado':
                                                            echo 'Estado';
                                                            break;
                                                        case 'porcentaje_completado':
                                                            echo 'Porcentaje completado';
                                                            break;
                                                        case 'fecha_inicio':
                                                            echo 'Fecha de inicio';
                                                            break;
                                                        case 'fecha_fin':
                                                            echo 'Fecha de finalización';
                                                            break;
                                                        case 'asignado_a':
                                                            echo 'Asignado a';
                                                            break;
                                                        case 'prioridad':
                                                            echo 'Prioridad';
                                                            break;
                                                        default:
                                                            echo htmlspecialchars($entry['campo_modificado']);
                                                    }
                                                    ?>
                                                    <br>
                                                    <strong>Valor anterior:</strong> <?php echo htmlspecialchars($entry['valor_anterior'] ?? 'N/A'); ?><br>
                                                    <strong>Nuevo valor:</strong> <?php echo htmlspecialchars($entry['valor_nuevo'] ?? 'N/A'); ?>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li>
                                        <div class="timeline-item">
                                            <div class="timeline-body text-center py-3">
                                                No hay cambios registrados aún.
                                            </div>
                                        </div>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <i class="fas fa-clock bg-gray"></i>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Acciones adicionales -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-cog mr-2"></i> Acciones
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="btn-group-vertical w-100">
                                <?php if ($_SESSION['user_role'] <= 2 || $task['creado_por'] == $_SESSION['user_id']): ?>
                                    <a href="index.php?controller=task&action=edit&id=<?php echo $task['id']; ?>" class="btn btn-default">
                                        <i class="fas fa-edit mr-2"></i> Editar Tarea
                                    </a>
                                    
                                    <?php if ($_SESSION['user_role'] <= 2): ?>
                                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteTaskModal">
                                            <i class="fas fa-trash mr-2"></i> Eliminar Tarea
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <a href="index.php?controller=task&action=index" class="btn btn-default">
                                    <i class="fas fa-list mr-2"></i> Volver a Lista
                                </a>
                                
                                <?php if ($task['proyecto_id']): ?>
                                    <a href="index.php?controller=project&action=view&id=<?php echo $task['proyecto_id']; ?>" class="btn btn-default">
                                        <i class="fas fa-project-diagram mr-2"></i> Ver Proyecto
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (isset($task['asignado_a']) && !empty($task['asignado_a']) && $task['asignado_a'] != $_SESSION['user_id']): ?>
                                    <a href="index.php?controller=user&action=view&id=<?php echo $task['asignado_a']; ?>" class="btn btn-default">
                                        <i class="fas fa-user mr-2"></i> Ver Perfil del Asignado
                                    </a>
                                <?php endif; ?>
                                
                                <a href="#" class="btn btn-info" onclick="window.print();">
                                    <i class="fas fa-print mr-2"></i> Imprimir
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal de eliminación -->
<?php if ($_SESSION['user_role'] <= 2): ?>
    <div class="modal fade" id="deleteTaskModal" tabindex="-1" role="dialog" aria-labelledby="deleteTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteTaskModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    ¿Está seguro de que desea eliminar esta tarea? Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form action="index.php?controller=task&action=delete" method="post">
                        <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'views/templates/footer.php'; ?>