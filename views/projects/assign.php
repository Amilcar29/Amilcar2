<?php
/**
 * Vista para asignar usuarios a un proyecto
 */
?>
<?php include 'views/templates/header.php'; ?>
<?php include 'views/templates/sidebar.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Asignar Usuarios al Proyecto</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?controller=dashboard&action=index">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php?controller=project&action=index">Proyectos</a></li>
                        <li class="breadcrumb-item"><a href="index.php?controller=project&action=view&id=<?php echo $proyecto['id']; ?>">Detalles</a></li>
                        <li class="breadcrumb-item active">Asignar Usuarios</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
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

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Asignar Usuarios al Proyecto: <?php echo htmlspecialchars($proyecto['titulo']); ?></h3>
                </div>
                <form action="index.php?controller=project&action=saveAssignments" method="POST">
                    <input type="hidden" name="proyecto_id" value="<?php echo $proyecto['id']; ?>">
                    <div class="card-body">
                        <div class="form-group">
                            <label>Seleccione los usuarios a asignar:</label>
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <div class="input-group">
                                                <input type="text" id="search-user" class="form-control" placeholder="Buscar usuario...">
                                                <div class="input-group-append">
                                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body table-responsive p-0" style="max-height: 400px; overflow-y: auto;">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 40px;">
                                                            <div class="icheck-primary">
                                                                <input type="checkbox" id="check-all">
                                                                <label for="check-all"></label>
                                                            </div>
                                                        </th>
                                                        <th>Nombre</th>
                                                        <th>Email</th>
                                                        <th>Rol</th>
                                                        <th>Área</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($usuarios as $usuario): ?>
                                                        <tr class="user-row">
                                                            <td>
                                                                <div class="icheck-primary">
                                                                    <input type="checkbox" name="usuarios[]" id="user-<?php echo $usuario['id']; ?>" value="<?php echo $usuario['id']; ?>" 
                                                                        <?php echo in_array($usuario['id'], $usuariosAsignados) ? 'checked' : ''; ?>>
                                                                    <label for="user-<?php echo $usuario['id']; ?>"></label>
                                                                </div>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($usuario['nombre_completo']); ?></td>
                                                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                                            <td><?php echo htmlspecialchars($usuario['rol']); ?></td>
                                                            <td><?php echo htmlspecialchars($usuario['area'] ?? 'No asignada'); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Guardar Asignaciones</button>
                        <a href="index.php?controller=project&action=view&id=<?php echo $proyecto['id']; ?>" class="btn btn-default">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<?php include 'views/templates/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Buscador de usuarios
    const searchInput = document.getElementById('search-user');
    const userRows = document.querySelectorAll('.user-row');
    
    searchInput.addEventListener('keyup', function() {
        const searchText = this.value.toLowerCase();
        
        userRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(searchText)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    
    // Selector "Marcar todos"
    const checkAll = document.getElementById('check-all');
    const checkboxes = document.querySelectorAll('input[name="usuarios[]"]');
    
    checkAll.addEventListener('change', function() {
        const isChecked = this.checked;
        
        checkboxes.forEach(checkbox => {
            // Solo cambiar los checkboxes de filas visibles
            if (checkbox.closest('tr').style.display !== 'none') {
                checkbox.checked = isChecked;
            }
        });
    });
    
    // Actualizar estado del checkAll cuando se cambian checkboxes individuales
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateCheckAllState);
    });
    
    function updateCheckAllState() {
        let allChecked = true;
        let allUnchecked = true;
        
        checkboxes.forEach(checkbox => {
            // Solo considerar los checkboxes de filas visibles
            if (checkbox.closest('tr').style.display !== 'none') {
                if (checkbox.checked) {
                    allUnchecked = false;
                } else {
                    allChecked = false;
                }
            }
        });
        
        if (allChecked) {
            checkAll.checked = true;
            checkAll.indeterminate = false;
        } else if (allUnchecked) {
            checkAll.checked = false;
            checkAll.indeterminate = false;
        } else {
            checkAll.indeterminate = true;
        }
    }
    
    // Inicializar estado del checkAll
    updateCheckAllState();
});
</script>