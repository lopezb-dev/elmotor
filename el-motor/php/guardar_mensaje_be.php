<?php
include 'conexion_be.php';
/** @var mysqli $conexion*/

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. CAPTURA Y SANITIZACIÓN BÁSICA (Quitar espacios y proteger contra XSS básico)
    $nombre = trim($_POST['nombre'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');

    // Sanitizamos los campos de texto libre para el panel de administración
    $nombre_sanitizado = filter_var($nombre, FILTER_SANITIZE_SPECIAL_CHARS);
    $direccion_sanitizada = filter_var($direccion, FILTER_SANITIZE_SPECIAL_CHARS);
    $mensaje_sanitizado = filter_var($mensaje, FILTER_SANITIZE_SPECIAL_CHARS);


    // 2. BLOQUE DE VALIDACIONES

    // Validación: Nombre Obligatorio
    if (empty($nombre)){
        echo '<script>alert("ERROR: El nombre es obligatorio"); window.location = "../contacto.php";</script>';
        mysqli_close($conexion);
        exit();
    }

    // Validación: Formato del Nombre (Solo letras, tildes, eñes, espacios y guiones)
    $patron_nombre = "/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ'’\- ]+$/u";
    if (!preg_match($patron_nombre, $nombre)) {
        echo '<script>alert("ERROR: El nombre contiene caracteres inválidos."); window.location = "../contacto.php";</script>';
        mysqli_close($conexion);
        exit();
    }

    // Validación: Longitud del Nombre
    if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 25){
        echo '<script>alert("ERROR: El nombre debe tener entre 2 y 25 caracteres."); window.location = "../contacto.php";</script>';
        mysqli_close($conexion);
        exit();
    }

    // Validación: Teléfono Obligatorio
    if (empty($telefono)){
        echo '<script>alert("ERROR: El teléfono es obligatorio"); window.location = "../contacto.php";</script>';
        mysqli_close($conexion);
        exit();
    }

    // Validación: Formato del Teléfono
    if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $telefono)){
        echo '<script>alert("ERROR: Ingrese un número de teléfono válido."); window.location = "../contacto.php";</script>';
        mysqli_close($conexion);
        exit();
    }

    // NUEVA VALIDACIÓN: Formato de Email seguro (Utiliza el filtro nativo de PHP)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<script>alert("ERROR: El correo electrónico no tiene un formato válido."); window.location = "../contacto.php";</script>';
        mysqli_close($conexion);
        exit();
    }


    // 3. PERSISTENCIA (Solo se llega acá si pasaron todos los filtros anteriores)
    
    // Al pasar las variables sanitizadas, el panel de administración estará protegido contra ataques XSS
    $stmt = $conexion->prepare("INSERT INTO mensajes_contacto (nombre, direccion, telefono, email, mensaje) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nombre_sanitizado, $direccion_sanitizada, $telefono, $email, $mensaje_sanitizado);

    if ($stmt->execute()) {
        $stmt->close();
        mysqli_close($conexion);
        echo '
            <script>
                alert("¡Mensaje enviado con éxito! Nos pondremos en contacto pronto.");
                window.location = "../contacto.php";
            </script>
        ';
        exit();
    } else {
        $stmt->close();
        mysqli_close($conexion);
        echo '
            <script>
                alert("Hubo un error al enviar el mensaje. Inténtalo de nuevo.");
                window.location = "../contacto.php";
            </script>
        ';
        exit();
    }
}
?>
