<?php
session_start();

if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
    header("Location: index.php"); 
    exit();
}

include '../php/conexion_be.php';
/** @var mysqli $conexion*/

// GESTIÓN DE ELIMINACIÓN DE MENSAJES
if (isset($_GET['eliminar_id'])) {
    $id_a_eliminar = intval($_GET['eliminar_id']);
    
    $stmt_del = $conexion->prepare("DELETE FROM mensajes_contacto WHERE id = ?");
    $stmt_del->bind_param("i", $id_a_eliminar);
    
    if ($stmt_del->execute()) {
        $stmt_del->close();
        // Redirigimos para limpiar la URL y evitar reenvíos accidentales
        header("Location: admin_mensajes.php?eliminado=1");
        exit();
    } else {
        $stmt_del->close();
    }
}

// GESTIÓN DE BÚSQUEDA Y FILTROS PARA MENSAJES
$busqueda_contacto = isset($_GET['busqueda_contacto']) ? trim($_GET['busqueda_contacto']) : '';
$orden_contacto = isset($_GET['orden_contacto']) && $_GET['orden_contacto'] == 'ASC' ? 'ASC' : 'DESC';

// Consulta segura de mensajes con buscador
if ($busqueda_contacto !== '') {
    $stmt_msg = $conexion->prepare("SELECT * FROM mensajes_contacto WHERE nombre LIKE ? OR direccion LIKE ? OR telefono LIKE ? OR email LIKE ? OR mensaje LIKE ? ORDER BY fecha $orden_contacto");
    $param_busqueda = "%" . $busqueda_contacto . "%";
    $stmt_msg->bind_param("sssss", $param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda);
    $stmt_msg->execute();
    $resultado_mensajes = $stmt_msg->get_result();
    $stmt_msg->close();
} else {
    $query_mensajes = "SELECT * FROM mensajes_contacto ORDER BY fecha $orden_contacto";
    $resultado_mensajes = mysqli_query($conexion, $query_mensajes);
}

?>
    
    <?php include 'cabecera.php'; ?>

    <main class="container" style="padding-top: 130px; padding-bottom: 50px;">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="mb-2">Panel de Control - Taller Mecánico</h1>
                <p class="text-white-50">Bienvenido, administrador. Gestiona turnos, mensajes, inventario y usuarios.</p>
                <hr class="border-secondary">
            </div>
        </div>

        <?php if (isset($_GET['eliminado']) && $_GET['eliminado'] == 1): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Mensaje eliminado correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row mb-5">
            <div class="col-12">
                <div class="card bg-dark border-secondary p-3">
                    <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start align-items-center">

                        <a href="admin_turnos.php" class="btn btn-outline-light fw-bold px-3">
                            📅 Gestión de turnos
                        </a>
                        
                        <a href="admin_ordenes.php" class="btn btn-outline-light fw-bold px-3">
                            ⚙️ Ordenes de trabajo
                        </a>

                        <a href="admin_mensajes.php" class="btn btn-light fw-bold px-3">
                            ✉️ Mensajes recibidos
                        </a>
                        
                        <a href="admin_inventario.php" class="btn btn-outline-light fw-bold px-3">
                            🛠️ Inventario y repuestos
                        </a>
                        
                        <a href="admin_usuarios.php" class="btn btn-outline-light fw-bold px-3">
                            👥 Usuarios del sistema
                        </a>
                    </div>
                </div>
            </div>
        </div>


        <div class="row mb-5">
            <div class="col-12">
                <h3 class="mb-3 text-danger">Mensajes de contacto</h3>
                
                <!-- Formulario de Buscador y Filtros -->
                <form method="GET" action="admin_mensajes.php" class="row g-3 mb-4">
                    <div class="col-md-6">
                        <input type="text" class="form-control bg-black text-white border-secondary" name="busqueda_contacto" placeholder="Buscar por nombre, correo o contenido..." value="<?php echo htmlspecialchars($busqueda_contacto); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="orden_contacto" class="form-select bg-black text-white border-secondary">
                            <option value="DESC" <?php if($orden_contacto == 'DESC') echo 'selected'; ?>>Más recientes primero</option>
                            <option value="ASC" <?php if($orden_contacto == 'ASC') echo 'selected'; ?>>Más antiguos primero</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-light w-50">Filtrar</button>
                        <a href="admin_mensajes.php" class="btn btn-outline-secondary w-50">Limpiar</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-dark table-striped table-bordered border-secondary align-middle">
                        <thead>
                            <tr>
                                <th>Remitente</th>
                                <th>Dirección</th>
                                <th>Teléfono</th>
                                <th>Correo</th>
                                <th>Mensaje</th>
                                <th>Fecha</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($resultado_mensajes) > 0) {
                                while($msg = mysqli_fetch_assoc($resultado_mensajes)) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($msg['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($msg['direccion'])?></td>
                                        <td><?php echo htmlspecialchars($msg['telefono']); ?></td>
                                        <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                        <td><?php echo htmlspecialchars($msg['mensaje']); ?></td>
                                        <td><?php echo $msg['fecha']; ?></td>
                                        <td class="text-center">
                                            <a href="admin_mensajes.php?eliminar_id=<?php echo $msg['id']; ?>" 
                                               class="btn btn-danger btn-sm" 
                                               onclick="return confirm('¿Estás seguro de que deseas eliminar este mensaje?');">
                                                 Borrar
                                            </a>
                                        </td>
                                    </tr>
                                <?php } 
                            } else { ?>
                                    <tr><td colspan="7" class="text-center">No se encontraron mensajes de contacto.</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>