<?php

    session_start();

    include 'conexion_be.php';
    /** @var mysqli $conexion*/

    $email = $_POST['email'];
    $contrasena = $_POST['contrasena'];
    //Encriptacion
    $contrasena = hash('sha512', $contrasena);

    $validar_sesion = mysqli_query($conexion, "SELECT * FROM usuarios WHERE email = '$email' AND contrasena = '$contrasena'");

    if(mysqli_num_rows($validar_sesion) > 0){
        $_SESSION['usuario'] = $email;
        header("location: ../index.php");
        exit();
    } else {
        echo '
            <script>
                alert("El usuario no existe, verifique los datos introducidos o intente registrarse.");
                window.location = "../sesion.php";
            </script>
        ';
        exit();
    }
?>