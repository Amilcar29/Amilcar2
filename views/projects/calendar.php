<?php
/**
 * Vista de calendario de proyectos
 */
?>
<?php include 'views/templates/header.php'; ?>
<?php include 'views/templates/sidebar.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Calendario de Proyectos</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?controller=dashboard&action=index">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php?controller=project&action=index">Proyectos</a></li>
                        <li class="breadcrumb-item active">Calendario</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </section>
</div>
<?php include 'views/templates/footer.php'; ?>

<!-- Fullcalendar JS -->
<script src="assets/js/moment.min.js"></script>
<script src="assets/js/fullcalendar.min.js"></script>
<script src="assets/js/locale/es.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        locale: 'es',
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día'
        },
        themeSystem: 'bootstrap',
        events: <?php echo $eventosJson; ?>,
        editable: false,
        droppable: false,
        eventClick: function(info) {
            window.location.href = 'index.php?controller=project&action=view&id=' + info.event.id;
        },
        eventDidMount: function(info) {
            $(info.el).tooltip({
                title: "Estado: " + info.event.extendedProps.estado + 
                      (info.event.extendedProps.area ? "<br>Área: " + info.event.extendedProps.area : ""),
                html: true,
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });
        }
    });
    
    calendar.render();
});
</script>