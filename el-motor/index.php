<?php 
session_start();

include("plantilla/cabecera.php");
include 'php/conexion_be.php';
/** @var mysqli $conexion */

// Definir si el usuario es administrador usando la variable de sesión
$es_admin = isset($_SESSION['es_admin']) && $_SESSION['es_admin'] == 1;

// Procesar la modificación masiva de precios si se envía por POST y el usuario es admin
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if($es_admin && isset($_POST['accion']) && $_POST['accion'] == 'actualizar_todos'){
        if(isset($_POST['precios']) && is_array($_POST['precios'])){
            $stmt_prec = $conexion->prepare("UPDATE tabla_servicios SET precio = ? WHERE id = ?");
            
            // Actualizamos cada servicio enviado en el array
            foreach($_POST['precios'] as $id => $nuevo_precio){
                $stmt_prec->bind_param("di", $nuevo_precio, $id);
                $stmt_prec->execute();
            }
            $stmt_prec->close();
        }

        // Redirige manteniendo la posición en la sección de precios (#precios)
        header("Location: index.php#precios");
        exit();
    }
}

// Consultar los servicios de la base de datos
$stmt_serv = $conexion->prepare("SELECT id, servicio, precio FROM tabla_servicios");
$stmt_serv->execute();
$resultado_servicios = $stmt_serv->get_result();

// Mapeo auxiliar con las duraciones originales
$duraciones_originales = [
    "Cambio de aceite y filtro" => "30 a 45 min",
    "Revision general" => "1 hora",
    "Cambio de pastillas de freno" => "1 a 2 horas",
    "Alineacion y balanceo" => "1 hora",
    "Cambio de bateria" => "20 a 30 min",
    "Cambio de bujias" => "45 min",
    "Diagnostico computarizado" => "45 min",
    "Servicios de embrague" => "6 a 10 horas",
    "Cambio de correa" => "4 a 6 horas",
    "Reparacion del sistema de refrigeracion" => "2 a 4 horas"
];
?>
<!--El equipo incluyó comentarios para facilitar el mantenimiento y/o modificaciones futuras-->
<!--Se realizaron cambios de emergencia. Es posible que hayan errores menores-->

  <main>
    <section class="banner">
      <span class="banner-bg"></span>
      <div class="banner-txt">
        <h1>Sitio oficial de El Motor</h1>
        <p>Taller mecánico en Ruta Nacional 14 Km 12, Monte Caseros, Corrientes, Argentina.</p>
      </div>
    </section>
    
    <section id="servicios">
      <div>
        <h2>Nuestros servicios</h2>
        <!-- Lista de Servicios -->
        <h3>Mecánica general e integral</h3>
        <img src="img/mecanica-general.jpg" width="539px" height="335px" alt="Mecánica general e integral" class="d-block mx-auto my-3 img-fluid rounded-5 shadow">
        <p>Reparación y mantenimiento completo de motorizaciones nafteras y diésel. Brindamos soluciones efectivas ante cualquier anomalía mecánica, desde ruidos e irregularidades en la marcha hasta rectificaciones y reparaciones complejas del motor.</p>

        <h3>Servicio de grúa</h3>
        <img src="img/grua.jpeg" width="539px" height="335px" alt="Contacto y envio de grua" class="d-block mx-auto my-3 img-fluid rounded-5 shadow">
        <p>Asistencia y traslado vehicular seguro ante cualquier desperfecto en ruta o avería imprevista que impida la circulación del automotor.</p>

        <h3>Diagnóstico computarizado</h3>
        <img src="img/diagnóstico-computarizado.jpg" width="539px" height="335px" alt="Diagnóstico computarizado automotriz" class="d-block mx-auto my-3 img-fluid rounded-5 shadow">
        <p>Efectuamos un escaneo electrónico integral para identificar con precisión las fallas registradas en la unidad de control del vehículo (ECU). Diagnosticamos sensores, sistemas de inyección y alertas eléctricas, procediendo al borrado de códigos de error según corresponda.</p>

        <h3>Servicio por kilometraje</h3>
        <img src="img/kilometraje.jpg" width="539px" height="335px" alt="Servicio por kilometraje" class="d-block mx-auto my-3 img-fluid rounded-5 shadow">
        <p>Mantenimiento preventivo planificado según los estándares del fabricante para preservar la vida útil de la unidad (servicios de 10k, 20k, 50k kilómetros o más). Incluye la renovación de fluidos lubricantes y el reemplazo de los filtros correspondientes.</p>

        <h3>Frenos y seguridad</h3>
        <img src="img/frenos.jpg" width="539px" height="335px" alt="Mantenimiento de frenos y seguridad" class="d-block mx-auto my-3 img-fluid rounded-5 shadow">
        <p>Inspección integral del sistema de frenado para asegurar una respuesta óptima. Realizamos el cambio de pastillas y discos de freno, la rectificación de campanas, la sustitución de cintas y la renovación del fluido hidráulico del circuito.</p>

        <h3>Tren delantero, trasero y suspensión</h3>
        <img src="img/suspension.jpg" width="539px" height="335px" alt="Reparación de tren delantero y suspensión" class="d-block mx-auto my-3 img-fluid rounded-5 shadow">
        <p>Diagnóstico y reparación de componentes de la suspensión y dirección para restituir el confort de marcha y la estabilidad. Efectuamos el reemplazo de amortiguadores, cazoletas, bujes, bieletas, extremos de dirección y rótulas.</p>

        <h3>Inyección electrónica</h3>
        <img src="img/inyeccion-electronica.jpg" width="539px" height="335px" alt="Servicio de inyección electrónica" class="d-block mx-auto my-3 img-fluid rounded-5 shadow">
        <p>Limpieza y calibración de inyectores mediante tecnología de ultrasonido, evaluación de la presión en la bomba de combustible y optimización general del sistema de alimentación para reducir el consumo y mejorar el rendimiento del motor.</p>

        <h3>Electricidad y baterías</h3>
        <img src="img/bateria.jpg" width="539px" height="335px" alt="Electricidad del automotor y baterías" class="d-block mx-auto my-3 img-fluid rounded-5 shadow">
        <p>Evaluación del sistema de carga y provisión de baterías de primeras marcas. Llevamos a cabo la reparación de alternadores, motores de arranque, sistemas de iluminación externa e interna, y la resolución de cortocircutos eléctricos.</p>

        <h3>Climatización y aire acondicionado</h3>
        <img src="img/climatizacion.jpg" width="539px" height="335px" alt="Climatización y aire acondicionado" class="d-block mx-auto my-3 img-fluid rounded-5 shadow">
        <p>Servicio de recarga de gas refrigerante ecológico, detección de fugas mediante fluidos de contraste y reparación de compresores. Incluye el reemplazo del filtro de habitáculo para asegurar un aire limpio y libre de impurezas.</p><br>
      </div>
    </section>

    <section class="sect" id="precios">
      <h2>Lista de precios</h2>
      
      <div class="container my-5">
        <div class="row justify-content-center">
          <div class="col-10 col-lg-8">
            
            <!-- Si es admin, envolvemos la tabla en un único formulario -->
            <?php if ($es_admin): ?>
            <form action="index.php#precios" method="POST">
              <input type="hidden" name="accion" value="actualizar_todos">
            <?php endif; ?>

              <div class="table-responsive rounded-3 shadow-lg">
                <table class="table table-dark table-striped table-hover align-middle mb-0">
                  <thead>
                    <tr class="text-uppercase tracking-wider border-bottom border-secondary text-warning" style="font-size: 0.9rem;">
                      <th scope="col" class="ps-4 py-3" style="width: 50%;">Servicio Técnico</th>
                      <th scope="col" class="text-end py-3" style="width: 25%;">Precio</th>
                      <th scope="col" class="text-center py-3 ps-3" style="width: 25%;">Duración Estimada</th>
                    </tr>
                  </thead>
                  
                  <tbody class="table-group-divider border-secondary">
                    <?php while($row = $resultado_servicios->fetch_assoc()): 
                      $nombre_servicio = $row['servicio'];
                      $duracion = isset($duraciones_originales[$nombre_servicio]) ? $duraciones_originales[$nombre_servicio] : "A consultar";
                    ?>
                      <tr>
                        <td class="ps-4 fw-medium text-white"><?php echo htmlspecialchars($nombre_servicio); ?></td>
                        <td class="text-end fw-bold">
                          <?php if ($es_admin): ?>
                            <!-- Input matricial vinculado al ID del servicio -->
                            <div class="d-inline-flex align-items-center justify-content-end gap-1 m-0">
                              <span class="text-success">$</span>
                              <input type="number" name="precios[<?php echo $row['id']; ?>]" value="<?php echo htmlspecialchars($row['precio']); ?>" class="form-control form-control-sm text-end text-success fw-bold bg-dark border-secondary" style="width: 110px;" required>
                            </div>
                          <?php else: ?>
                            <!-- Vista para usuario común (solo texto en verde) -->
                            <span class="text-success">$<?php echo number_format($row['precio'], 0, ',', '.'); ?></span>
                          <?php endif; ?>
                        </td>
                        <td class="text-center text-light opacity-75">
                          <?php echo htmlspecialchars($duracion); ?>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>

              <!-- Botón único grande al final de la tabla (solo visible para admin) -->
              <?php if ($es_admin): ?>
                <div class="text-center mt-4">
                  <button type="submit" class="btn btn-warning fw-bold px-5 py-2 shadow">
                    Guardar todos los cambios
                  </button>
                </div>
            </form>
              <?php endif; ?>

              <?php 
                $stmt_serv->close();
                $conexion->close();
              ?>
          </div>
        </div>
      </div>
    </section>
    <section class="sect">
      <h2>¿Necesitás agendar un turno?</h2>
      <p>Si necesitás nuestros servicios, no dudes en agendar un turno mediante el siguiente botón:</p><br>
      <a class="btn btn-outline-light" href="turnos.php">Pedí tu turno</a>
    </section>
    <section class="sect">
      <h2>¿Necesitás una grúa?</h2>
      <p>Si quedaste varado en medio de la ruta o si tu auto dejó de funcionar de repente, no dudes en contactarnos:</p><br>
      <a class="btn btn-outline-light" href="grua.php">Número de grúa</a>
    </section>
    <section class="sect">
      <h2>¿Tenés alguna duda?</h2>
      <p>Si necesitás más información sobre nosotros, no dudes en contactarnos mediante la página de contacto. Envianos un mensaje y te responderemos cuanto antes. Podés acceder por medio del siguiente botón:</p><br>
      <a class="btn btn-outline-light" href="contacto.php">Contactanos</a>
    </section>
  </main>

<?php include("plantilla/pie.php");?>