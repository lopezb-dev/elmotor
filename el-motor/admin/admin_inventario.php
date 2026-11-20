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

    // Si se añadió un producto al inventario
    if (isset($_POST['accion']) && $_POST['accion'] == 'agregar_producto') {
        $producto = $_POST['producto'];
        $cantidad = $_POST['cantidad'];
        $descripcion = $_POST['descripcion'];
        $precio = $_POST['precio']; // Capturamos el precio del formulario

        // Añadimos 'precio' y una "i" extra (sisi) al bind_param
        $stmt_inv = $conexion->prepare("INSERT INTO inventario (producto, cantidad, descripcion, precio) VALUES (?, ?, ?, ?)");
        $stmt_inv->bind_param("sisi", $producto, $cantidad, $descripcion, $precio);
        $stmt_inv->execute();
        $stmt_inv->close();

        header("Location: admin_inventario.php");
        exit();
    }

    // Modificar cantidad de producto
    if (isset($_POST['accion']) && $_POST['accion'] == 'modificar_cantidad') {
        $producto_id = $_POST['producto_id'];
        $nueva_cantidad = $_POST['cantidad'];

        $stmt_mod = $conexion->prepare("UPDATE inventario SET cantidad = ? WHERE id = ?");
        $stmt_mod->bind_param("ii", $nueva_cantidad, $producto_id);
        $stmt_mod->execute();
        $stmt_mod->close();

        header("Location: admin_inventario.php");
        exit();
    }

    // NUEVA ACCIÓN: Modificar precio del producto
    if (isset($_POST['accion']) && $_POST['accion'] == 'modificar_precio') {
        $producto_id = $_POST['producto_id'];
        $nuevo_precio = $_POST['precio'];

        $stmt_prec = $conexion->prepare("UPDATE inventario SET precio = ? WHERE id = ?");
        $stmt_prec->bind_param("ii", $nuevo_precio, $producto_id);
        $stmt_prec->execute();
        $stmt_prec->close();

        header("Location: admin_inventario.php");
        exit();
    }

    // Eliminar producto del inventario
    if (isset($_POST['accion']) && $_POST['accion'] == 'eliminar_producto') {
        $producto_id = $_POST['producto_id'];

        $stmt_elim = $conexion->prepare("DELETE FROM inventario WHERE id = ?");
        $stmt_elim->bind_param("i", $producto_id);
        $stmt_elim->execute();
        $stmt_elim->close();

        header("Location: admin_inventario.php");
        exit();
    }
}

// GESTIÓN DE BÚSQUEDA Y FILTROS PARA INVENTARIO
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

// Agregamos el campo 'precio' a las consultas de selección (SELECT)
if ($busqueda_inv !== '') {
    $stmt_inv_list = $conexion->prepare("SELECT id, producto, cantidad, descripcion, precio FROM inventario WHERE producto LIKE ? OR descripcion LIKE ? ORDER BY $orden_sql");
    $param_busqueda_inv = "%" . $busqueda_inv . "%";
    $stmt_inv_list->bind_param("ss", $param_busqueda_inv, $param_busqueda_inv);
    $stmt_inv_list->execute();
    $resultado_inventario = $stmt_inv_list->get_result();
    $stmt_inv_list->close();
} else {
    $query_inventario = "SELECT id, producto, cantidad, descripcion, precio FROM inventario ORDER BY $orden_sql";
    $resultado_inventario = mysqli_query($conexion, $query_inventario);
}
?>
    
    <?php include 'cabecera.php'; ?>

    <main class="container" style="padding-top: 130px; padding-bottom: 50px;">
        <!-- Título principal -->
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
                    
                    <a href="admin_turnos.php" class="btn btn-outline-light fw-bold px-3">
                        📅 Gestión de turnos
                    </a>

                    <a href="admin_ordenes.php" class="btn btn-outline-light fw-bold px-3">
                        ⚙️ Ordenes de trabajo
                    </a>
                    
                    <a href="admin_mensajes.php" class="btn btn-outline-light fw-bold px-3">
                        ✉️ Mensajes recibidos
                    </a>
                        
                    <a href="admin_inventario.php" class="btn btn-light fw-bold px-3">
                        🛠️ Inventario y repuestos
                    </a>
                        
                    <a href="admin_usuarios.php" class="btn btn-outline-light fw-bold px-3">
                        👥 Usuarios del sistema
                    </a>
                </div>
            </div>
        </div>
    </div>

        <!-- Sección de Inventario -->
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="mb-3 text-warning">Inventario y repuestos</h3>
            </div>
            
            <!-- Columna Izquierda: Formulario de Alta con campo Precio -->
            <div class="col-md-4 mb-4">
                <div class="card bg-dark text-white border-secondary p-3">
                    <h5 class="card-title mb-3">Añadir nuevo producto</h5>
                    <form action="admin_inventario.php" method="POST">
                        <input type="hidden" name="accion" value="agregar_producto">
                        <div class="mb-3">
                            <label class="form-label">Nombre del producto / pieza</label>
                            <input type="text" class="form-control bg-black text-white border-secondary" name="producto" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cantidad en stock</label>
                            <input type="number" class="form-control bg-black text-white border-secondary" name="cantidad" min="0" value="1" required>
                        </div>
                        <!-- NUEVO CAMPO AGREGADO -->
                        <div class="mb-3">
                            <label class="form-label">Precio ($)</label>
                            <input type="number" class="form-control bg-black text-white border-secondary" name="precio" min="0" value="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción (opcional)</label>
                            <textarea class="form-control bg-black text-white border-secondary" name="descripcion" rows="2" placeholder="Tipo, marca, proveedor..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 fw-bold">Guardar en inventario</button>
                    </form>
                </div>
            </div>

            <!-- Columna Derecha: Buscador, Filtros y Tabla -->
            <div class="col-md-8">
                <form method="GET" action="admin_inventario.php" class="row g-2 mb-3">
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
                        <a href="admin_inventario.php" class="btn btn-sm btn-outline-secondary w-50">Limpiar</a>
                    </div>
                </form>

            <div class="table-responsive">
                <table class="table table-dark table-striped table-bordered border-secondary mb-0">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th style="width: 120px;">Stock</th>
                        <th style="width: 150px;">Precio ($)</th>
                        <th>Descripción</th>
                        <th style="width: 70px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado_inventario && $resultado_inventario->num_rows > 0): ?>
                        <?php while($inv = $resultado_inventario->fetch_assoc()): 
                            // Verificamos si el stock es menor a 5
                            $es_stock_bajo = ($inv['cantidad'] < 5);
                        ?>
                            <!-- Si el stock es bajo, aplicamos la clase table-danger a la fila -->
                            <tr class="<?php echo $es_stock_bajo ? 'table-danger text-dark' : ''; ?>">
                        
                                <td class="align-middle">
                                    <?php echo htmlspecialchars($inv['producto']); ?> 
                                    <!-- Si el stock es bajo, mostramos el badge -->
                                    <?php if ($es_stock_bajo): ?>
                                        <span class="badge bg-danger text-white ms-1">¡Stock bajo!</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Modificador de Stock inline -->
                                <td class="align-middle">
                                    <form action="admin_inventario.php" method="POST" class="d-flex gap-1 mb-0">
                                        <input type="hidden" name="accion" value="modificar_cantidad">
                                        <input type="hidden" name="producto_id" value="<?php echo $inv['id']; ?>">
                                        <input type="number" class="form-control form-control-sm bg-black text-white border-secondary text-center" name="cantidad" value="<?php echo $inv['cantidad']; ?>" min="0" required style="width: 55px;">
                                        <button type="submit" class="btn btn-sm btn-warning" title="Actualizar Stock">💾</button>
                                    </form>
                                </td>

                        <!-- Modificador de Precio inline -->
                                <td class="align-middle">
                                    <form action="admin_inventario.php" method="POST" class="d-flex gap-1 mb-0">
                                        <input type="hidden" name="accion" value="modificar_precio">
                                        <input type="hidden" name="producto_id" value="<?php echo $inv['id']; ?>">
                                        <input type="number" class="form-control form-control-sm bg-black text-white border-secondary text-center" name="precio" value="<?php echo $inv['precio']; ?>" min="0" required style="width: 75px;">
                                        <button type="submit" class="btn btn-sm btn-success" title="Actualizar Precio">💾</button>
                                    </form>
                                </td>

                                <td class="align-middle"><?php echo htmlspecialchars($inv['descripcion']); ?></td>
                        
                        <!-- Eliminar producto -->
                                <td class="align-middle text-center">
                                    <form action="admin_inventario.php" method="POST" class="mb-0" onsubmit="return confirm('¿Seguro que deseas eliminar este producto?');">
                                        <input type="hidden" name="accion" value="eliminar_producto">
                                        <input type="hidden" name="producto_id" value="<?php echo $inv['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-3">
                                        <?php if (isset($busqueda_inv) && $busqueda_inv !== ''): ?>
                                            <span class="text-warning">No se encontraron productos que coincidan con "<strong><?php echo htmlspecialchars($busqueda_inv); ?></strong>".</span>
                                        <?php else: ?>
                                            No hay productos cargados en el inventario.
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <hr class="border-secondary mb-5">
    </main>

    <script src="../js/bootstrap.bundle.min.js"></script>

<?php
mysqli_close($conexion);
?>