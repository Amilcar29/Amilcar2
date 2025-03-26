<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h3 class="m-0">
                            <i class="fas fa-exclamation-triangle mr-2"></i> Error
                        </h3>
                    </div>
                    <div class="card-body text-center">
                        <div class="error-page mb-4">
                            <i class="fas fa-bug fa-5x text-muted mb-4"></i>
                        </div>
                        <h3 class="mb-3">Ha ocurrido un error inesperado.</h3>
                        <p class="lead">
                            <?php echo isset($_SESSION['error']) ? $_SESSION['error'] : 'No se pudo procesar su solicitud. Por favor, intente nuevamente.'; ?>
                            <?php if (isset($_SESSION['error'])) unset($_SESSION['error']); ?>
                        </p>
                        <div class="mt-4">
                            <a href="index.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-home mr-2"></i> Volver al inicio
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>