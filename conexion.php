<?php
$servidor = "localhost";
$usuario  = "root";
$password = ""; // Por defecto en XAMPP está vacío
$db       = "sistema_creesiente";

$conexion = mysqli_connect($servidor, $usuario, $password, $db);

if (!$conexion) {
    die("Fallo la conexión: " . mysqli_connect_error());
}

// Esto es importante para que los acentos de "Panadería" se vean bien
mysqli_set_charset($conexion, "utf8"); 
?>