<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../estilos/bootstrap.css">
  <link rel="stylesheet" href="../estilos/estilo.css">
  <link rel="stylesheet" href="../estilos/estilo-sesion-registro.css">
  <title>El Motor - Admin</title>
  <link rel="icon" type="image/png" href="../img/logo-el-motor.png">
</head>

<header>
    <nav
      class="navbar navbar-expand-md fixed-top navbar-dark bg-black bg-opacity-75 bg-blur border-btm shadow-lg"
    >
      <div class="container">
        <a class="navbar-brand" href="index.php"><img src="../img/logo-el-motor.png" alt="Logo El Motor" width="75"></a>
        <button
          class="navbar-toggler d-lg-none"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#collapsibleNavId"
          aria-controls="collapsibleNavId"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="collapsibleNavId">
          <ul class="navbar-nav ms-auto mt-2 mt-lg-0 gap-3">
            <li class="nav-item">
              <a class="nav-link" href="../index.php" aria-current="page"
                >Inicio
                <span class="visually-hidden">(current)</span></a
              >
            </li>
            <li class="nav-item">
              <a class="nav-link" href="../grua.php">Llamar grúa</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="../turnos.php">Pedir turno</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="../contacto.php">Contacto</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../nosotros.php">Nosotros</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="admin_turnos.php">Admin</a>
            </li>
            <li class="nav-item">
              <a class="btn btn-outline-danger" href="../php/cerrar_sesion.php">Cerrar sesión</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
</header>