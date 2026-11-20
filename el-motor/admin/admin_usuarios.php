<?php
session_start();

if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
    header("Location: index.php"); 
    exit();
}

include '../php/conexion_be.php';
/** @var mysqli $conexion*/
    
// PROCESAR ACCIONES
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Eliminar un usuario del sistema
    if (isset($_POST['accion']) && $_POST['accion'] == 'eliminar_usuario') {
        $usuario_email = $_POST['usuario_email'];

        //Se usa el email como identificador para que el borrado sea seguro
        $stmt_elim_user = $conexion->prepare("DELETE FROM usuarios WHERE email = ?");
        $stmt_elim_user->bind_param("s", $usuario_email);
        $stmt_elim_user->execute();
        $stmt_elim_user->close();

        header("Location: admin_usuarios.php");
        exit();
    }

    // NUEVA ACCIÓN: Cambiar el rol del usuario (Admin / Usuario común)
    if (isset($_POST['accion']) && $_POST['accion'] == 'cambiar_rol') {
        $usuario_email = $_POST['usuario_email'];
        $nuevo_rol = (int)$_POST['nuevo_rol']; // Forzamos a entero (0 o 1)

        $stmt_rol = $conexion->prepare("UPDATE usuarios SET es_admin = ? WHERE email = ?");
        $stmt_rol->bind_param("is", $nuevo_rol, $usuario_email);
        $stmt_rol->execute();
        $stmt_rol->close();

        header("Location: admin_usuarios.php");
        exit();
    }

}


// NUEVA GESTIÓN DE BÚSQUEDA Y FILTROS PARA USUARIOS
$busqueda_user = isset($_GET['busqueda_user']) ? trim($_GET['busqueda_user']) : '';
$orden_user = isset($_GET['orden_user']) && $_GET['orden_user'] == 'DESC' ? 'DESC' : 'ASC';

// SE AGREGÓ EL CAMPO "es_admin" A LAS CONSULTAS SELECT
if ($busqueda_user !== '') {
    $stmt_user_list = $conexion->prepare("SELECT nombre_completo, usuario, email, es_admin FROM usuarios WHERE nombre_completo LIKE ? OR usuario LIKE ? OR email LIKE ? ORDER BY nombre_completo $orden_user");
    $param_busqueda_user = "%" . $busqueda_user . "%";
    $stmt_user_list->bind_param("sss", $param_busqueda_user, $param_busqueda_user, $param_busqueda_user);
    $stmt_user_list->execute();
    $resultado_usuarios = $stmt_user_list->get_result();
    $stmt_user_list->close();
} else {
    $query_usuarios = "SELECT nombre_completo, usuario, email, es_admin FROM usuarios ORDER BY nombre_completo $orden_user";
    $resultado_usuarios = mysqli_query($conexion, $query_usuarios);
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
                <!-- MENÚ DE NAVEGACIÓN ENTRE SECCIONES -->
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
                        
                        <a href="admin_inventario.php" class="btn btn-outline-light fw-bold px-3">
                            🛠️ Inventario y repuestos
                        </a>
                        
                        <a href="admin_usuarios.php" class="btn btn-light fw-bold px-3">
                            👥 Usuarios del sistema
                        </a>
                    </div>
                </div>
            </div>
        </div>


        <!-- SECCIÓN 4: USUARIOS REGISTRADOS -->
        <div class="row">
            <div class="col-12">
                <h3 class="mb-3 text-success">Usuarios registrados en el sistema</h3>
                
                <!-- Formulario de Buscador y Filtros para Usuarios -->
                <form method="GET" action="admin_usuarios.php" class="row g-3 mb-4">
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
                        <a href="admin_usuarios.php" class="btn btn-outline-secondary w-50">Limpiar</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-dark table-striped table-bordered border-secondary align-middle">
                        <thead>
                            <tr>
                                <th>Nombre Completo</th>
                                <th>Nombre de Usuario</th>
                                <th>Correo Electrónico</th>
                                <th style="width: 190px;">Rol de Usuario</th>
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
                                        
                                        <!-- NUEVA COLUMNA: Modificador de Rol inline -->
                                        <td>
                                            <form action="admin_usuarios.php" method="POST" class="d-flex gap-1 mb-0">
                                                <input type="hidden" name="accion" value="cambiar_rol">
                                                <input type="hidden" name="usuario_email" value="<?php echo $row['email']; ?>">
                                                <select name="nuevo_rol" class="form-select form-select-sm bg-black text-white border-secondary">
                                                    <option value="0" <?php echo ($row['es_admin'] == 0) ? 'selected' : ''; ?>>Usuario Común</option>
                                                    <option value="1" <?php echo ($row['es_admin'] == 1) ? 'selected' : ''; ?>>Administrador</option>
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Actualizar Rol">💾</button>
                                            </form>
                                        </td>

                                        <td class="text-center">
                                            <!-- Botón para eliminar el usuario -->
                                            <form action="admin_usuarios.php" method="POST" class="m-0" onsubmit="return confirm('¿Estás seguro de que deseas eliminar al usuario <?php echo htmlspecialchars($row['usuario']); ?>? Esta acción no se puede deshacer.');">
                                                <input type="hidden" name="accion" value="eliminar_usuario">
                                                <input type="hidden" name="usuario_email" value="<?php echo $row['email']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger fw-bold">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } 
                            } else { ?>
                                    <tr><td colspan="5" class="text-center">No hay usuarios registrados todavía o no coinciden con la búsqueda.</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <script src="../js/bootstrap.bundle.min.js"></script>
