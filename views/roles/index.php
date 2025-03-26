<?php
// Verifica permisos: solo Administradores
if ($_SESSION['user_role'] > 1) {
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
                    <h1 class="m-0">Roles</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item active">Roles</li>
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
                            <i class="fas fa-user-tag mr-1"></i> Lista de Roles
                        </h3>
                        <div class="card-tools">
                            <a href="index.php?controller=role&action=create" class="btn btn-primary">
                                <i class="fas fa-plus"></i> <span class="d-none d-sm-inline-block">Nuevo Rol</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Tabla de roles -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 10%">ID</th>
                                    <th style="width: 30%">Nombre</th>
                                    <th>Descripción</th>
                                    <th style="width: 15%">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($roles) && is_array($roles)): ?>
                                    <?php foreach ($roles as $role): ?>
                                        <?php if (is_array($role)): ?>
                                            <tr>
                                                <td><?php echo isset($role['id']) ? $role['id'] : 'N/A'; ?></td>
                                                <td><?php echo isset($role['nombre']) ? htmlspecialchars($role['nombre']) : 'Sin nombre'; ?></td>
                                                <td><?php echo !empty($role['descripcion']) ? htmlspecialchars($role['descripcion']) : '<span class="text-muted">Sin descripción</span>'; ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="index.php?controller=role&action=edit&id=<?php echo isset($role['id']) ? $role['id'] : '0'; ?>" class="btn btn-xs btn-warning" title="Editar rol">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if (isset($role['id']) && $role['id'] > 5): // No permitir eliminar roles del sistema ?>
                                                            <button type="button" class="btn btn-xs btn-danger btn-delete" data-toggle="modal" data-target="#deleteModal" data-id="<?php echo $role['id']; ?>" title="Eliminar rol">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No hay roles disponibles</td>
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
                <p>¿Está seguro de que desea eliminar este rol?</p>
                <p class="text-danger"><strong>Advertencia:</strong> Esta acción no se puede deshacer y solo se permitirá si no hay usuarios asociados a este rol.</p>
            </div>
            <div class="modal-footer">
                <form action="index.php?controller=role&action=delete" method="post">
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