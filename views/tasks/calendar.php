<?php
require_once 'views/templates/header.php';
require_once 'views/templates/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Calendario de Tareas</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php?controller=task&action=index">Tareas</a></li>
                        <li class="breadcrumb-item active">Calendario</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-alt mr-1"></i> Calendario de Tareas
                        </h3>
                        <div class="card-tools">
                            <a href="index.php?controller=task&action=create" class="btn btn-primary">
                                <i class="fas fa-plus"></i> <span class="d-none d-sm-inline-block">Nueva Tarea</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <form method="get" action="index.php" class="form-inline flex-wrap" id="filtroCalendario">
                                <input type="hidden" name="controller" value="task">
                                <input type="hidden" name="action" value="calendar">
                                
                                <div class="form-group mr-2 mb-2">
                                    <label for="estado" class="mr-2">Estado:</label>
                                    <select class="form-control form-control-sm" id="estado" name="estado" onchange="document.getElementById('filtroCalendario').submit();">
                                        <option value="">Todos</option>
                                        <option value="Pendiente" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                        <option value="En Progreso" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'En Progreso') ? 'selected' : ''; ?>>En Progreso</option>
                                        <option value="Completado" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Completado') ? 'selected' : ''; ?>>Completado</option>
                                        <option value="Cancelado" <?php echo (isset($_GET['estado']) && $_GET['estado'] == 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mr-2 mb-2">
                                    <label for="prioridad" class="mr-2">Prioridad:</label>
                                    <select class="form-control form-control-sm" id="prioridad" name="prioridad" onchange="document.getElementById('filtroCalendario').submit();">
                                        <option value="">Todas</option>
                                        <option value="Baja" <?php echo (isset($_GET['prioridad']) && $_GET['prioridad'] == 'Baja') ? 'selected' : ''; ?>>Baja</option>
                                        <option value="Media" <?php echo (isset($_GET['prioridad']) && $_GET['prioridad'] == 'Media') ? 'selected' : ''; ?>>Media</option>
                                        <option value="Alta" <?php echo (isset($_GET['prioridad']) && $_GET['prioridad'] == 'Alta') ? 'selected' : ''; ?>>Alta</option>
                                        <option value="Urgente" <?php echo (isset($_GET['prioridad']) && $_GET['prioridad'] == 'Urgente') ? 'selected' : ''; ?>>Urgente</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mr-2 mb-2">
                                    <a href="index.php?controller=task&action=calendar" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-sync-alt"></i> Limpiar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Calendario -->
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal de detalles de tarea -->
<div class="modal fade" id="taskModal" tabindex="-1" role="dialog" aria-labelledby="taskModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskModalLabel">Detalle de Tarea</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                    <p>Cargando detalles de la tarea...</p>
                </div>
                <div id="taskDetails" style="display: none;">
                    <div class="form-group">
                        <label>Título:</label>
                        <p id="taskTitle" class="font-weight-bold"></p>
                    </div>
                    
                    <div class="form-group">
                        <label>Proyecto:</label>
                        <p id="taskProject"></p>
                    </div>
                    
                    <div class="form-group">
                        <label>Descripción:</label>
                        <p id="taskDescription"></p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Estado:</label>
                                <p id="taskStatus"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Prioridad:</label>
                                <p id="taskPriority"></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha Inicio:</label>
                                <p id="taskStartDate"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Fecha Fin:</label>
                                <p id="taskEndDate"></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Asignado a:</label>
                        <p id="taskAssigned"></p>
                    </div>
                    
                    <div class="form-group">
                        <label>Progreso:</label>
                        <div class="progress">
                            <div id="taskProgress" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                        </div>
                        <small id="taskProgressText" class="form-text text-muted text-right">0%</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <a href="#" id="viewTaskButton" class="btn btn-primary">Ver Detalle Completo</a>
            </div>
        </div>
    </div>
</div>

<!-- Incluir FullCalendar -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales/es.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        
        var calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            initialView: 'dayGridMonth',
            locale: 'es',
            navLinks: true,
            businessHours: true,
            editable: false,
            selectable: true,
            events: <?php echo json_encode($calendarTasks); ?>,
            eventClick: function(info) {
                // Mostrar modal con detalles de la tarea
                $('#taskTitle').text('Cargando...');
                $('#taskProject').text('Cargando...');
                $('#taskDescription').text('Cargando...');
                $('#taskStatus').text('Cargando...');
                $('#taskPriority').text('Cargando...');
                $('#taskStartDate').text('Cargando...');
                $('#taskEndDate').text('Cargando...');
                $('#taskAssigned').text('Cargando...');
                $('#taskProgress').css('width', '0%');
                $('#taskProgressText').text('0%');
                
                // Mostrar spinner y ocultar detalles
                $('#taskDetails').hide();
                $('.fa-spinner').parent().show();
                
                // Establecer enlace para ver detalle completo
                $('#viewTaskButton').attr('href', 'index.php?controller=task&action=view&id=' + info.event.id);
                
                // Abrir el modal
                $('#taskModal').modal('show');
                
                // Cargar detalles de la tarea mediante AJAX
                $.ajax({
                    url: 'index.php?controller=task&action=getTaskDetails',
                    type: 'GET',
                    data: {
                        id: info.event.id
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            var task = response.task;
                            
                            // Actualizar contenido del modal
                            $('#taskTitle').text(task.titulo);
                            $('#taskProject').text(task.proyecto_titulo || 'Sin proyecto');
                            $('#taskDescription').text(task.descripcion || 'Sin descripción');
                            
                            // Estado con badge
                            var statusClass = 'badge-secondary';
                            switch (task.estado) {
                                case 'En Progreso':
                                    statusClass = 'badge-primary';
                                    break;
                                case 'Completado':
                                    statusClass = 'badge-success';
                                    break;
                                case 'Cancelado':
                                    statusClass = 'badge-danger';
                                    break;
                            }
                            $('#taskStatus').html('<span class="badge ' + statusClass + '">' + task.estado + '</span>');
                            
                            // Prioridad con badge
                            var priorityClass = 'badge-secondary';
                            switch (task.prioridad) {
                                case 'Baja':
                                    priorityClass = 'badge-info';
                                    break;
                                case 'Media':
                                    priorityClass = 'badge-secondary';
                                    break;
                                case 'Alta':
                                    priorityClass = 'badge-warning';
                                    break;
                                case 'Urgente':
                                    priorityClass = 'badge-danger';
                                    break;
                            }
                            $('#taskPriority').html('<span class="badge ' + priorityClass + '">' + task.prioridad + '</span>');
                            
                            // Fechas formateadas
                            $('#taskStartDate').text(formatDate(task.fecha_inicio));
                            $('#taskEndDate').text(formatDate(task.fecha_fin));
                            
                            // Asignado
                            $('#taskAssigned').text(task.asignado_nombre || 'Sin asignar');
                            
                            // Progreso
                            $('#taskProgress').css('width', task.porcentaje_completado + '%');
                            $('#taskProgressText').text(task.porcentaje_completado + '%');
                            
                            // Ocultar spinner y mostrar detalles
                            $('.fa-spinner').parent().hide();
                            $('#taskDetails').show();
                        } else {
                            alert('Error al cargar los detalles de la tarea');
                            $('#taskModal').modal('hide');
                        }
                    },
                    error: function() {
                        alert('Error de comunicación al cargar los detalles');
                        $('#taskModal').modal('hide');
                    }
                });
                
                // Prevenir navegación al evento
                info.jsEvent.preventDefault();
            },
            dateClick: function(info) {
                // Redirigir a la página de creación de tarea con la fecha seleccionada
                window.location.href = 'index.php?controller=task&action=create&fecha=' + info.dateStr;
            }
        });
        
        calendar.render();
        
        // Ajustar tamaño del calendario al redimensionar la ventana
        window.addEventListener('resize', function() {
            calendar.updateSize();
        });
    });
    
    // Función para formatear fechas
    function formatDate(dateStr) {
        var date = new Date(dateStr);
        var day = date.getDate().toString().padStart(2, '0');
        var month = (date.getMonth() + 1).toString().padStart(2, '0');
        var year = date.getFullYear();
        
        return day + '/' + month + '/' + year;
    }
</script>

<?php require_once 'views/templates/footer.php'; ?>