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
                    <h1 class="m-0">Usuarios</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item active">Usuarios</li>
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
                            <i class="fas fa-users mr-1"></i> Lista de Usuarios
                        </h3>
                        <div class="card-tools">
                            <a href="index.php?controller=user&action=create" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> <span class="d-none d-sm-inline-block">Nuevo Usuario</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <form method="get" action="index.php" class="form-inline flex-wrap">
                                <input type="hidden" name="controller" value="user">
                                <input type="hidden" name="action" value="index">
                                
                                <div class="form-group mr-2 mb-2">
                                    <label for="rol_id" class="mr-2">Rol:</label>
                                    <select class="form-control form-control-sm" id="rol_id" name="rol_id">
                                        <option value="">Todos</option>
                                        <option value="1" <?php echo (isset($_GET['rol_id']) && $_GET['rol_id'] == '1') ? 'selected' : ''; ?>>Administrador</option>
                                        <option value="2" <?php echo (isset($_GET['rol_id']) && $_GET['rol_id'] == '2') ? 'selected' : ''; ?>>Gerente General</option>
                                        <option value="3" <?php echo (isset($_GET['rol_id']) && $_GET['rol_id'] == '3') ? 'selected' : ''; ?>>Gerente de Área</option>
                                        <option value="4" <?php echo (isset($_GET['rol_id']) && $_GET['rol_id'] == '4') ? 'selected' : ''; ?>>Jefe de Departamento</option>
                                        <option value="5" <?php echo (isset($_GET['rol_id']) && $_GET['rol_id'] == '5') ? 'selected' : ''; ?>>Colaborador</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mr-2 mb-2">
                                    <label for="area_id" class="mr-2">Área:</label>
                                    <select class="form-control form-control-sm" id="area_id" name="area_id">
                                        <option value="">Todas</option>
                                        <?php if(isset($areas) && $areas): ?>
                                        <?php foreach ($areas as $area): ?>
                                            <option value="<?php echo $area['id']; ?>" <?php echo (isset($_GET['area_id']) && $_GET['area_id'] == $area['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($area['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group mr-2 mb-2">
                                    <label for="activo" class="mr-2">Estado:</label>
                                    <select class="form-control form-control-sm" id="activo" name="activo">
                                        <option value="">Todos</option>
                                        <option value="1" <?php echo (isset($_GET['activo']) && $_GET['activo'] == '1') ? 'selected' : ''; ?>>Activo</option>
                                        <option value="0" <?php echo (isset($_GET['activo']) && $_GET['activo'] == '0') ? 'selected' : ''; ?>>Inactivo</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mr-2 mb-2">
                                    <label for="busqueda" class="mr-2">Buscar:</label>
                                    <input type="text" class="form-control form-control-sm" id="busqueda" name="busqueda" placeholder="Nombre o Email" value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : ''; ?>">
                                </div>
                                
                                <div class="form-group mr-2 mb-2">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-filter"></i> Filtrar
                                    </button>
                                    <a href="index.php?controller=user&action=index" class="btn btn-sm btn-secondary ml-1">
                                        <i class="fas fa-sync-alt"></i> Limpiar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Tabla de usuarios -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre Completo</th>
                                    <th class="d-none d-md-table-cell">Email</th>
                                    <th>Rol</th>
                                    <th class="d-none d-md-table-cell">Área / Departamento</th>
                                    <th class="d-none d-md-table-cell">Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($users)): ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellido']); ?></td>
                                            <td class="d-none d-md-table-cell"><?php echo htmlspecialchars($user['email']); ?></td>
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
                                                            echo 'badge-success';
                                                            break;
                                                        case 4:
                                                            echo 'badge-info';
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
                                            <td class="d-none d-md-table-cell">
                                                <?php 
                                                    $ubicacion = [];
                                                    if (!empty($user['area_nombre'])) {
                                                        $ubicacion[] = $user['area_nombre'];
                                                    }
                                                    if (!empty($user['departamento_nombre'])) {
                                                        $ubicacion[] = $user['departamento_nombre'];
                                                    }
                                                    echo !empty($ubicacion) ? htmlspecialchars(implode(' / ', $ubicacion)) : 'Sin asignar';
                                                ?>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <?php if (isset($user['activo']) && $user['activo']): ?>
                                                    <span class="badge badge-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="index.php?controller=user&action=view&id=<?php echo $user['id']; ?>" class="btn btn-xs btn-info" title="Ver detalle">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    <a href="index.php?controller=user&action=edit&id=<?php echo $user['id']; ?>" class="btn btn-xs btn-warning" title="Editar usuario">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <?php if (isset($user['id']) && $user['id'] != $_SESSION['user_id']): // No permitir desactivar al usuario actual ?>
                                                        <?php if (isset($user['activo']) && $user['activo']): ?>
                                                            <button type="button" class="btn btn-xs btn-danger toggle-status" data-toggle="modal" data-target="#statusModal" data-id="<?php echo $user['id']; ?>" data-action="deactivate" title="Desactivar usuario">
                                                                <i class="fas fa-user-slash"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-xs btn-success toggle-status" data-toggle="modal" data-target="#statusModal" data-id="<?php echo $user['id']; ?>" data-action="activate" title="Activar usuario">
                                                                <i class="fas fa-user-check"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No hay usuarios disponibles</td>
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
                                    <a class="page-link" href="<?php echo $currentPage <= 1 ? '#' : 'index.php?controller=user&action=index&page=' . ($currentPage - 1) . $queryParams; ?>">Anterior</a>
                                </li>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $currentPage == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="index.php?controller=user&action=index&page=<?php echo $i . $queryParams; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo $currentPage >= $totalPages ? '#' : 'index.php?controller=user&action=index&page=' . ($currentPage + 1) . $queryParams; ?>">Siguiente</a>
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

<!-- Modal para cambio de estado -->
<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Confirmar Cambio de Estado</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea cambiar el estado de este usuario?
            </div>
            <div class="modal-footer">
                <form action="index.php?controller=user&action=toggleStatus" method="post">
                    <input type="hidden" name="id" id="userId">
                    <input type="hidden" name="action" id="statusAction">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Configurar el modal de cambio de estado
        $('.toggle-status').on('click', function () {
            var id = $(this).data('id');
            var action = $(this).data('action');
            
            $('#userId').val(id);
            $('#statusAction').val(action);
            
            if (action === 'deactivate') {
                $('#statusModalLabel').text('Confirmar Desactivación');
                $('.modal-body').text('¿Está seguro de que desea desactivar este usuario? El usuario no podrá acceder al sistema.');
                $('.modal-footer .btn-primary').removeClass('btn-success').addClass('btn-danger');
            } else {
                $('#statusModalLabel').text('Confirmar Activación');
                $('.modal-body').text('¿Está seguro de que desea activar este usuario? El usuario podrá acceder nuevamente al sistema.');
                $('.modal-footer .btn-primary').removeClass('btn-danger').addClass('btn-success');
            }
        });
    });
</script>

<?php require_once 'views/templates/footer.php'; ?>