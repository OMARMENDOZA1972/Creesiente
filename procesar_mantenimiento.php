<?php
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nueva_tarea = $_POST['tarea_tecnica'];
    $nota = $_POST['nota'];
    $hoy = date('Y-m-d');

    // 1. Guardamos en el historial
    $stmt1 = $conexion->prepare("INSERT INTO historial_mantenimiento (maquinaria_id, fecha, tarea_realizada, notas) VALUES (?, ?, ?, ?)");
    $stmt1->bind_param("isss", $id, $hoy, $nueva_tarea, $nota);
    $stmt1->execute();

    // 2. Actualizamos la máquina (Fecha, Estado, Nueva Tarea y Nota)
    $stmt2 = $conexion->prepare("UPDATE maquinarias SET fecha_ultimo_mantenimiento = ?, tarea_tecnica = ?, ultima_nota = ?, estado = 'Operativo' WHERE id = ?");
    $stmt2->bind_param("sssi", $hoy, $nueva_tarea, $nota, $id);
    $stmt2->execute();

    header("Location: maquinaria.php?success=1");
}
?>