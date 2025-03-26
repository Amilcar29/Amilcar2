<footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>Versión</b> <?php echo APP_VERSION; ?>
            </div>
            <strong>Copyright &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?></strong>
            Todos los derechos reservados.
        </footer>
    </div>
    <!-- ./wrapper -->

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.1.0/dist/js/adminlte.min.js"></script>
    <!-- ChartJS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    <!-- Scripts personalizados -->
    <script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
    
    <?php if (isset($_SESSION['success']) || isset($_SESSION['error']) || isset($_SESSION['warning']) || isset($_SESSION['info'])): ?>
    <script>
        $(document).ready(function() {
            // Auto dismiss alerts after 5 seconds
            window.setTimeout(function() {
                $(".alert").fadeTo(500, 0).slideUp(500, function(){
                    $(this).remove(); 
                });
            }, 5000);
        });
    </script>
    <?php endif; ?>
</body>
</html>