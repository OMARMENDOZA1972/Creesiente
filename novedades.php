<?php
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $empresa = $_POST['empresa'];
    $novedad = $_POST['novedad'];
    $prioridad = $_POST['prioridad'];
    $fecha = $_POST['fecha']; // Nueva variable para la fecha

    // Agregamos 'fecha' a la consulta de inserción
    $sql = "INSERT INTO agenda (empresa_id, novedad, prioridad, fecha, estado) 
            VALUES ('$empresa', '$novedad', '$prioridad', '$fecha', 'Pendiente')";

    if (mysqli_query($conexion, $sql)) {
        echo "<div class='alert alert-success'>Novedad programada para el $fecha con éxito.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . mysqli_error($conexion) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Programar Novedad | Creesiente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5" style="max-width: 600px;">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">📝 Nueva Novedad Diaria</h4>
        </div>
        <form method="POST" class="card-body">
            <div class="mb-3">
                <label class="form-label">Fecha del Recordatorio:</label>
                <input type="date" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Empresa:</label>
                <select name="empresa" class="form-select" required>
                    <option value="1">Panadería Creesiente</option>
                    <option value="2">Soja Creesiente</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Novedad / Recordatorio:</label>
                <textarea name="novedad" class="form-control" rows="3" required placeholder="Ej: Comprar bolsas de polipropileno..."></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Prioridad:</label>
                <select name="prioridad" class="form-select">
                    <option value="Baja">Baja</option>
                    <option value="Media" selected>Media</option>
                    <option value="Alta">Alta</option>
                </select>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Guardar en Agenda</button>
                <a href="index.php" class="btn btn-outline-secondary">Volver al Inicio</a>
            </div>
        </form>
    </div>
</body>
</html>