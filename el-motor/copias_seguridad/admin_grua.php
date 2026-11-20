<?php 
session_start();

if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] !=1){
    header("Location: index.php");
    exit();
}

include 'php/conexion_be.php';
/** @var mysqli $conexion*/

$