<?php
session_start();
$usuario_logueado = isset($_SESSION['usuario']);
?>


<!-- Navbar según sesión -->
<?php 
    include("plantilla/cabecera.php");
?>

    <main class="container" style="padding-top: 140px; padding-bottom: 60px;">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                
                <div class="card bg-dark text-white border-secondary p-4 shadow-lg">
                    <h2 class="mb-3 text-center">Solicitar turno</h2>
                    <p class="text-white-50 text-center mb-4">
                        Horarios de atención: <strong>Lunes a Viernes de 8:00 a 18:00 h</strong> y <strong>Sábados de 8:00 a 12:00 h</strong>.
                    </p>

                    <?php if (!$usuario_logueado): ?>
                        <div class="alert alert-warning text-center" role="alert">
                            ¡Atención! Debes <a href="sesion.php" class="alert-link">iniciar sesión</a> o <a href="sesion.php" class="alert-link">registrarte</a> para poder solicitar un turno.
                        </div>
                    <?php endif; ?>

                    <form action="php/guardar_turno_be.php" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Marca del vehículo</label>
                                <input type="text" class="form-control bg-black text-white border-secondary" name="marca" placeholder="Ej. Chevrolet" required <?php echo !$usuario_logueado ? 'disabled' : ''; ?>>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Modelo del vehículo</label>
                                <input type="text" class="form-control bg-black text-white border-secondary" name="modelo" placeholder="Ej. Onix" required <?php echo !$usuario_logueado ? 'disabled' : ''; ?>>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Patente del vehículo</label>
                                <input type="text" class="form-control bg-black text-white border-secondary" name="patente" placeholder="Ej. AA-000-AA" required <?php echo !$usuario_logueado ? 'disabled' : ''; ?>>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">¿Qué problema tiene el auto o qué servicio deseas?</label>
                            <textarea class="form-control bg-black text-white border-secondary" name="descripcion" rows="3" placeholder="Ej. Cambio de pastillas de freno, revisión general..." required <?php echo !$usuario_logueado ? 'disabled' : ''; ?>></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha deseada</label>
                                <input type="date" class="form-control bg-black text-white border-secondary" name="fecha" min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" required <?php echo !$usuario_logueado ? 'disabled' : ''; ?>>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hora (L-V: 8h-18h / Sáb: 8h-12h)</label>
                                <input type="time" class="form-control bg-black text-white border-secondary" name="hora" min="08:00" max="18:00" required <?php echo !$usuario_logueado ? 'disabled' : ''; ?>>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control bg-black text-white border-secondary" name="telefono" placeholder="Ej. 3454123456" required<?php echo !$usuario_logueado ? 'disabled' : ''; ?>>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="urgente" name="urgente" value="1" <?php echo !$usuario_logueado ? 'disabled' : ''; ?>>
                            <label class="form-check-label text-warning" for="urgente">¿Es un servicio urgente?</label>
                        </div>

                        <button type="submit" class="btn btn-light w-100 fw-bold py-2 <?php echo !$usuario_logueado ? 'disabled' : ''; ?>">
                            <?php echo $usuario_logueado ? 'Solicitar Turno' : 'Inicia sesión para pedir turno'; ?>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </main>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>