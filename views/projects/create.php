<?php
// Verifica permisos
$allowedRoles = [1, 2, 3, 4]; // Admin, Gerente General, Gerente de Área, Jefe de Departamento
if (!in_array($_SESSION['user_role'], $allowedRoles)) {
    header('Location: index.php?controller=error&action=forbidden');
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
                    <h1 class="m-0">Crear Nuevo Proyecto</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php?controller=project&action=index">Proyectos</a></li>
                        <li class="breadcrumb-item active">Crear</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido principal -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
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
                    
                    <!-- Formulario de creación de proyecto -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Información del Proyecto</h3>
                        </div>
                        <form method="post" action="index.php?controller=project&action=store">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="titulo">Título del Proyecto <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="titulo" name="titulo" placeholder="Ingrese el título del proyecto" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="area_id">Área <span class="text-danger">*</span></label>
                                            <select class="form-control" id="area_id" name="area_id" required>
                                                <option value="">Seleccione un área</option>
                                                <?php foreach ($areas as $area): ?>
                                                    <option value="<?php echo $area['id']; ?>" <?php echo ($_SESSION['user_role'] == 3 && $_SESSION['user_area'] == $area['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($area['nombre']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="fecha_inicio">Fecha de Inicio <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="fecha_fin">Fecha de Finalización <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="estado">Estado <span class="text-danger">*</span></label>
                                            <select class="form-control" id="estado" name="estado" required>
                                                <option value="Pendiente">Pendiente</option>
                                                <option value="En Progreso">En Progreso</option>
                                                <option value="Completado">Completado</option>
                                                <option value="Cancelado">Cancelado</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="prioridad">Prioridad <span class="text-danger">*</span></label>
                                            <select class="form-control" id="prioridad" name="prioridad" required>
                                                <option value="Baja">Baja</option>
                                                <option value="Media" selected>Media</option>
                                                <option value="Alta">Alta</option>
                                                <option value="Urgente">Urgente</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="descripcion">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="4" placeholder="Describa el proyecto..."></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label>Asignar Usuarios</label>
                                    <select class="form-control select2" id="usuarios" name="usuarios[]" multiple data-placeholder="Seleccione usuarios para asignar">
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <option value="<?php echo $usuario['id']; ?>">
                                                <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido'] . ' (' . $usuario['rol_nombre'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="form-text text-muted">
                                        Puede seleccionar múltiples usuarios. Usted será asignado automáticamente.
                                    </small>
                                </div>
                            </div>
                            
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Crear Proyecto</button>
                                <a href="index.php?controller=project&action=index" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Script para Select2 y validación -->
<script>
$(document).ready(function() {
    // Inicializar Select2
    $('.select2').select2();
    
    // Validación de fechas
    $('#fecha_inicio, #fecha_fin').on('change', function() {
        var fechaInicio = $('#fecha_inicio').val();
        var fechaFin = $('#fecha_fin').val();
        
        if (fechaInicio && fechaFin) {
            if (fechaInicio > fechaFin) {
                alert('La fecha de finalización debe ser posterior a la fecha de inicio.');
                $('#fecha_fin').val('');
            }
        }
    });
    
    // Establecer fecha de inicio por defecto (hoy)
    var today = new Date();
    var dd = String(today.getDate()).padStart(2, '0');
    var mm = String(today.getMonth() + 1).padStart(2, '0');
    var yyyy = today.getFullYear();
    
    today = yyyy + '-' + mm + '-' + dd;
    $('#fecha_inicio').val(today);
});
</script>

<?php require_once 'views/templates/footer.php'; ?>