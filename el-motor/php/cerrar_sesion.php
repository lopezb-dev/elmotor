<?php
    session_start();
    session_destroy(); // Borra la sesión de la memoria
    header("Location: ../index.php"); // Regresa al index público
    exit();
?>