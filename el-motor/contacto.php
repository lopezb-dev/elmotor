<?php
session_start();
    
include("plantilla/cabecera.php");
?>

    <main class="container" style="padding-top: 140px; padding-bottom: 60px;">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                
                <div class="card bg-dark text-white border-secondary p-4 shadow-lg">
                    <h2 class="mb-3 text-center">Contactanos</h2>
                    <p class="text-white-50 text-center mb-4">¿Tenés alguna duda o consulta sobre tu vehículo? Dejanos un mensaje y te responderemos pronto.</p>

                    <!-- Formulario que envía los datos al backend -->
                    <form action="php/guardar_mensaje_be.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nombre y apellido</label>
                            <input type="text" class="form-control bg-black text-white border-secondary" name="nombre" placeholder="Ej. Juan Pérez" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control bg-black text-white border-secondary" name="direccion" placeholder="Ej. Ruta Nacional 14 Km 12" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control bg-black text-white border-secondary" name="telefono" placeholder="Ej. 3775 12-3456" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control bg-black text-white border-secondary" name="email" placeholder="correo@ejemplo.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mensaje (máx. 500 caracteres)</label>
                            <textarea class="form-control bg-black text-white border-secondary" maxlength="500" name="mensaje" rows="4" placeholder="Escribí tu consulta acá..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 fw-bold py-2">Enviar mensaje</button>
                    </form>
                </div>

            </div>
        </div>
    </main>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>