/**
 * Archivo principal de JavaScript para la aplicación
 * Sistema de Gestión de Proyectos y Tareas
 */

$(function () {
    'use strict';

    // Inicialización de componentes
    initializeComponents();
    
    // Configuración de eventos
    setupEventListeners();
    
    // Inicialización de gráficos si existen los contenedores
    initializeCharts();
    
    // Ajustes para vista móvil
    setupMobileView();
});

/**
 * Inicializa los componentes de la interfaz de usuario
 */
function initializeComponents() {
    // Activar tooltips de Bootstrap
    $('[data-toggle="tooltip"]').tooltip();
    
    // Activar popovers de Bootstrap
    $('[data-toggle="popover"]').popover();
    
    // Inicializar Select2 si existe
    if ($.fn.select2) {
        $('.select2').select2({
            language: {
                noResults: function() {
                    return "No se encontraron resultados";
                }
            },
            width: '100%'
        });
    }
    
    // Inicializar DatePicker para campos de fecha si existe
    if ($.fn.datepicker) {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true,
            language: 'es'
        });
    }
    
    // Inicializar DataTables si existe
    if ($.fn.DataTable) {
        $('.data-table').DataTable({
            "responsive": true,
            "autoWidth": false,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
            }
        });
    }
    
    // Auto-cerrar alertas después de 5 segundos
    setTimeout(function() {
        $('.alert:not(.alert-permanent)').fadeOut('slow');
    }, 5000);
}

/**
 * Configura escuchadores de eventos para elementos de la interfaz
 */
function setupEventListeners() {
    // Confirmación para eliminación
    $('.btn-delete').on('click', function(e) {
        if (!confirm('¿Está seguro de que desea eliminar este elemento? Esta acción no se puede deshacer.')) {
            e.preventDefault();
        }
    });
    
    // Actualización de porcentaje en tareas
    $('#estado').on('change', function() {
        if ($(this).val() === 'Completado') {
            $('#porcentaje_completado').val(100);
        }
    });
    
    // Validación de fechas
    $('#fecha_fin').on('change', function() {
        var fechaInicio = $('#fecha_inicio').val();
        var fechaFin = $(this).val();
        
        if (fechaInicio && fechaFin && fechaInicio > fechaFin) {
            alert('La fecha de finalización debe ser posterior a la fecha de inicio.');
            $(this).val('');
        }
    });
    
    // Habilitar/deshabilitar campos según selección
    $('select[data-toggle-field]').on('change', function() {
        var targetField = $(this).data('toggle-field');
        var enableValue = $(this).data('toggle-value');
        
        if ($(this).val() === enableValue) {
            $('#' + targetField).prop('disabled', false);
        } else {
            $('#' + targetField).prop('disabled', true);
        }
    });
    
    // Actualizar el sidebar activo
    $('.nav-sidebar .nav-link').each(function() {
        var link = $(this).attr('href');
        if (window.location.href.indexOf(link) !== -1) {
            $(this).addClass('active');
            $(this).closest('.nav-item').addClass('menu-open');
            $(this).closest('.nav-treeview').prev().addClass('active');
        }
    });
}

/**
 * Configura comportamientos específicos para vistas móviles
 */
function setupMobileView() {
    // Cerrar sidebar automáticamente después de hacer clic en un enlace en dispositivos móviles
    if ($(window).width() < 768) {
        $('.sidebar .nav-link').on('click', function() {
            if (!$(this).hasClass('has-treeview') || !$(this).parent().hasClass('menu-open')) {
                $('body').removeClass('sidebar-open');
            }
        });
    }
    
    // Redimensionar elementos dinámicamente al cambiar tamaño de ventana
    $(window).resize(function() {
        adjustLayout();
    });
    
    // Ajustar layout según el tamaño de la pantalla
    function adjustLayout() {
        if ($(window).width() < 768) {
            $('.card-title').addClass('h6');
            $('.btn-block-sm').addClass('btn-block');
        } else {
            $('.card-title').removeClass('h6');
            $('.btn-block-sm').removeClass('btn-block');
        }
    }
    
    // Inicializar ajustes de layout
    adjustLayout();
}

/**
 * Inicializa gráficos si existen los contenedores
 */
function initializeCharts() {
    // Gráfico de estado de proyectos
    if ($('#projectStatusChart').length > 0) {
        var projectCtx = document.getElementById('projectStatusChart').getContext('2d');
        var projectChartData = $('#projectStatusChart').data('chart');
        var projectChart = new Chart(projectCtx, {
            type: 'pie',
            data: projectChartData || {
                labels: ['Pendientes', 'En Progreso', 'Completados', 'Cancelados'],
                datasets: [{
                    data: [0, 0, 0, 0],
                    backgroundColor: ['#f8f9fa', '#007bff', '#28a745', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: function() {
                        return window.innerWidth < 768 ? 'bottom' : 'right';
                    }(),
                    labels: {
                        boxWidth: 12
                    }
                }
            }
        });
    }
    
    // Gráfico de estado de tareas
    if ($('#taskStatusChart').length > 0) {
        var taskCtx = document.getElementById('taskStatusChart').getContext('2d');
        var taskChartData = $('#taskStatusChart').data('chart');
        var taskChart = new Chart(taskCtx, {
            type: 'pie',
            data: taskChartData || {
                labels: ['Pendientes', 'En Progreso', 'Completadas', 'Canceladas'],
                datasets: [{
                    data: [0, 0, 0, 0],
                    backgroundColor: ['#f8f9fa', '#007bff', '#28a745', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: function() {
                        return window.innerWidth < 768 ? 'bottom' : 'right';
                    }(),
                    labels: {
                        boxWidth: 12
                    }
                }
            }
        });
    }
    
    // Gráfico de prioridad de tareas (si existe)
    if ($('#taskPriorityChart').length > 0) {
        var priorityCtx = document.getElementById('taskPriorityChart').getContext('2d');
        var priorityChartData = $('#taskPriorityChart').data('chart');
        var priorityChart = new Chart(priorityCtx, {
            type: 'bar',
            data: priorityChartData || {
                labels: ['Baja', 'Media', 'Alta', 'Urgente'],
                datasets: [{
                    label: 'Tareas por prioridad',
                    data: [0, 0, 0, 0],
                    backgroundColor: ['#17a2b8', '#6c757d', '#ffc107', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            precision: 0
                        }
                    }]
                }
            }
        });
    }
}

/**
 * Formatea una fecha para mostrar
 * 
 * @param {string} dateString Fecha en formato ISO (YYYY-MM-DD)
 * @param {string} format Formato deseado (default, short, long)
 * @return {string} Fecha formateada
 */
function formatDate(dateString, format = 'default') {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    
    switch (format) {
        case 'short':
            return date.toLocaleDateString('es-ES');
        case 'long':
            return date.toLocaleDateString('es-ES', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        default:
            return date.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
    }
}

/**
 * Muestra una notificación al usuario
 * 
 * @param {string} message Mensaje a mostrar
 * @param {string} type Tipo de notificación (success, error, warning, info)
 * @param {number} duration Duración en milisegundos (0 para no ocultar)
 */function showNotification(message, type = 'info', duration = 5000) {
    // Crear elemento para el contenedor de notificaciones si no existe
    if ($('#notification-container').length === 0) {
        $('body').append('<div id="notification-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; width: 300px;"></div>');
    }
    
    const notificationHtml = `
        <div class="alert alert-${type} alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            ${message}
        </div>
    `;
    
    const $notification = $(notificationHtml);
    $('#notification-container').prepend($notification);
    
    if (duration > 0) {
        setTimeout(function() {
            $notification.fadeOut('slow', function() {
                $(this).remove();
            });
        }, duration);
    }
}

/**
 * Actualiza el estado de una tarea mediante AJAX
 * 
 * @param {number} taskId ID de la tarea
 * @param {string} status Nuevo estado
 * @param {number} percentage Porcentaje de completado
 */
function updateTaskStatus(taskId, status, percentage) {
    $.ajax({
        url: 'index.php?controller=task&action=updateStatusAjax',
        type: 'POST',
        data: {
            id: taskId,
            estado: status,
            porcentaje_completado: percentage
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showNotification(response.message, 'success');
                // Actualizar UI si es necesario
                if (typeof refreshTaskView === 'function') {
                    refreshTaskView(taskId);
                }
            } else {
                showNotification(response.message, 'error');
            }
        },
        error: function() {
            showNotification('Error al actualizar el estado de la tarea', 'error');
        }
    });
}

/**
 * Maneja el ajuste responsivo de elementos UI en tablets y móviles
 */
function handleResponsiveUI() {
    if (window.matchMedia('(max-width: 767.98px)').matches) {
        // Ajustes específicos para móviles
        $('.desktop-only').hide();
        $('.mobile-only').show();
        $('.responsive-table').addClass('table-sm');
        $('.card-header-pills').addClass('flex-column');
    } else if (window.matchMedia('(max-width: 991.98px)').matches) {
        // Ajustes específicos para tablets
        $('.desktop-only').show();
        $('.mobile-only').hide();
        $('.responsive-table').removeClass('table-sm');
        $('.card-header-pills').removeClass('flex-column');
    } else {
        // Ajustes para escritorio
        $('.desktop-only').show();
        $('.mobile-only').hide();
        $('.responsive-table').removeClass('table-sm');
        $('.card-header-pills').removeClass('flex-column');
    }
}

// Ejecutar la función de manejo responsivo al cargar y al redimensionar
$(document).ready(function() {
    handleResponsiveUI();
    $(window).resize(function() {
        handleResponsiveUI();
    });
});