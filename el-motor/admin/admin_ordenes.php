<?php
session_start();

// 1. CONTROL DE ACCESO (Seguridad de Administrador)
if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
    header("Location: index.php"); 
    exit();
}

include '../php/conexion_be.php';
/** @var mysqli $conexion*/

$error_mensaje = '';

// 2. PROCESAR ACCIONES POST (Guardado y Actualizaciones)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // ACCIÓN A: Crear una nueva orden de trabajo
    if (isset($_POST['accion']) && $_POST['accion'] == 'crear_orden') {
        
        // Captura de datos generales del formulario
        $fecha = $_POST['fecha'];
        $cliente = trim($_POST['cliente']);
        $marca = trim($_POST['vehiculo_marca']);
        $modelo = trim($_POST['vehiculo_modelo']);
        $patente = trim($_POST['vehiculo_patente']);
        $diagnostico = trim($_POST['diagnostico']);

        // 🔒 TRANSACCIÓN: Si algo falla a mitad de camino, MySQL deshace todo para no dejar datos huérfanos
        $conexion->begin_transaction();

        try {
            // PASO A: Insertar los datos generales en 'ordenes_trabajo'
            $stmt_orden = $conexion->prepare("INSERT INTO ordenes_trabajo (fecha, cliente, vehiculo_marca, vehiculo_modelo, vehiculo_patente, diagnostico) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_orden->bind_param("ssssss", $fecha, $cliente, $marca, $modelo, $patente, $diagnostico);
            $stmt_orden->execute();
            
            // 🔑 LA CLAVE DEL ÉXITO: Obtenemos el ID que MySQL le acaba de dar a esta orden nueva (ej: 1, 2, 3...)
            $orden_id = $conexion->insert_id; 
            $stmt_orden->close();

            // PASO B: Insertar la lista de Trabajos (Mano de obra)
            if (isset($_POST['trabajos']) && is_array($_POST['trabajos'])) {
                $stmt_trabajo = $conexion->prepare("INSERT INTO orden_detalles_trabajos (orden_id, descripcion_trabajo, precio_mano_obra) VALUES (?, ?, ?)");
                
                foreach ($_POST['trabajos'] as $trabajo) {
                    if (!empty($trabajo['descripcion'])) {
                        $desc_t = trim($trabajo['descripcion']);
                        $precio_t = intval($trabajo['precio']);
                        
                        // Vinculamos el trabajo al $orden_id que conseguimos arriba
                        $stmt_trabajo->bind_param("isi", $orden_id, $desc_t, $precio_t);
                        $stmt_trabajo->execute();
                    }
                }
                $stmt_trabajo->close();
            }

            // PASO C: Insertar los Repuestos utilizados y descontar stock automáticamente
            if (isset($_POST['repuestos']) && is_array($_POST['repuestos'])) {
                $stmt_repuesto = $conexion->prepare("INSERT INTO orden_detalles_repuestos (orden_id, inventario_id, cantidad_usada, precio_unitario) VALUES (?, ?, ?, ?)");
                $stmt_stock = $conexion->prepare("UPDATE inventario SET cantidad = cantidad - ? WHERE id = ?");

                foreach ($_POST['repuestos'] as $repuesto) {
                    if (!empty($repuesto['id'])) {
                        $repuesto_id = intval($repuesto['id']);
                        $cant_usada = intval($repuesto['cantidad']);
                        $precio_u = intval($repuesto['precio']);

                        // Insertamos el detalle del repuesto en la orden
                        $stmt_repuesto->bind_param("iiii", $orden_id, $repuesto_id, $cant_usada, $precio_u);
                        $stmt_repuesto->execute();

                        // Descontamos la cantidad del stock real de la tabla inventario
                        $stmt_stock->bind_param("ii", $cant_usada, $repuesto_id);
                        $stmt_stock->execute();
                    }
                }
                $stmt_repuesto->close();
                $stmt_stock->close();
            }

            // Si todos los pasos salieron bien, impactamos los cambios en la BD definitivamente
            $conexion->commit();
            header("Location: admin_ordenes.php?guardado=1");
            exit();

        } catch (Exception $e) {
            // Si hubo algún error (ej: te quedaste sin internet a la mitad), se cancela todo
            $conexion->rollback();
            $error_mensaje = "Error en el sistema: " . $e->getMessage();
        }
    }

    // ACCIÓN B: Actualizar el estado de una orden existente (Nueva lógica añadida)
    if (isset($_POST['accion']) && $_POST['accion'] == 'actualizar_estado') {
        $orden_id = intval($_POST['orden_id']);
        $nuevo_estado = $_POST['estado'];

        // Validamos que el estado enviado pertenezca al ENUM de la base de datos
        if (in_array($nuevo_estado, ['pendiente', 'en_progreso', 'completado'])) {
            $stmt_est = $conexion->prepare("UPDATE ordenes_trabajo SET estado = ? WHERE id = ?");
            $stmt_est->bind_param("si", $nuevo_estado, $orden_id);
            $stmt_est->execute();
            $stmt_est->close();
            
            header("Location: admin_ordenes.php?actualizado=1");
            exit();
        }
    }
}

// 3. CONSULTAS PARA ALIMENTAR LA PÁGINA TRAS BAMBALINAS

// Consulta 1: Buscamos repuestos que tengan stock > 0 para listarlos en el formulario dinámico
$query_items = "SELECT id, producto, precio, cantidad FROM inventario WHERE cantidad > 0 ORDER BY producto ASC";
$resultado_repuestos_disponibles = mysqli_query($conexion, $query_items);

// Consulta 2: Buscamos el historial de órdenes calculando su costo TOTAL combinado mediante subconsultas SQL
$query_lista_ordenes = "
    SELECT o.*, 
           (IFNULL((SELECT SUM(precio_mano_obra) FROM orden_detalles_trabajos WHERE orden_id = o.id), 0) + 
            IFNULL((SELECT SUM(cantidad_usada * precio_unitario) FROM orden_detalles_repuestos WHERE orden_id = o.id), 0)) AS total_orden
    FROM ordenes_trabajo o 
    ORDER BY o.fecha DESC, o.id DESC";
$resultado_ordenes = mysqli_query($conexion, $query_lista_ordenes);
?>

<?php include 'cabecera.php'; ?>

<main class="container" style="padding-top: 130px; padding-bottom: 50px;">
    <!-- Encabezado de la página -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-2">Panel de Control - Taller Mecánico</h1>
            <p class="text-white-50">Bienvenido, administrador. Gestiona turnos, mensajes, inventario y usuarios.</p>
            <hr class="border-secondary">
            
            <?php if(isset($_GET['guardado'])): ?>
                <div class="alert alert-success bg-dark text-success border-success fw-bold">
                    ✅ ¡Orden de trabajo registrada y stock actualizado con éxito!
                </div>
            <?php endif; ?>
            
            <?php if(!empty($error_mensaje)): ?>
                <div class="alert alert-danger bg-dark text-danger border-danger fw-bold">
                    ❌ <?php echo htmlspecialchars($error_mensaje); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-12">
            <div class="card bg-dark border-secondary p-3">
                <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start align-items-center">
                    
                    <a href="admin_turnos.php" class="btn btn-outline-light fw-bold px-3">
                        📅 Gestión de turnos
                    </a>

                    <a href="admin_ordenes.php" class="btn btn-light fw-bold px-3">
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

    <!-- Formulario de Alta Completo -->
    <h3 class="text-warning mb-4">Ordenes de trabajo</h3>
    <div class="row">
        <div class="col-12 mb-5">
            <div class="card bg-dark text-white border-secondary p-4">
                <form action="admin_ordenes.php" method="POST">
                    <input type="hidden" name="accion" value="crear_orden">
                    
                    <!-- Fila 1: Fecha y Cliente -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Fecha de entrada</label>
                            <input type="date" class="form-control bg-black text-white border-secondary" name="fecha" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">Cliente / Propietario</label>
                            <input type="text" class="form-control bg-black text-white border-secondary" name="cliente" placeholder="Nombre completo del dueño del vehículo" required>
                        </div>
                    </div>

                    <!-- Fila 2: Datos del Vehículo -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Marca del vehículo</label>
                            <input type="text" class="form-control bg-black text-white border-secondary" name="vehiculo_marca" placeholder="Ej: Ford, Fiat, Volkswagen" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Modelo</label>
                            <input type="text" class="form-control bg-black text-white border-secondary" name="vehiculo_modelo" placeholder="Ej: Focus 2.0, Cronos" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Patente / Dominio</label>
                            <input type="text" class="form-control bg-black text-white border-secondary" name="vehiculo_patente" placeholder="Ej: AB123CD o AAA111" required>
                        </div>
                    </div>

                    <!-- Fila 3: Diagnóstico Inicial -->
                    <div class="mb-4">
                        <label class="form-label">Diagnóstico detallado</label>
                        <textarea class="form-control bg-black text-white border-secondary" name="diagnostico" rows="3" placeholder="Describí la falla reportada o el mantenimiento solicitado por el cliente..." required></textarea>
                    </div>

                    <!-- Fila 4: Secciones Dinámicas (Trabajos y Repuestos) -->
                    <div class="row">
                        <!-- Columna Izquierda: Trabajos (Mano de obra) -->
                        <div class="col-md-6 mb-4">
                            <div class="p-3 border border-secondary rounded bg-black-50">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0 text-info">Trabajos / Mano de obra</h5>
                                    <button type="button" class="btn btn-sm btn-outline-info fw-bold" id="btn-add-trabajo">+ Añadir Trabajo</button>
                                </div>
                                <div id="contenedor-trabajos">
                                    <!-- Fila base obligatoria -->
                                    <div class="row g-2 mb-2 trabajo-fila">
                                        <div class="col-8">
                                            <input type="text" class="form-control form-control-sm bg-black text-white border-secondary" name="trabajos[0][descripcion]" placeholder="Ej: Cambio de pastillas de freno" required>
                                        </div>
                                        <div class="col-4">
                                            <input type="number" class="form-control form-control-sm bg-black text-white border-secondary text-center" name="trabajos[0][precio]" placeholder="Costo $" min="0" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Repuestos (Inventario) -->
                        <div class="col-md-6 mb-4">
                            <div class="p-3 border border-secondary rounded bg-black-50">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0 text-success">Repuestos del inventario</h5>
                                    <button type="button" class="btn btn-sm btn-outline-success fw-bold" id="btn-add-repuesto">+ Vincular Repuesto</button>
                                </div>
                                <div id="contenedor-repuestos">
                                    <!-- Se poblará dinámicamente mediante JavaScript -->
                                </div>
                                <p class="text-white-50 small mt-2 mb-0">Seleccioná un repuesto para cargar su precio de stock automáticamente.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Botón de Envío Principal -->
                    <button type="submit" class="btn btn-warning w-100 fw-bold py-2 mt-2">Registrar Orden de Trabajo e Iniciar Servicio</button>
                </form>
            </div>
        </div>
    </div>
        <!-- Sección de Historial de Órdenes -->
    <div class="row mt-4">
        <div class="col-12">
            <h3 class="mb-3 text-warning">Historial de órdenes</h3>
            
            <!-- Cartel de confirmación cuando se cambia un estado -->
            <?php if(isset($_GET['actualizado'])): ?>
                <div class="alert alert-info bg-dark text-info border-info fw-bold mb-3">
                    ℹ️ El estado de la orden de trabajo se actualizó correctamente.
                </div>
            <?php endif; ?>
            
            <div class="table-responsive">
                <table class="table table-dark table-striped table-bordered border-secondary mb-0">
                    <thead>
                        <tr>
                            <th style="width: 90px;">N° Orden</th>
                            <th style="width: 110px;">Fecha</th>
                            <th>Cliente</th>
                            <th>Vehículo (Patente)</th>
                            <th>Diagnóstico</th>
                            <th style="width: 180px;">Estado del Servicio</th>
                            <th style="width: 140px;">Total Cobrado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultado_ordenes && mysqli_num_rows($resultado_ordenes) > 0): ?>
                            <?php while($ord = mysqli_fetch_assoc($resultado_ordenes)): 
                                // Definir color del badge según el estado
                                $badge_color = 'bg-danger'; // pendiente
                                if ($ord['estado'] == 'en_progreso') { $badge_color = 'bg-warning text-black'; }
                                elseif ($ord['estado'] == 'completado') { $badge_color = 'bg-success'; }
                            ?>
                                <tr>
                                    <!-- ID de la Orden -->
                                    <td class="align-middle fw-bold text-warning text-center">#<?php echo $ord['id']; ?></td>
                                    
                                    <!-- Fecha convertida a d/m/Y -->
                                    <td class="align-middle text-center"><?php echo date('d/m/Y', strtotime($ord['fecha'])); ?></td>
                                    
                                    <!-- Nombre del Cliente -->
                                    <td class="align-middle"><?php echo htmlspecialchars($ord['cliente']); ?></td>
                                    
                                    <!-- Vehículo y su Patente -->
                                    <td class="align-middle">
                                        <?php echo htmlspecialchars($ord['vehiculo_marca'] . ' ' . $ord['vehiculo_modelo']); ?> 
                                        <div class="badge bg-secondary d-block mt-1 font-monospace" style="letter-spacing: 1px; max-width: 90px;">
                                            <?php echo htmlspecialchars($ord['vehiculo_patente']); ?>
                                        </div>
                                    </td>
                                    
                                    <!-- Diagnóstico de Entrada -->
                                    <td class="align-middle small text-white-50"><?php echo htmlspecialchars($ord['diagnostico']); ?></td>
                                    
                                    <!-- Selector de Cambios de Estado Inline -->
                                    <td class="align-middle">
                                        <form action="admin_ordenes.php" method="POST" class="d-flex gap-1 mb-0 align-items-center">
                                            <input type="hidden" name="accion" value="actualizar_estado">
                                            <input type="hidden" name="orden_id" value="<?php echo $ord['id']; ?>">
                                            
                                            <select name="estado" class="form-select form-select-sm bg-black text-white border-secondary small" onchange="this.form.submit()">
                                                <option value="pendiente" <?php if($ord['estado'] == 'pendiente') echo 'selected'; ?>>🔴 Pendiente</option>
                                                <option value="en_progreso" <?php if($ord['estado'] == 'en_progreso') echo 'selected'; ?>>🟡 En Progreso</option>
                                                <option value="completado" <?php if($ord['estado'] == 'completado') echo 'selected'; ?>>🟢 Completado</option>
                                            </select>
                                        </form>
                                    </td>
                                    
                                    <!-- Total Monetario Acumulado -->
                                    <td class="align-middle fw-bold text-success text-end pe-3">$<?php echo number_format($ord['total_orden'], 0, ',', '.'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <!-- Plan B: Si la base de datos está vacía -->
                            <tr>
                                <td colspan="7" class="text-center py-4 text-white-50">
                                    No hay órdenes de trabajo registradas en el sistema todavía.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script src="../js/bootstrap.bundle.min.js"></script>

<script>
let contadorTrabajos = 1;
let contadorRepuestos = 0;

// 1. Agregar nueva fila de Trabajo / Mano de obra
document.getElementById('btn-add-trabajo').addEventListener('click', function() {
    const contenedor = document.getElementById('contenedor-trabajos');
    const html = `
        <div class="row g-2 mb-2 trabajo-fila">
            <div class="col-8">
                <input type="text" class="form-control form-control-sm bg-black text-white border-secondary" name="trabajos[${contadorTrabajos}][descripcion]" placeholder="Descripción del trabajo" required>
            </div>
            <div class="col-4 d-flex gap-1">
                <input type="number" class="form-control form-control-sm bg-black text-white border-secondary text-center" name="trabajos[${contadorTrabajos}][precio]" placeholder="Costo $" min="0" required>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.row').remove()">✕</button>
            </div>
        </div>`;
    contenedor.insertAdjacentHTML('beforeend', html);
    contadorTrabajos++;
});

// 2. Mapeo de repuestos disponibles traídos desde el backend de PHP
const listaRepuestos = [
    <?php 
    if ($resultado_repuestos_disponibles && mysqli_num_rows($resultado_repuestos_disponibles) > 0) {
        mysqli_data_seek($resultado_repuestos_disponibles, 0); // Reiniciar el puntero de MySQL
        while($r = mysqli_fetch_assoc($resultado_repuestos_disponibles)) {
            echo "{ id: {$r['id']}, nombre: '" . addslashes($r['producto']) . "', precio: {$r['precio']}, stock: {$r['cantidad']} },";
        }
    }
    ?>
];

// 3. Agregar nueva fila de Repuestos vinculados a stock
document.getElementById('btn-add-repuesto').addEventListener('click', function() {
    const contenedor = document.getElementById('contenedor-repuestos');
    
    let opcionesHTML = '<option value="">-- Seleccionar pieza --</option>';
    listaRepuestos.forEach(item => {
        opcionesHTML += `<option value="${item.id}" data-precio="${item.precio}" data-stock="${item.stock}">${item.nombre} (Dispo: ${item.stock})</option>`;
    });

    const html = `
        <div class="row g-2 mb-2 repuesto-fila">
            <div class="col-6">
                <select class="form-select form-select-sm bg-black text-white border-secondary sel-repuesto" name="repuestos[${contadorRepuestos}][id]" onchange="actualizarPrecioFila(this)" required>
                    ${opcionesHTML}
                </select>
            </div>
            <div class="col-2">
                <input type="number" class="form-control form-control-sm bg-black text-white border-secondary text-center inp-cantidad" name="repuestos[${contadorRepuestos}][cantidad]" value="1" min="1" onchange="validarStockMax(this)" required>
            </div>
            <div class="col-4 d-flex gap-1">
                <input type="number" class="form-control form-control-sm bg-black text-white border-secondary text-center inp-precio" name="repuestos[${contadorRepuestos}][precio]" placeholder="Precio $" min="0" readonly required>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.row').remove()">✕</button>
            </div>
        </div>`;
    contenedor.insertAdjacentHTML('beforeend', html);
    contadorRepuestos++;
});

// 4. Autocompletar el precio unitario guardado al elegir una pieza
function actualizarPrecioFila(selectElement) {
    const opcionSeleccionada = selectElement.options[selectElement.selectedIndex];
    const precio = opcionSeleccionada.getAttribute('data-precio') || 0;
    const stockMax = opcionSeleccionada.getAttribute('data-stock') || 1;
    
    const fila = selectElement.closest('.row');
    fila.querySelector('.inp-precio').value = precio;
    
    const inputCant = fila.querySelector('.inp-cantidad');
    inputCant.setAttribute('max', stockMax);
    
    // Si el usuario ya había cambiado la cantidad, revalidar contra el nuevo tope
    validarStockMax(inputCant);
}

// 5. Control de seguridad: Evitar que se use más repuestos de los disponibles
function validarStockMax(inputElement) {
    const max = parseInt(inputElement.getAttribute('max')) || 1;
    if (parseInt(inputElement.value) > max) {
        alert('⚠️ Atención: No hay stock suficiente en el inventario. Máximo disponible: ' + max);
        inputElement.value = max;
    }
}
</script>