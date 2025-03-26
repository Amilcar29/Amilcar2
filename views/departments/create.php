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
                    <h1 class="m-0">Nuevo Departamento</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php?controller=department&action=index">Departamentos</a></li>
                        <li class="breadcrumb-item active">Nuevo Departamento</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Mensajes de alerta -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h5><i class="icon fas fa-ban"></i> Error</h5>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus-circle mr-1"></i> Crear Nuevo Departamento
                    </h3>
                </div>
                <form method="post" action="index.php?controller=department&action=store">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nombre">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ingrese el nombre del departamento" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="area_id">Área <span class="text-danger">*</span></label>
                            <select class="form-control select2" id="area_id" name="area_id" required>
                                <option value="">Seleccione un área</option>
                                <?php foreach ($areas as $area): ?>
                                    <option value="<?php echo $area['id']; ?>">
                                        <?php echo htmlspecialchars($area['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Ingrese una descripción para el departamento"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="jefe_id">Jefe de Departamento</label>
                            <select class="form-control select2" id="jefe_id" name="jefe_id">
                                <option value="">Seleccione un jefe (opcional)</option>
                                <?php foreach ($jefes as $jefe): ?>
                                    <option value="<?php echo $jefe['id']; ?>">
                                        <?php echo htmlspecialchars($jefe['nombre'] . ' ' . $jefe['apellido']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Seleccione un usuario con rol de Jefe de Departamento para asignarlo como responsable.</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="index.php?controller=department&action=index" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
    $(document).ready(function() {
        // Inicializar Select2 si está disponible
        if ($.fn.select2) {
            $('.select2').select2({
                theme: 'bootstrap4'
            });
        }
    });
</script>

<?php require_once 'views/templates/footer.php'; ?>