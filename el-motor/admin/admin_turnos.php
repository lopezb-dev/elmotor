<?php
session_start();

// 1. CONTROL DE ACCESO (Seguridad de Administrador)
if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
    header("Location: index.php"); 
    exit();
}

include '../php/conexion_be.php';
/** @var mysqli $conexion*/
    
// PROCESAR ACCIONES
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Si se aceptó un turno
    if (isset($_POST['accion']) && $_POST['accion'] == 'aceptar_turno') {
        $turno_id = $_POST['turno_id'];

        $stmt_turno = $conexion->prepare("UPDATE turnos SET estado = 'aceptado' WHERE id = ?");
        $stmt_turno->bind_param("i", $turno_id);
        $stmt_turno->execute();
        $stmt_turno->close();

        header("Location: admin_turnos.php");
        exit();
    } elseif (isset($_POST['accion']) && $_POST['accion'] == 'rechazar_turno') {
        $turno_id = $_POST['turno_id'];

        $stmt_rechazar = $conexion->prepare("DELETE FROM turnos WHERE id = ? AND estado = 'pendiente'");
        $stmt_rechazar->bind_param("i", $turno_id);
        $stmt_rechazar->execute();
        $stmt_rechazar->close();

        header("Location: admin_turnos.php");
        exit();
    }
    
    // NUEVA ACCIÓN: Eliminar un turno que ya fue aceptado anteriormente
    elseif (isset($_POST['accion']) && $_POST['accion'] == 'eliminar_turno_aceptado') {
        $turno_id = $_POST['turno_id'];

        $stmt_eliminar = $conexion->prepare("DELETE FROM turnos WHERE id = ? AND estado = 'aceptado'");
        $stmt_eliminar->bind_param("i", $turno_id);
        $stmt_eliminar->execute();
        $stmt_eliminar->close();

        header("Location: admin_turnos.php");
        exit();
    }

    // NUEVA ACCIÓN: Modificar fecha y hora de un turno aceptado
    elseif (isset($_POST['accion']) && $_POST['accion'] == 'modificar_fecha_hora') {
        $turno_id = $_POST['turno_id'];
        $nueva_fecha = $_POST['nueva_fecha'];
        $nueva_hora = $_POST['nueva_hora'];

        $stmt_cambio = $conexion->prepare("UPDATE turnos SET fecha = ?, hora = ? WHERE id = ? AND estado = 'aceptado'");
        $stmt_cambio->bind_param("ssi", $nueva_fecha, $nueva_hora, $turno_id);
        $stmt_cambio->execute();
        $stmt_cambio->close();

        header("Location: admin_turnos.php");
        exit();
    }
}

$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$orden = isset($_GET['orden']) && $_GET['orden'] == 'antiguo' ? 'ASC' : 'DESC';

$query_aceptados = "SELECT t.*, u.nombre_completo FROM turnos t JOIN usuarios u ON t.usuario_id = u.id WHERE t.estado = 'aceptado'";

if ($buscar !== '') {
    $query_aceptados .= " AND (u.nombre_completo LIKE ? OR t.fecha LIKE ?)";
}

$query_aceptados .= " ORDER BY t.fecha $orden, t.hora $orden";

$stmt_aceptados = $conexion->prepare($query_aceptados);

if ($buscar !== '') {
    $termino_busqueda = "%" . $buscar . "%";
    $stmt_aceptados->bind_param("ss", $termino_busqueda, $termino_busqueda);
}

$stmt_aceptados->execute();
$resultado_aceptados = $stmt_aceptados->get_result(); 

// SE MANTIENE "t.id" PARA LAS ACCIONES DE EDICIÓN Y BORRADO
$query_proximos = "SELECT t.id, t.*, u.nombre_completo, t.telefono, t.urgente FROM turnos t JOIN usuarios u ON t.usuario_id = u.id WHERE t.fecha >= CURDATE() ORDER BY t.fecha ASC, t.hora ASC LIMIT 31";
$resultado_proximos = mysqli_query($conexion, $query_proximos);

$query_pendientes = "SELECT t.*, u.nombre_completo FROM turnos t JOIN usuarios u ON t.usuario_id = u.id WHERE t.estado = 'pendiente' ORDER BY t.fecha ASC";
$resultado_pendientes = mysqli_query($conexion, $query_pendientes);

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

        <div class="row mb-5">
            <div class="col-12">
                <div class="card bg-dark border-secondary p-3">
                    <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start align-items-center">

                        <a href="admin_turnos.php" class="btn btn-light fw-bold px-3">
                            📅 Gestión de turnos
                        </a>

                        <a href="admin_ordenes.php" class="btn btn-outline-light fw-bold px-3">
                            ⚙️ Ordenes de trabajo
                        </a>
                        
                        <a href="admin_mensajes.php" class="btn btn-outline-light fw-bold px-3">
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
                <h3 class="mb-3">Gestión de turnos</h3>
            </div>

            <div class="col-md-12 mb-4">
                <div class="card bg-dark text-white border-secondary p-3">
                    <h5 class="card-title text-warning mb-3">🕒 Turnos próximos</h5>
                    <div class="table-responsive">
                        <table class="table table-dark table-striped table-bordered border-secondary mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Teléfono</th>
                                    <th style="min-width: 150px;">Fecha</th>
                                    <th style="min-width: 130px;">Hora</th>
                                    <th>Vehículo</th>
                                    <th>Descripción</th>
                                    <th>¿Urgente?</th>
                                    <th>Estado</th>
                                    <th style="width: 100px;" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($resultado_proximos) > 0) {
                                    while($p = mysqli_fetch_assoc($resultado_proximos)) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($p['nombre_completo']); ?></td>
                                            <td><?php echo htmlspecialchars($p['telefono'] ?? 'No cargado'); ?></td>
                                            
                                            <?php if ($p['estado'] == 'aceptado'): ?>
                                                <!-- INTERFAZ DE MODIFICACIÓN EN LÍNEA (FECHA Y HORA COMBINADAS EN UN FORMULARIO) -->
                                                <td colspan="2">
                                                    <form action="admin_turnos.php" method="POST" class="d-flex gap-1 mb-0 align-items-center">
                                                        <input type="hidden" name="accion" value="modificar_fecha_hora">
                                                        <input type="hidden" name="turno_id" value="<?php echo $p['id']; ?>">
                                                        
                                                        <input type="date" class="form-control form-control-sm bg-black text-white border-secondary text-center" name="nueva_fecha" value="<?php echo $p['fecha']; ?>" required style="max-width: 135px;">
                                                        <input type="time" class="form-control form-control-sm bg-black text-white border-secondary text-center" name="nueva_hora" value="<?php echo date('H:i', strtotime($p['hora'])); ?>" required style="max-width: 90px;">
                                                        
                                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Guardar cambios de fecha y hora">💾</button>
                                                    </form>
                                                </td>
                                            <?php else: ?>
                                                <!-- Vista normal para turnos que no están aceptados -->
                                                <td><?php echo $p['fecha']; ?></td>
                                                <td><?php echo $p['hora']; ?></td>
                                            <?php endif; ?>

                                            <td><?php echo $p['marca']; ?>, <?php echo $p['modelo']; ?>, <?php echo $p['patente']; ?>.</td>
                                            <td><?php echo htmlspecialchars($p['descripcion']); ?></td>
                                            <td>
                                                <?php if ($p['urgente'] == 1): ?>
                                                    <span class="badge bg-danger">Sí</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">No</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge bg-<?php echo ($p['estado'] == 'aceptado') ? 'success' : 'warning'; ?>"><?php echo ucfirst($p['estado']); ?></span></td>
                                            
                                            <!-- NUEVA COLUMNA DE ACCIONES -->
                                            <td class="text-center">
                                                <?php if ($p['estado'] == 'aceptado'): ?>
                                                    <form action="admin_turnos.php" method="POST" class="mb-0" onsubmit="return confirm('¿Seguro que deseas eliminar/cancelar este turno aceptado? Esta acción no se puede deshacer.');">
                                                        <input type="hidden" name="accion" value="eliminar_turno_aceptado">
                                                        <input type="hidden" name="turno_id" value="<?php echo $p['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php } 
                                } else { ?>
                                    <!-- Se cambió el colspan a 9 debido a la nueva columna añadida -->
                                    <tr><td colspan="9" class="text-center">No hay turnos próximos programados.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            <!-- Turnos pendientes ocupa todo el ancho disponible -->
            <div class="col-md-12 mb-4">
                <div class="card bg-dark text-white border-secondary p-3">
                    <h5 class="card-title text-danger mb-3">⏳ Turnos pendientes</h5>
                    <div class="table-responsive">
                        <table class="table table-dark table-striped table-bordered border-secondary">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Vehículo</th>
                                    <th>Fecha / Hora</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($resultado_pendientes) > 0) {
                                    while($pend = mysqli_fetch_assoc($resultado_pendientes)) { ?>
                                        <tr>
                                            <td>
                                                <?php echo htmlspecialchars($pend['nombre_completo']); ?><br>
                                                <small class="text-white-50">
                                                    <?php echo $pend['descripcion']; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-white-50">
                                                    <?php echo $pend['marca']; ?>, <?php echo $pend['modelo']; ?>, <?php echo $pend['patente']; ?>.
                                                </small>
                                            </td>
                                            <td>
                                                <?php echo $pend['fecha']; ?><br> 
                                                <?php echo $pend['hora']; ?>
                                            </td>
                                            <td>
                                              <div class="d-flex gap-2">
                                                <!-- Botón Aceptar -->
                                                <form action="admin_turnos.php" method="POST" class="m-0" onsubmit="return confirm('¿Estás seguro de que deseas aceptar este turno?');">
                                                  <input type="hidden" name="accion" value="aceptar_turno">
                                                  <input type="hidden" name="turno_id" value="<?php echo $pend['id']; ?>">
                                                  <button type="submit" class="btn btn-sm btn-success fw-bold">Aceptar</button>
                                                </form>

                                                <!-- Botón Rechazar (Eliminar) -->
                                                <form action="admin_turnos.php" method="POST" class="m-0" onsubmit="return confirm('¿Estás seguro de que deseas rechazar y eliminar este turno?');">
                                                  <input type="hidden" name="accion" value="rechazar_turno">
                                                  <input type="hidden" name="turno_id" value="<?php echo $pend['id']; ?>">
                                                  <button type="submit" class="btn btn-sm btn-danger fw-bold">Rechazar</button>
                                                </form>
                                              </div>
                                            </td>
                                        </tr>
                                    <?php } 
                                } else { ?>
                                    <tr><td colspan="4" class="text-center">No hay turnos pendientes.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Historial de turnos ahora aparece abajo ocupando todo el ancho -->
            <div class="col-md-12 mb-4">
                <div class="card bg-dark text-white border-secondary p-3">
                    
                    <!-- Encabezado y Formulario de Filtros -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-3">
                        <h5 class="card-title text-success m-0">📋 Historial de turnos</h5>
                        
                        <form action="admin_turnos.php" method="GET" class="d-flex flex-wrap gap-2 align-items-center m-0">
                            <!-- Input para buscar nombre o día -->
                            <div class="input-group input-group-sm" style="max-width: 250px;">
                                <input type="text" name="buscar" class="form-control bg-dark text-white border-secondary" placeholder="Buscar por nombre o fecha..." value="<?php echo htmlspecialchars($buscar); ?>">
                                <?php if ($buscar !== ''): ?>
                                    <a href="admin_turnos.php" class="btn btn-outline-secondary" title="Limpiar búsqueda">✕</a>
                                <?php endif; ?>
                            </div>

                            <!-- Selector de Orden -->
                            <select name="orden" class="form-select form-select-sm bg-dark text-white border-secondary" style="max-width: 160px;" onchange="this.form.submit()">
                                <option value="reciente" <?php echo ($orden == 'DESC') ? 'selected' : ''; ?>>Más recientes</option>
                                <option value="antiguo" <?php echo ($orden == 'ASC') ? 'selected' : ''; ?>>Más antiguos</option>
                            </select>

                            <button type="submit" class="btn btn-sm btn-success fw-bold">Buscar</button>
                        </form>
                    </div>

                    <!-- Tabla de Resultados -->
                    <div class="table-responsive">
                        <table class="table table-dark table-striped table-bordered border-secondary mb-0">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Vehículo</th>
                                    <th>Fecha / Hora</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($resultado_aceptados && $resultado_aceptados->num_rows > 0) {
                                    while($ac = $resultado_aceptados->fetch_assoc()) { ?>
                                        <tr>
                                            <td>
                                                <?php echo htmlspecialchars($ac['nombre_completo']); ?>
                                            </td>
                                            <td>
                                                <?php echo $ac['marca']; ?>, <?php echo $ac['modelo']; ?>, <?php echo $ac['patente']; ?>.
                                            </td>
                                            <td>
                                                <?php echo $ac['fecha']; ?> - <?php echo $ac['hora']; ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($ac['descripcion']); ?>
                                            </td>
                                        </tr>
                                    <?php } 
                                } else { ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-3">
                                            <?php if ($buscar !== ''): ?>
                                                <span class="text-warning">No se encontraron turnos que coincidan con "<strong><?php echo htmlspecialchars($buscar); ?></strong>".</span>
                                            <?php else: ?>
                                                No hay registros en el historial todavía.
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <hr class="border-secondary mb-5">
        <script src="../js/bootstrap.bundle.min.js"></script>