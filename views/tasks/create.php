<?php
require_once 'views/templates/header.php';
require_once 'views/templates/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Nueva Tarea</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php?controller=task&action=index">Tareas</a></li>
                        <li class="breadcrumb-item active">Nueva Tarea</li>
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
                        <i class="fas fa-plus-circle mr-1"></i> Crear Nueva Tarea
                    </h3>
                </div>
                <form method="post" action="index.php?controller=task&action=store">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="titulo">Título <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" placeholder="Ingrese el título de la tarea" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="proyecto_id">Proyecto <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="proyecto_id" name="proyecto_id" required>
                                        <option value="">Seleccione un proyecto</option>
                                        <?php foreach ($projects as $project): ?>
                                            <option value="<?php echo $project['id']; ?>" <?php echo (isset($selectedProject) && $selectedProject['id'] == $project['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($project['titulo']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="asignado_a">Asignar a</label>
                                    <select class="form-control select2" id="asignado_a" name="asignado_a">
                                        <option value="">Sin asignar</option>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <option value="<?php echo $usuario['id']; ?>">
                                                <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="estado">Estado <span class="text-danger">*</span></label>
                                    <select class="form-control" id="estado" name="estado" required>
                                        <option value="Pendiente" selected>Pendiente</option>
                                        <option value="En Progreso">En Progreso</option>
                                        <option value="Completado">Completado</option>
                                        <option value="Cancelado">Cancelado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
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
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_inicio">Fecha de Inicio <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_fin">Fecha de Finalización <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="4" placeholder="Ingrese una descripción detallada de la tarea"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="porcentaje_completado">Porcentaje Completado</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="porcentaje_completado" name="porcentaje_completado" min="0" max="100" value="0">
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="index.php?controller=task&action=index" class="btn btn-secondary">Cancelar</a>
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
        
        // Actualizar porcentaje si el estado es Completado
        $('#estado').on('change', function() {
            if ($(this).val() === 'Completado') {
                $('#porcentaje_completado').val(100);
            }
        });
    });
</script>

<?php require_once 'views/templates/footer.php'; ?>