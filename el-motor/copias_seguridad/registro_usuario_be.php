<?php

    include 'conexion_be.php';
    /** @var mysqli $conexion*/
    
    $nombre_completo = $_POST['nombre_completo'];
    $email = $_POST['email'];
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];
    //Encriptacion
    $contrasena = hash('sha512', $contrasena);

    // Verificar correo válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '
            <script>
                alert("Por favor, use un correo electrónico válido.");
                window.location = "../sesion.php";
            </script>
        ';
        mysqli_close($conexion);
        exit();
    }

    // Verificar que el correo no se repita
    $verificar_email = mysqli_query($conexion, "SELECT * FROM usuarios WHERE email = '$email'");

    if(mysqli_num_rows($verificar_email) > 0) {
        echo '
            <script>
                alert("El correo ya está en uso, intente con uno distinto.");
                window.location = "../sesion.php";
            </script>
        ';
        mysqli_close($conexion);
        exit();
    }

    $verificar_usuario = mysqli_query($conexion, "SELECT * FROM usuarios WHERE usuario = '$usuario'");

    if(mysqli_num_rows($verificar_usuario) > 0) {
        echo '
            <script>
                alert("El nombre de usuario ya está en uso, intente con uno distinto.");
                window.location = "../sesion.php";
            </script>
        ';
        mysqli_close($conexion);
        exit();
    }

    // Si el correo está libre, intentar registrar al usuario
    $query = "INSERT INTO usuarios(nombre_completo, email, usuario, contrasena) VALUES('$nombre_completo', '$email', '$usuario', '$contrasena')";
    $ejecutar = mysqli_query($conexion, $query);

    // Evaluar si el registro en la base de datos fue exitoso
    if ($ejecutar) {
        session_start();
        $_SESSION['usuario'] = $email;
        mysqli_close($conexion);
        header("Location: ../index.php"); 
        exit();
    } else {
        echo '
            <script>
                alert("Registro fallido, inténtalo de nuevo");
                window.location = "../sesion.php";
            </script>
        ';
        mysqli_close($conexion);
        exit();
    }

?>
    
    
<!--    
    if ($ejecutar){
        echo '
            <script>
                alert("Usuario almacenado exitosamente");
                window.location = ../index.php;
            </script>
        ';
    }
?>