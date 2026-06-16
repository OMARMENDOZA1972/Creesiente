<?php
include 'conexion.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Cambiamos el estado de 'Pendiente' a 'Finalizado'
    $sql = "UPDATE agenda SET estado = 'Finalizado' WHERE id = $id";
    
    if (mysqli_query($conexion, $sql)) {
        // Redirigimos al index con un mensaje de éxito
        header("Location: index.php?msj=logrado");
        exit;
    } else {
        echo "Error al actualizar: " . mysqli_error($conexion);
    }
}
?>