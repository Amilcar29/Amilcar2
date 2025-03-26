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
                    <h1 class="m-0">Nueva Área</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="index.php?controller=area&action=index">Áreas</a></li>
                        <li class="breadcrumb-item active">Nueva Área</li>
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
                        <i class="fas fa-plus-circle mr-1"></i> Crear Nueva Área
                    </h3>
                </div>
                <form method="post" action="index.php?controller=area&action=store">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nombre">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ingrese el nombre del área" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Ingrese una descripción para el área"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="gerente_id">Gerente</label>
                            <select class="form-control select2" id="gerente_id" name="gerente_id">
                                <option value="">Seleccione un gerente (opcional)</option>
                                <?php foreach ($gerentes as $gerente): ?>
                                    <option value="<?php echo $gerente['id']; ?>">
                                        <?php echo htmlspecialchars($gerente['nombre'] . ' ' . $gerente['apellido']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Seleccione un usuario con rol de Gerente de Área para asignarlo como responsable.</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="index.php?controller=area&action=index" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<?php require_once 'views/templates/footer.php'; ?>