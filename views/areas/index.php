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
                    <h1 class="m-0">Áreas</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item active">Áreas</li>
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
                            <i class="fas fa-building mr-1"></i> Lista de Áreas
                        </h3>
                        <div class="card-tools">
                            <a href="index.php?controller=area&action=create" class="btn btn-primary">
                                <i class="fas fa-plus"></i> <span class="d-none d-sm-inline-block">Nueva Área</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Tabla de áreas -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 30%">Nombre</th>
                                    <th class="d-none d-md-table-cell">Descripción</th>
                                    <th>Gerente</th>
                                    <th style="width: 15%">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($areas)): ?>
                                    <?php foreach ($areas as $area): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($area['nombre']); ?></td>
                                            <td class="d-none d-md-table-cell">
                                                <?php echo !empty($area['descripcion']) ? htmlspecialchars($area['descripcion']) : '<span class="text-muted">Sin descripción</span>'; ?>
                                            </td>
                                            <td>
                                                <?php echo !empty($area['gerente_nombre']) ? htmlspecialchars($area['gerente_nombre']) : '<span class="text-muted">Sin asignar</span>'; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="index.php?controller=area&action=view&id=<?php echo $area['id']; ?>" class="btn btn-xs btn-info" title="Ver detalle">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="index.php?controller=area&action=edit&id=<?php echo $area['id']; ?>" class="btn btn-xs btn-warning" title="Editar área">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-xs btn-danger btn-delete" data-toggle="modal" data-target="#deleteModal" data-id="<?php echo $area['id']; ?>" title="Eliminar área">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No hay áreas disponibles</td>
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
                <p>¿Está seguro de que desea eliminar esta área?</p>
                <p class="text-danger"><strong>Advertencia:</strong> Esta acción no se puede deshacer y solo se permitirá si el área no tiene departamentos o proyectos asociados.</p>
            </div>
            <div class="modal-footer">
                <form action="index.php?controller=area&action=delete" method="post">
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