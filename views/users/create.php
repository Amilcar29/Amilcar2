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
                    <h1 class="m-0">Nuevo Usuario</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php?controller=user&action=index">Usuarios</a></li>
                        <li class="breadcrumb-item active">Nuevo Usuario</li>
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
                        <i class="fas fa-user-plus mr-1"></i> Crear Nuevo Usuario
                    </h3>
                </div>
                <form method="post" action="index.php?controller=user&action=store">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombre">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ingrese el nombre" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="apellido">Apellido <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" placeholder="Ingrese el apellido" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Correo Electrónico <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Ingrese el correo electrónico" required>
                                </div>
                            </div>
							<div class="col-md-6">
                                <div class="form-group">
                                    <label for="area_id">Área</label>
                                    <select class="form-control" id="area_id" name="area_id">
                                        <option value="">Seleccione un área</option>
                                        <?php if(isset($areas) && $areas): ?>
                                        <?php foreach ($areas as $area): ?>
                                            <option value="<?php echo $area['id']; ?>"><?php echo htmlspecialchars($area['nombre']); ?></option>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Contraseña <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Ingrese la contraseña" required>
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="far fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
						<div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="departamento_id">Departamento</label>
                                    <select class="form-control" id="departamento_id" name="departamento_id" disabled>
                                        <option value="">Seleccione primero un área</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch mt-4">
                                        <input type="checkbox" class="custom-control-input" id="activo" name="activo" checked>
                                        <label class="custom-control-label" for="activo">Usuario Activo</label>
                                    </div>
                                </div>
                            </div>
								<div class="card-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="index.php?controller=user&action=index" class="btn btn-secondary">Cancelar</a>
                    </div>
							
                        </div>
                        <!--
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rol_id">Rol <span class="text-danger">*</span></label>
                                    <select class="form-control" id="rol_id" name="rol_id" required>
                                        <option value="">Seleccione un rol</option>
                                        <?php if(isset($roles) && $roles): ?>
                                        <?php foreach ($roles as $rol): ?>
                                            <option value="<?php echo $rol['id']; ?>"><?php echo htmlspecialchars($rol['nombre']); ?></option>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            
						
							
                        </div>
                        --!>

                    </div>
                    
                </form>
            </div>
        </div>
    </section>
</div>

<script>
    $(document).ready(function() {
        // Mostrar/ocultar contraseña
        $('#togglePassword').click(function() {
            const passwordField = $('#password');
            const passwordFieldType = passwordField.attr('type');
            
            if (passwordFieldType === 'password') {
                passwordField.attr('type', 'text');
                $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordField.attr('type', 'password');
                $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
        
        // Cargar departamentos cuando se selecciona un área
        $('#area_id').change(function() {
            var areaId = $(this).val();
            var departamentoSelect = $('#departamento_id');
            
            departamentoSelect.empty().append('<option value="">Seleccione un departamento</option>');
            
            if (areaId === '') {
                departamentoSelect.prop('disabled', true);
                return;
            }
            
            // Habilitar el select de departamentos
            departamentoSelect.prop('disabled', false);
            
            // Cargar departamentos mediante AJAX
            $.ajax({
                url: 'index.php?controller=user&action=getDepartmentsByArea',
                type: 'GET',
                data: { area_id: areaId },
                dataType: 'json',
                success: function(response) {
                    if (response.departments && response.departments.length > 0) {
                        $.each(response.departments, function(i, department) {
                            departamentoSelect.append('<option value="' + department.id + '">' + department.nombre + '</option>');
                        });
                    } else {
                        departamentoSelect.append('<option value="">No hay departamentos disponibles</option>');
                    }
                },
                error: function() {
                    alert('Error al cargar los departamentos');
                    departamentoSelect.prop('disabled', true);
                }
            });
        });
    });
</script>

<?php require_once 'views/templates/footer.php'; ?>