<?php
// 1. LA SESIÓN Y LA SEGURIDAD VAN PRIMERO QUE TODO
session_start();

if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
    header("Location: sesion.php"); 
    exit();
}

include 'php/conexion_be.php';
/** @var mysqli $conexion*/

// 2. PROCESAR ACCIONES
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Si se añadió un producto al inventario (usando 'producto' en lugar de 'nombre_producto')
    if (isset($_POST['accion']) && $_POST['accion'] == 'agregar_producto') {

        $producto = $_POST['producto'];
        $cantidad = $_POST['cantidad'];
        $descripcion = $_POST['descripcion'];

        $stmt_inv = $conexion->prepare("INSERT INTO inventario (producto, cantidad, descripcion) VALUES (?, ?, ?)");
        $stmt_inv->bind_param("sis", $producto, $cantidad, $descripcion);
        $stmt_inv->execute();
        $stmt_inv->close();
        
        header("Location: admin.php");
        exit();
    }

    // NUEVA ACCIÓN: Modificar cantidad de producto
    if (isset($_POST['accion']) && $_POST['accion'] == 'modificar_cantidad') {
        $producto_id = $_POST['producto_id'];
        $nueva_cantidad = $_POST['cantidad'];

        $stmt_mod = $conexion->prepare("UPDATE inventario SET cantidad = ? WHERE id = ?");
        $stmt_mod->bind_param("ii", $nueva_cantidad, $producto_id);
        $stmt_mod->execute();
        $stmt_mod->close();

        header("Location: admin.php");
        exit();
    }

    // NUEVA ACCIÓN: Eliminar producto del inventario
    if (isset($_POST['accion']) && $_POST['accion'] == 'eliminar_producto') {
        $producto_id = $_POST['producto_id'];

        $stmt_elim = $conexion->prepare("DELETE FROM inventario WHERE id = ?");
        $stmt_elim->bind_param("i", $producto_id);
        $stmt_elim->execute();
        $stmt_elim->close();

        header("Location: admin.php");
        exit();
    }

    // NUEVA ACCIÓN: Eliminar un usuario del sistema
    if (isset($_POST['accion']) && $_POST['accion'] == 'eliminar_usuario') {
        $usuario_email = $_POST['usuario_email'];

        // Usamos el email como identificador único para el borrado seguro
        $stmt_elim_user = $conexion->prepare("DELETE FROM usuarios WHERE email = ?");
        $stmt_elim_user->bind_param("s", $usuario_email);
        $stmt_elim_user->execute();
        $stmt_elim_user->close();

        header("Location: admin.php");
        exit();
    }

    // Si se aceptó un turno
    if (isset($_POST['accion']) && $_POST['accion'] == 'aceptar_turno') {
        $turno_id = $_POST['turno_id'];

        $stmt_turno = $conexion->prepare("UPDATE turnos SET estado = 'aceptado' WHERE id = ?");
        $stmt_turno->bind_param("i", $turno_id);
        $stmt_turno->execute();
        $stmt_turno->close();

        header("Location: admin.php");
        exit();
    } elseif (isset($_POST['accion']) && $_POST['accion'] == 'rechazar_turno') {
        $turno_id = $_POST['turno_id'];

        $stmt_rechazar = $conexion->prepare("DELETE FROM turnos WHERE id = ? AND estado = 'pendiente'");
        $stmt_rechazar->bind_param("i", $turno_id);
        $stmt_rechazar->execute();
        $stmt_rechazar->close();

        header("Location: admin.php");
        exit();
    }
}

// 3. GESTIÓN DE BÚSQUEDA Y FILTROS PARA MENSAJES
$busqueda_contacto = isset($_GET['busqueda_contacto']) ? trim($_GET['busqueda_contacto']) : '';
$orden_contacto = isset($_GET['orden_contacto']) && $_GET['orden_contacto'] == 'ASC' ? 'ASC' : 'DESC';

// Consulta segura de mensajes con buscador
if ($busqueda_contacto !== '') {
    $stmt_msg = $conexion->prepare("SELECT * FROM mensajes_contacto WHERE nombre LIKE ? OR email LIKE ? OR mensaje LIKE ? ORDER BY fecha $orden_contacto");
    $param_busqueda = "%" . $busqueda_contacto . "%";
    $stmt_msg->bind_param("sss", $param_busqueda, $param_busqueda, $param_busqueda);
    $stmt_msg->execute();
    $resultado_mensajes = $stmt_msg->get_result();
    $stmt_msg->close();
} else {
    $query_mensajes = "SELECT * FROM mensajes_contacto ORDER BY fecha $orden_contacto";
    $resultado_mensajes = mysqli_query($conexion, $query_mensajes);
}

// NUEVA GESTIÓN DE BÚSQUEDA Y FILTROS PARA USUARIOS
$busqueda_user = isset($_GET['busqueda_user']) ? trim($_GET['busqueda_user']) : '';
$orden_user = isset($_GET['orden_user']) && $_GET['orden_user'] == 'DESC' ? 'DESC' : 'ASC';

if ($busqueda_user !== '') {
    $stmt_user_list = $conexion->prepare("SELECT nombre_completo, usuario, email FROM usuarios WHERE nombre_completo LIKE ? OR usuario LIKE ? OR email LIKE ? ORDER BY nombre_completo $orden_user");
    $param_busqueda_user = "%" . $busqueda_user . "%";
    $stmt_user_list->bind_param("sss", $param_busqueda_user, $param_busqueda_user, $param_busqueda_user);
    $stmt_user_list->execute();
    $resultado_usuarios = $stmt_user_list->get_result();
    $stmt_user_list->close();
} else {
    $query_usuarios = "SELECT nombre_completo, usuario, email FROM usuarios ORDER BY nombre_completo $orden_user";
    $resultado_usuarios = mysqli_query($conexion, $query_usuarios);
}

// NUEVA GESTIÓN DE BÚSQUEDA Y FILTROS PARA INVENTARIO
$busqueda_inv = isset($_GET['busqueda_inv']) ? trim($_GET['busqueda_inv']) : '';
$orden_inv = isset($_GET['orden_inv']) ? $_GET['orden_inv'] : 'ALFA_ASC';

// Determinar la cláusula ORDER BY según el filtro seleccionado
switch ($orden_inv) {
    case 'ALFA_DESC': $orden_sql = "producto DESC"; break;
    case 'STOCK_DESC': $orden_sql = "cantidad DESC"; break;
    case 'STOCK_ASC': $orden_sql = "cantidad ASC"; break;
    case 'ALFA_ASC':
    default: $orden_sql = "producto ASC"; break;
}

if ($busqueda_inv !== '') {
    $stmt_inv_list = $conexion->prepare("SELECT id, producto, cantidad, descripcion FROM inventario WHERE producto LIKE ? OR descripcion LIKE ? ORDER BY $orden_sql");
    $param_busqueda_inv = "%" . $busqueda_inv . "%";
    $stmt_inv_list->bind_param("ss", $param_busqueda_inv, $param_busqueda_inv);
    $stmt_inv_list->execute();
    $resultado_inventario = $stmt_inv_list->get_result();
    $stmt_inv_list->close();
} else {
    $query_inventario = "SELECT id, producto, cantidad, descripcion FROM inventario ORDER BY $orden_sql";
    $resultado_inventario = mysqli_query($conexion, $query_inventario);
}

$query_proximos = "SELECT t.*, u.nombre_completo FROM turnos t JOIN usuarios u ON t.usuario_id = u.id WHERE t.fecha >= CURDATE() ORDER BY t.fecha ASC, t.hora ASC LIMIT 31";
$resultado_proximos = mysqli_query($conexion, $query_proximos);

$query_pendientes = "SELECT t.*, u.nombre_completo FROM turnos t JOIN usuarios u ON t.usuario_id = u.id WHERE t.estado = 'pendiente' ORDER BY t.fecha ASC";
$resultado_pendientes = mysqli_query($conexion, $query_pendientes);

$query_aceptados = "SELECT t.*, u.nombre_completo FROM turnos t JOIN usuarios u ON t.usuario_id = u.id WHERE t.estado = 'aceptado' ORDER BY t.fecha ASC";
$resultado_aceptados = mysqli_query($conexion, $query_aceptados);
?>

    <?php include 'admin/cabecera.php'; ?>

    <main class="container" style="padding-top: 130px; padding-bottom: 50px;">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="mb-2">Panel de Control - Taller Mecánico</h1>
                <p class="text-white-50">Bienvenido, administrador. Gestiona turnos, mensajes, inventario y usuarios.</p>
                <hr class="border-secondary">
            </div>
        </div>

        <!-- SECCIÓN 1: TURNOS -->
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="mb-3 text-info">1. Gestión de Turnos</h3>
            </div>

            <div class="col-md-12 mb-4">
                <div class="card bg-dark text-white border-secondary p-3">
                    <h5 class="card-title text-warning mb-3">🕒 Los 5 Turnos Más Próximos</h5>
                    <div class="table-responsive">
                        <table class="table table-dark table-striped table-bordered border-secondary mb-0">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($resultado_proximos) > 0) {
                                    while($p = mysqli_fetch_assoc($resultado_proximos)) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($p['nombre_completo']); ?></td>
                                            <td><?php echo $p['fecha']; ?></td>
                                            <td><?php echo $p['hora']; ?></td>
                                            <td><?php echo htmlspecialchars($p['descripcion']); ?></td>
                                            <td><span class="badge bg-<?php echo ($p['estado'] == 'aceptado') ? 'success' : 'warning'; ?>"><?php echo ucfirst($p['estado']); ?></span></td>
                                        </tr>
                                    <?php } 
                                } else { ?>
                                    <tr><td colspan="5" class="text-center">No hay turnos próximos programados.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card bg-dark text-white border-secondary p-3 h-100">
                    <h5 class="card-title text-danger mb-3">⏳ Turnos Pendientes por Aprobar</h5>
                    <div class="table-responsive">
                        <table class="table table-dark table-striped table-bordered border-secondary">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Fecha / Hora</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($resultado_pendientes) > 0) {
                                    while($pend = mysqli_fetch_assoc($resultado_pendientes)) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($pend['nombre_completo']); ?><br><small class="text-white-50"><?php echo $pend['descripcion']; ?></small></td>
                                            <td><?php echo $pend['fecha']; ?> <br> <?php echo $pend['hora']; ?></td>
                                            <td>
                                              <div class="d-flex gap-2">
                                                <!-- Botón Aceptar -->
                                                <form action="admin.php" method="POST" class="m-0">
                                                  <input type="hidden" name="accion" value="aceptar_turno">
                                                  <input type="hidden" name="turno_id" value="<?php echo $pend['id']; ?>">
                                                  <button type="submit" class="btn btn-sm btn-success fw-bold">Aceptar</button>
                                                </form>

                                                <!-- Botón Rechazar (Eliminar) -->
                                                <form action="admin.php" method="POST" class="m-0" onsubmit="return confirm('¿Estás seguro de que deseas rechazar y eliminar este turno?');">
                                                  <input type="hidden" name="accion" value="rechazar_turno">
                                                  <input type="hidden" name="turno_id" value="<?php echo $pend['id']; ?>">
                                                  <button type="submit" class="btn btn-sm btn-danger fw-bold">Rechazar</button>
                                                </form>
                                              </div>
                                            </td>
                                        </tr>
                                    <?php } 
                                } else { ?>
                                    <tr><td colspan="3" class="text-center">No hay turnos pendientes.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card bg-dark text-white border-secondary p-3 h-100">
                    <h5 class="card-title text-success mb-3">✅ Turnos Aceptados</h5>
                    <div class="table-responsive">
                        <table class="table table-dark table-striped table-bordered border-secondary">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Fecha / Hora</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($resultado_aceptados) > 0) {
                                    while($ac = mysqli_fetch_assoc($resultado_aceptados)) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ac['nombre_completo']); ?></td>
                                            <td><?php echo $ac['fecha']; ?> - <?php echo $ac['hora']; ?></td>
                                            <td><?php echo htmlspecialchars($ac['descripcion']); ?></td>
                                        </tr>
                                    <?php } 
                                } else { ?>
                                    <tr><td colspan="3" class="text-center">No hay turnos aceptados todavía.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <hr class="border-secondary mb-5">

        <!-- SECCIÓN 2: MENSAJES DE CONTACTO (Con Buscador y Filtro) -->
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="mb-3 text-danger">2. Mensajes de Contacto Recibidos</h3>
                
                <!-- Formulario de Buscador y Filtros -->
                <form method="GET" action="admin.php" class="row g-3 mb-4">
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
                        <a href="admin.php" class="btn btn-outline-secondary w-50">Limpiar</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-dark table-striped table-bordered border-secondary align-middle">
                        <thead>
                            <tr>
                                <th>Remitente</th>
                                <th>Correo</th>
                                <th>Mensaje</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($resultado_mensajes) > 0) {
                                while($msg = mysqli_fetch_assoc($resultado_mensajes)) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($msg['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                        <td><?php echo htmlspecialchars($msg['mensaje']); ?></td>
                                        <td><?php echo $msg['fecha']; ?></td>
                                    </tr>
                                <?php } 
                            } else { ?>
                                    <tr><td colspan="4" class="text-center">No se encontraron mensajes de contacto.</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <hr class="border-secondary mb-5">

        <!-- SECCIÓN 3: INVENTARIO Y REPUESTOS -->
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="mb-3 text-warning">3. Inventario y Repuestos</h3>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card bg-dark text-white border-secondary p-3">
                    <h5 class="card-title mb-3">Añadir Nuevo Producto</h5>
                    <form action="admin.php" method="POST">
                        <input type="hidden" name="accion" value="agregar_producto">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Producto / Pieza</label>
                            <input type="text" class="form-control bg-black text-white border-secondary" name="producto" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cantidad en Stock</label>
                            <input type="number" class="form-control bg-black text-white border-secondary" name="cantidad" min="0" value="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción (opcional)</label>
                            <textarea class="form-control bg-black text-white border-secondary" name="descripcion" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 fw-bold">Guardar en Inventario</button>
                    </form>
                </div>
            </div>

            <div class="col-md-8">
                <!-- Formulario de Buscador y Filtros para Inventario -->
                <form method="GET" action="admin.php" class="row g-2 mb-3">
                    <div class="col-md-5">
                        <input type="text" class="form-control form-control-sm bg-black text-white border-secondary" name="busqueda_inv" placeholder="Buscar producto o descripción..." value="<?php echo htmlspecialchars($busqueda_inv); ?>">
                    </div>
                    <div class="col-md-4">
                        <select name="orden_inv" class="form-select form-select-sm bg-black text-white border-secondary">
                            <option value="ALFA_ASC" <?php if($orden_inv == 'ALFA_ASC') echo 'selected'; ?>>Alfabetico (A-Z)</option>
                            <option value="ALFA_DESC" <?php if($orden_inv == 'ALFA_DESC') echo 'selected'; ?>>Alfabetico (Z-A)</option>
                            <option value="STOCK_DESC" <?php if($orden_inv == 'STOCK_DESC') echo 'selected'; ?>>Stock (Mayor a Menor)</option>
                            <option value="STOCK_ASC" <?php if($orden_inv == 'STOCK_ASC') echo 'selected'; ?>>Stock (Menor a Mayor)</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-outline-light w-50">Filtrar</button>
                        <a href="admin.php" class="btn btn-sm btn-outline-secondary w-50">Limpiar</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-dark table-striped table-bordered border-secondary align-middle">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th style="width: 180px;">Stock</th>
                                <th>Descripción</th>
                                <th style="width: 100px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($resultado_inventario) > 0) {
                                while($inv = mysqli_fetch_assoc($resultado_inventario)) { 
                                    // Definimos si el stock es crítico (menos de 3 unidades)
                                    $es_critico = ($inv['cantidad'] < 3);
                                    // Si es crítico, aplicamos una clase de alerta a la fila, si no, queda normal
                                    $clase_fila = $es_critico ? 'table-danger text-dark fw-semibold' : '';
                                ?>
                                    <tr class="<?php echo $clase_fila; ?>">
                                        <td>
                                            <?php echo htmlspecialchars($inv['producto']); ?>
                                            <?php if ($es_critico) { ?>
                                                <span class="badge bg-danger ms-1 text-white animate-pulse">¡Stock Bajo!</span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <!-- Formulario integrado en línea para modificar stock -->
                                            <form action="admin.php" method="POST" class="d-flex align-items-center gap-1 m-0">
                                                <input type="hidden" name="accion" value="modificar_cantidad">
                                                <input type="hidden" name="producto_id" value="<?php echo $inv['id']; ?>">
                                                <!-- Cambiamos las clases del input dinámicamente si es crítico para que se lea bien el texto -->
                                                <input type="number" name="cantidad" class="form-control form-control-sm <?php echo $es_critico ? 'bg-white text-black border-danger' : 'bg-black text-white border-secondary'; ?> text-center fw-bold" value="<?php echo $inv['cantidad']; ?>" min="0" style="width: 70px;" required>
                                                <button type="submit" class="btn btn-sm <?php echo $es_critico ? 'btn-danger' : 'btn-primary'; ?>" title="Actualizar Stock">✓</button>
                                            </form>
                                        </td>
                                        <td><?php echo htmlspecialchars($inv['descripcion']); ?></td>
                                        <td class="text-center">
                                            <!-- Botón para eliminar el producto -->
                                            <form action="admin.php" method="POST" class="m-0" onsubmit="return confirm('¿Eliminar <?php echo htmlspecialchars($inv['producto']); ?> del inventario?');">
                                                <input type="hidden" name="accion" value="eliminar_producto">
                                                <input type="hidden" name="producto_id" value="<?php echo $inv['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger fw-bold">Borrar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } 
                            } else { ?>
                                    <tr><td colspan="4" class="text-center">No hay productos registrados en el inventario.</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <hr class="border-secondary mb-5">

        <!-- SECCIÓN 4: USUARIOS REGISTRADOS -->
        <div class="row">
            <div class="col-12">
                <h3 class="mb-3 text-success">4. Usuarios Registrados en el Sistema</h3>
                
                <!-- Formulario de Buscador y Filtros para Usuarios -->
                <form method="GET" action="admin.php" class="row g-3 mb-4">
                    <div class="col-md-6">
                        <input type="text" class="form-control bg-black text-white border-secondary" name="busqueda_user" placeholder="Buscar por nombre, usuario o correo..." value="<?php echo htmlspecialchars($busqueda_user); ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="orden_user" class="form-select bg-black text-white border-secondary">
                            <option value="ASC" <?php if($orden_user == 'ASC') echo 'selected'; ?>>Nombre (A-Z)</option>
                            <option value="DESC" <?php if($orden_user == 'DESC') echo 'selected'; ?>>Nombre (Z-A)</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-light w-50">Filtrar</button>
                        <a href="admin.php" class="btn btn-outline-secondary w-50">Limpiar</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-dark table-striped table-bordered border-secondary align-middle">
                        <thead>
                            <tr>
                                <th>Nombre Completo</th>
                                <th>Nombre de Usuario</th>
                                <th>Correo Electrónico</th>
                                <th style="width: 100px;" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($resultado_usuarios) > 0) {
                                while($row = mysqli_fetch_assoc($resultado_usuarios)) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['nombre_completo']); ?></td>
                                        <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td class="text-center">
                                            <!-- Botón para eliminar el usuario -->
                                            <form action="admin.php" method="POST" class="m-0" onsubmit="return confirm('¿Estás seguro de que deseas eliminar al usuario <?php echo htmlspecialchars($row['usuario']); ?>? Esta acción no se puede deshacer.');">
                                                <input type="hidden" name="accion" value="eliminar_usuario">
                                                <input type="hidden" name="usuario_email" value="<?php echo $row['email']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger fw-bold">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } 
                            } else { ?>
                                    <tr><td colspan="4" class="text-center">No hay usuarios registrados todavía o no coinciden con la búsqueda.</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="estilos/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
mysqli_close($conexion);
?>