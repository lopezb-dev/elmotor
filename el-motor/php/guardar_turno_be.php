<?php
session_start();
include 'conexion_be.php';
/** @var mysqli $conexion*/

if (!isset($_SESSION['usuario'])) {
    header("Location: ../sesion.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. VALIDACIONES DE ENTRADA INMEDIATAS (Evitamos procesar de más si falla el teléfono)
    $telefono = trim($_POST['telefono'] ?? '');

    if (empty($telefono)){
        echo '
            <script>
                alert("ERROR: El teléfono es obligatorio");
                window.location = "../turnos.php";
            </script>
        ';
        mysqli_close($conexion);
        exit();
    }

    if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $telefono)){
        echo '
            <script>
                alert("ERROR: Ingrese un número válido.");
                window.location = "../turnos.php";
            </script>
        ';
        mysqli_close($conexion);
        exit();
    }

    // 2. IDENTIFICACIÓN SEGURA DEL USUARIO
    $usuario_id = $_SESSION['id'] ?? null;

    if (!$usuario_id && isset($_SESSION['usuario'])) {
        $nombre_usu = $_SESSION['usuario'];
        
        // CORRECCIÓN: Reemplazamos la concatenación directa por una Sentencia Preparada segura
        $stmt_user = $conexion->prepare("SELECT id FROM usuarios WHERE usuario = ? OR email = ?");
        $stmt_user->bind_param("ss", $nombre_usu, $nombre_usu);
        $stmt_user->execute();
        $resultado_user = $stmt_user->get_result();
        
        if ($row_u = $resultado_user->fetch_assoc()) {
            $usuario_id = $row_u['id'];
        }
        $stmt_user->close();
    }

    // Si después de buscarlo sigue sin aparecer, cortamos la ejecución limpiamente
    if (!$usuario_id) {
        echo '<script>alert("ERROR: No se pudo identificar al usuario."); window.location = "../turnos.php";</script>';
        mysqli_close($conexion);
        exit();
    }

    // 3. CAPTURA Y SANITIZACIÓN DE CAMPOS PARA EL TURNO
    // Aplicamos sanitización para evitar XSS en el panel del administrador cuando revise la marca, patente o descripción
    $marca = filter_var(trim($_POST['marca'] ?? ''), FILTER_SANITIZE_SPECIAL_CHARS);
    $modelo = filter_var(trim($_POST['modelo'] ?? ''), FILTER_SANITIZE_SPECIAL_CHARS);
    $patente = filter_var(trim($_POST['patente'] ?? ''), FILTER_SANITIZE_SPECIAL_CHARS);
    $descripcion = filter_var(trim($_POST['descripcion'] ?? ''), FILTER_SANITIZE_SPECIAL_CHARS);
    $fecha = $_POST['fecha'] ?? '';
    $hora = $_POST['hora'] ?? '';
    
    $urgente = isset($_POST['urgente']) ? 1 : 0;
    $estado = 'pendiente'; // Por defecto llega como pendiente para que el admin lo acepte

    // 4. INSERCIÓN SEGURA DEL TURNO
    $stmt = $conexion->prepare("INSERT INTO turnos (usuario_id, fecha, hora, marca, modelo, patente, descripcion, estado, urgente, telefono) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssssis", $usuario_id, $fecha, $hora, $marca, $modelo, $patente, $descripcion, $estado, $urgente, $telefono);

    if ($stmt->execute()) {
        $stmt->close();
        mysqli_close($conexion);
        echo '<script>alert("¡Turno solicitado con éxito! El administrador lo revisará pronto."); window.location = "../turnos.php";</script>';
        exit();
    } else {
        $stmt->close();
        mysqli_close($conexion);
        echo '<script>alert("Hubo un error al solicitar el turno. Inténtalo de nuevo."); window.location = "../turnos.php";</script>';
        exit();
    }
}
?>
