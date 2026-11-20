<?php
    session_start();
    include 'conexion_be.php';
    /** @var mysqli $conexion*/

    $email = $_POST['email'];
    $contrasena = $_POST['contrasena'];

    // 1. Modificamos la consulta para traer la contraseña encriptada de la BD usando solo el email
    $stmt = $conexion->prepare("SELECT id, nombre_completo, email, contrasena, es_admin FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if($resultado->num_rows > 0){
        $usuario = $resultado->fetch_assoc();
        
        // 2. Usamos password_verify para comparar la clave ingresada con el hash seguro de la BD
        if (password_verify($contrasena, $usuario['contrasena'])) {
            
            // CORRECCIÓN: Guardamos el ID único del usuario en la sesión para que esté disponible globalmente
            $_SESSION['id'] = $usuario['id'];
            $_SESSION['usuario'] = $email;
            $_SESSION['es_admin'] = $usuario['es_admin'];
            
            $stmt->close();
            mysqli_close($conexion);

            if ($usuario['es_admin'] == 1) {
                header("location: ../admin/admin_turnos.php");
            } else {
                header("location: ../index.php");
            }
            exit();
        }
    }

    // 3. Si el usuario no existe O la contraseña falla, cae aquí (Mismo mensaje por seguridad)
    if (isset($stmt)) { $stmt->close(); }
    mysqli_close($conexion);
    echo '
        <script>
            alert("El usuario no existe o los datos son incorrectos.");
            window.location = "../sesion.php";
        </script>
    ';
    exit();
?>
