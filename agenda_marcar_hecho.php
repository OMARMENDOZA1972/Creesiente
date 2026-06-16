<?php
include 'conexion.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Validamos que sea un número
    
    // Cambiamos el estado a 'Finalizado'
    $sql = "UPDATE agenda SET estado = 'Finalizado' WHERE id = $id";
    
    if (mysqli_query($conexion, $sql)) {
        header("Location: index.php?msj=tarea_ok");
    } else {
        echo "Error al actualizar: " . mysqli_error($conexion);
    }
}
?>