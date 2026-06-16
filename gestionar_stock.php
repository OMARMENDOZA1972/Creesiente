<?php
include 'conexion.php';

if ($_POST) {
    $insumo_id = $_POST['insumo_id'];
    $cantidad_nueva = $_POST['cantidad'];
    $tipo = $_POST['tipo_movimiento']; // "Entrada" o "Salida"
    $vencimiento = $_POST['fecha_vencimiento']; // Nuevo campo

    // 1. Obtenemos el stock actual
    $consulta = mysqli_query($conexion, "SELECT cantidad FROM stock_materia_prima WHERE id = $insumo_id");
    $fila = mysqli_fetch_assoc($consulta);
    $stock_actual = $fila['cantidad'];

    // 2. Calculamos el nuevo total (CORRECCIÓN DE LÍNEA 13)
    if ($tipo == "Entrada") {
        $nuevo_total = $stock_actual + $cantidad_nueva;
    } else {
        $nuevo_total = $stock_actual - $cantidad_nueva;
    }

    // 3. Actualizamos la tabla con la nueva fecha de vencimiento
    $sql_update = "UPDATE stock_materia_prima SET 
                   cantidad = '$nuevo_total', 
                   fecha_vencimiento = '$vencimiento' 
                   WHERE id = $insumo_id";
    
    if (mysqli_query($conexion, $sql_update)) {
        echo "<div class='alert alert-success'>Movimiento registrado con éxito.</div>";
    } else {
        echo "Error: " . mysqli_error($conexion);
    }
}

// Para el desplegable de insumos
$insumos = mysqli_query($conexion, "SELECT id, nombre FROM stock_materia_prima");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cargar Movimiento | Creesiente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4" style="max-width: 500px;">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">📦 Registrar Entrada/Salida</div>
        <form method="POST" class="card-body">
            <div class="mb-3">
                <label class="form-label">Insumo:</label>
                <select name="insumo_id" class="form-select" required>
                    <?php while($i = mysqli_fetch_assoc($insumos)): ?>
                        <option value="<?php echo $i['id']; ?>"><?php echo $i['nombre']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Tipo de Movimiento:</label>
                <select name="tipo_movimiento" class="form-select">
                    <option value="Entrada">➕ Entrada (Compra/Producción)</option>
                    <option value="Salida">➖ Salida (Uso/Venta)</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Cantidad:</label>
                <input type="number" step="0.01" name="cantidad" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Fecha de Vencimiento:</label>
                <input type="date" name="fecha_vencimiento" class="form-control">
                <small class="text-muted">Solo necesario en "Entradas".</small>
            </div>
            <button type="submit" class="btn btn-primary w-100">Guardar Movimiento</button>
            <a href="stock.php" class="btn btn-link w-100 mt-2">← Volver al Stock</a>
        </form>
    </div>
</body>
</html>