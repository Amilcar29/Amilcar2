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
                    <h1 class="m-0">Departamentos</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item active">Departamentos</li>
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
                            <i class="fas fa-sitemap mr-1"></i> Lista de Departamentos
                        </h3>
                        <div class="card-tools">
                            <a href="index.php?controller=department&action=create" class="btn btn-primary">
                                <i class="fas fa-plus"></i> <span class="d-none d-sm-inline-block">Nuevo Departamento</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <form method="get" action="index.php" class="form-inline flex-wrap">
                                <input type="hidden" name="controller" value="department">
                                <input type="hidden" name="action" value="index">
                                
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
                                
                                <div class="form-group mr-2 mb-2">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-filter"></i> Filtrar
                                    </button>
                                    <a href="index.php?controller=department&action=index" class="btn btn-sm btn-secondary ml-1">
                                        <i class="fas fa-sync-alt"></i> Limpiar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Tabla de departamentos -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Área</th>
                                    <th class="d-none d-md-table-cell">Descripción</th>
                                    <th>Jefe</th>
                                    <th style="width: 15%">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($departments)): ?>
                                    <?php foreach ($departments as $department): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($department['nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($department['area_nombre']); ?></td>
                                            <td class="d-none d-md-table-cell">
                                                <?php echo !empty($department['descripcion']) ? htmlspecialchars($department['descripcion']) : '<span class="text-muted">Sin descripción</span>'; ?>
                                            </td>
                                            <td>
                                                <?php echo !empty($department['jefe_nombre']) ? htmlspecialchars($department['jefe_nombre']) : '<span class="text-muted">Sin asignar</span>'; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="index.php?controller=department&action=view&id=<?php echo $department['id']; ?>" class="btn btn-xs btn-info" title="Ver detalle">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="index.php?controller=department&action=edit&id=<?php echo $department['id']; ?>" class="btn btn-xs btn-warning" title="Editar departamento">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-xs btn-danger btn-delete" data-toggle="modal" data-target="#deleteModal" data-id="<?php echo $department['id']; ?>" title="Eliminar departamento">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No hay departamentos disponibles</td>
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
                <p>¿Está seguro de que desea eliminar este departamento?</p>
                <p class="text-danger"><strong>Advertencia:</strong> Esta acción no se puede deshacer y solo se permitirá si el departamento no tiene usuarios asociados.</p>
            </div>
            <div class="modal-footer">
                <form action="index.php?controller=department&action=delete" method="post">
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