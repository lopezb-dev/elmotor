<?php
    include 'conexion_be.php';
    /** @var mysqli $conexion*/
    
    // 1. CAPTURA Y SANITIZACIÓN INICIAL
    $nombre_completo = trim($_POST['nombre_completo'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $usuario         = trim($_POST['usuario'] ?? '');
    $contrasena_raw  = $_POST['contrasena'] ?? ''; // Guardamos la clave limpia para procesarla al final

    // 2. VALIDACIONES DE CAMPOS VACÍOS Y LONGITUDES
    if (empty($nombre_completo)){
        echo '<script>alert("ERROR: El nombre es obligatorio"); window.location = "../sesion.php";</script>';
        mysqli_close($conexion);
        exit();
    }

    if (mb_strlen($nombre_completo) < 2 || mb_strlen($nombre_completo) > 50){
        echo '<script>alert("ERROR: El nombre debe tener entre 2 y 50 caracteres."); window.location = "../sesion.php";</script>';
        mysqli_close($conexion);
        exit();
    }

    if (empty($usuario)){
        echo '<script>alert("ERROR: El nombre de usuario es obligatorio"); window.location = "../sesion.php";</script>';
        mysqli_close($conexion);
        exit();
    }

    if (mb_strlen($usuario) < 2 || mb_strlen($usuario) > 50){
        echo '<script>alert("ERROR: El nombre de usuario debe tener entre 2 y 50 caracteres."); window.location = "../sesion.php";</script>';
        mysqli_close($conexion);
        exit();
    }

    if (empty($contrasena_raw)){
        echo '<script>alert("ERROR: La contraseña es obligatoria"); window.location = "../sesion.php";</script>';
        mysqli_close($conexion);
        exit();
    }

    // Verificar correo válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<script>alert("Por favor, use un correo electrónico válido."); window.location = "../sesion.php";</script>';
        mysqli_close($conexion);
        exit();
    }

    // 3. COMPROBACIONES DE DUPLICADOS EN LA BASE DE DATO

    // Verificar que el correo no se repita
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0) {
        echo '<script>alert("El correo ya está en uso, intente con uno distinto."); window.location = "../sesion.php";</script>';
        $stmt->close();
        mysqli_close($conexion);
        exit();
    }
    $stmt->close();

    // Verificar que el usuario no se repita
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0) {
        echo '<script>alert("El nombre de usuario ya está en uso, intente con uno distinto."); window.location = "../sesion.php";</script>';
        $stmt->close();
        mysqli_close($conexion);
        exit();
    }
    $stmt->close();


    // 4. PROCESAMIENTO SEGURO (Solo si todos los datos pasaron los filtros)
    
    // Encriptamos la contraseña justo antes de insertar
    $contrasena_encriptada = password_hash($contrasena_raw, PASSWORD_DEFAULT);

    // Intentar registrar al usuario
    $stmt = $conexion->prepare("INSERT INTO usuarios(nombre_completo, email, usuario, contrasena) VALUES(?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre_completo, $email, $usuario, $contrasena_encriptada);

    // Evaluar si el registro en la base de datos fue exitoso
    if ($stmt->execute()) {
        session_start();
        
        // ID auto-incremental que la base de datos le asignó a este registro
        $_SESSION['id'] = $conexion->insert_id; 
        $_SESSION['usuario'] = $email;
        $_SESSION['es_admin'] = 0; // Por defecto entra como usuario común
        
        $stmt->close();
        mysqli_close($conexion);
        header("Location: ../index.php"); 
        exit();
    } else {
        echo '<script>alert("Registro fallido, inténtalo de nuevo"); window.location = "../sesion.php";</script>';
        $stmt->close();
        mysqli_close($conexion);
        exit();
    }
?>
