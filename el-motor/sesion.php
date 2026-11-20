<?php 
session_start();

include("plantilla/cabecera.php");
?>

<body>
  <main class="registro-sesion">
    <div class="contenedor-todo">
        
      <div class="caja-trasera">

        <div class="caja-trasera-sesion">
          <h3>¿Ya tenés una cuenta?</h3>
          <p>Iniciá sesión para usar nuestros servicios.</p>
          <button id="btn-sesion">Iniciar sesión</button>
        </div>
        
        <div class="caja-trasera-registro">
          <h3>¿Todavía no tenés una cuenta?</h3>
          <p>Creá una para usar nuestros servicios.</p>
          <button id="btn-registro">Crear cuenta</button>
        </div>
      </div>
<!--Formulario de inicio de sesión y registro-->
      <div class="contenedor-sesion-registro">
<!--Inicio de sesión-->
        <form action="php/sesion_usuario_be.php" method="POST" class="formulario-sesion">
          <h2>Iniciar sesión</h2>
          <input type="text" placeholder="Correo electrónico" name="email">
          <input type="password" placeholder="Contraseña" name="contrasena">
          <button type="submit">Ingresar</button>
        </form>
<!--Registro-->
        <form action="php/registro_usuario_be.php" method="POST" class="formulario-registro">
          <h2>Crear cuenta</h2>
          <input type="text" placeholder="Nombre y apellido" name="nombre_completo">
          <input type="email" placeholder="Correo electrónico" name="email" required>
          <input type="text" placeholder="Nombre de usuario" name="usuario">
          <input type="password" placeholder="Contraseña" name="contrasena">
          <button type="submit">Registrarse</button>
        </form>

      </div>

    </div>
  </main>
  <script src="js/script.js"></script>
  <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>