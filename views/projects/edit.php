<?php
/**
 * Vista para editar un proyecto existente
 */
?>
<?php include 'views/templates/header.php'; ?>
<?php include 'views/templates/sidebar.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Editar Proyecto</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?controller=dashboard&action=index">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php?controller=project&action=index">Proyectos</a></li>
                        <li class="breadcrumb-item active">Editar Proyecto</li>
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
                    <h3 class="card-title">Información del Proyecto</h3>
                </div>
                <form action="index.php?controller=project&action=update" method="POST">
                    <input type="hidden" name="id" value="<?php echo $proyecto['id']; ?>">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="titulo">Título *</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" placeholder="Título del proyecto" required
                                value="<?php echo isset($_SESSION['form_data']['titulo']) ? htmlspecialchars($_SESSION['form_data']['titulo']) : htmlspecialchars($proyecto['titulo']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="4" placeholder="Descripción detallada del proyecto"><?php echo isset($_SESSION['form_data']['descripcion']) ? htmlspecialchars($_SESSION['form_data']['descripcion']) : htmlspecialchars($proyecto['descripcion']); ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_inicio">Fecha de Inicio *</label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required
                                        value="<?php echo isset($_SESSION['form_data']['fecha_inicio']) ? htmlspecialchars($_SESSION['form_data']['fecha_inicio']) : htmlspecialchars($proyecto['fecha_inicio']); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_fin">Fecha de Fin *</label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required
                                        value="<?php echo isset($_SESSION['form_data']['fecha_fin']) ? htmlspecialchars($_SESSION['form_data']['fecha_fin']) : htmlspecialchars($proyecto['fecha_fin']); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="area_id">Área</label>
                                    <select class="form-control" id="area_id" name="area_id">
                                        <option value="">-- Seleccione un área --</option>
                                        <?php foreach ($areas as $area): ?>
                                            <option value="<?php echo $area['id']; ?>" 
                                                <?php 
                                                if (isset($_SESSION['form_data']['area_id'])) {
                                                    echo $_SESSION['form_data']['area_id'] == $area['id'] ? 'selected' : '';
                                                } else {
                                                    echo $proyecto['area_id'] == $area['id'] ? 'selected' : '';
                                                }
                                                ?>>
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
                                    <label for="estado">Estado *</label>
                                    <select class="form-control" id="estado" name="estado" required>
                                        <?php 
                                        $estados = ['Pendiente', 'En Progreso', 'Completado', 'Cancelado'];
                                        foreach ($estados as $estadoOpt): 
                                        ?>
                                            <option value="<?php echo $estadoOpt; ?>" 
                                                <?php 
                                                if (isset($_SESSION['form_data']['estado'])) {
                                                    echo $_SESSION['form_data']['estado'] == $estadoOpt ? 'selected' : '';
                                                } else {
                                                    echo $proyecto['estado'] == $estadoOpt ? 'selected' : '';
                                                }
                                                ?>>
                                                <?php echo $estadoOpt; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="prioridad">Prioridad *</label>
                                    <select class="form-control" id="prioridad" name="prioridad" required>
                                        <?php 
                                        $prioridades = ['Baja', 'Media', 'Alta', 'Urgente'];
                                        foreach ($prioridades as $prioridadOpt): 
                                        ?>
                                            <option value="<?php echo $prioridadOpt; ?>" 
                                                <?php 
                                                if (isset($_SESSION['form_data']['prioridad'])) {
                                                    echo $_SESSION['form_data']['prioridad'] == $prioridadOpt ? 'selected' : '';
                                                } else {
                                                    echo $proyecto['prioridad'] == $prioridadOpt ? 'selected' : '';
                                                }
                                                ?>>
                                                <?php echo $prioridadOpt; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        <a href="index.php?controller=project&action=view&id=<?php echo $proyecto['id']; ?>" class="btn btn-default">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<?php 
// Limpiar datos del formulario de la sesión
if (isset($_SESSION['form_data'])) {
    unset($_SESSION['form_data']);
}
?>

<?php include 'views/templates/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación de fechas
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    
    fechaInicio.addEventListener('change', function() {
        if (fechaFin.value && fechaInicio.value > fechaFin.value) {
            fechaFin.value = fechaInicio.value;
        }
    });
    
    fechaFin.addEventListener('change', function() {
        if (fechaInicio.value && fechaInicio.value > fechaFin.value) {
            alert('La fecha de fin no puede ser anterior a la fecha de inicio');
            fechaFin.value = fechaInicio.value;
        }
    });
});
</script>