<?php
include 'conexion.php';
$mensaje = "";

// 1. CAPTURAR EL PRODUCTO SI VIENE DESDE EL STOCK EN MOSTRADOR O RECETAS (CORREGIDO)
$id_preseleccionado = isset($_GET['producto_id']) ? $_GET['producto_id'] : (isset($_GET['id']) ? $_GET['id'] : "");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_prod = mysqli_real_escape_string($conexion, $_POST['producto_id']);
    $cantidad_a_producir = floatval($_POST['cantidad']);
    $fecha_hoy = date('Y-m-d H:i:s');

    // 2. VALIDACIÓN: BUSCAR LA RECETA Y CHEQUEAR STOCK EN 'stock_materia_prima'
    $sql_receta = "SELECT r.id_insumo, r.cantidad_necesaria, s.nombre, s.cantidad as stock_disponible 
                  FROM recetas r 
                  JOIN stock_materia_prima s ON r.id_insumo = s.id 
                  WHERE r.id_producto = '$id_prod'";
    
    $res_receta = mysqli_query($conexion, $sql_receta);
    
    $puedo_producir = true;
    $insumo_insuficiente = "";
    $lista_descuentos = [];

    if (mysqli_num_rows($res_receta) > 0) {
        while ($item = mysqli_fetch_assoc($res_receta)) {
            $gasto_total = $item['cantidad_necesaria'] * $cantidad_a_producir;
            
            if ($item['stock_disponible'] < $gasto_total) {
                $puedo_producir = false;
                $insumo_insuficiente = $item['nombre'];
                break;
            }
            $lista_descuentos[] = ['id' => $item['id_insumo'], 'cantidad' => $gasto_total];
        }
    } else {
        $puedo_producir = false;
        $mensaje = "<div class='alert alert-warning alert-dismissible fade show shadow' role='alert'>
                        ⚠️ <strong>Atención:</strong> Este producto no tiene una receta configurada en el sistema.
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>";
    }

    // 3. EJECUCIÓN: ACTUALIZACIÓN DE AMBOS STOCKS
    if ($puedo_producir) {
        mysqli_begin_transaction($conexion);
        try {
            // A. Sumar al stock de Productos Terminados
            mysqli_query($conexion, "UPDATE productos_terminados SET stock_actual = stock_actual + $cantidad_a_producir WHERE id = '$id_prod'");

            // B. Restar de Materia Prima
            foreach ($lista_descuentos as $desc) {
                $id_ins = $desc['id'];
                $cant_desc = $desc['cantidad'];
                mysqli_query($conexion, "UPDATE stock_materia_prima SET cantidad = cantidad - $cant_desc WHERE id = '$id_ins'");
            }

            mysqli_commit($conexion);
            $mensaje = "<div class='alert alert-success alert-dismissible fade show shadow' role='alert'>
                            🚀 <b>¡Producción Exitosa!</b> Se agregaron $cantidad_a_producir unidades al mostrador y se descontaron los insumos de bodega.
                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                        </div>";
        } catch (Exception $e) {
            mysqli_rollback($conexion);
            $mensaje = "<div class='alert alert-danger shadow'>❌ <b>Error crítico:</b> " . $e->getMessage() . "</div>";
        }
    } elseif ($insumo_insuficiente != "") {
        $mensaje = "<div class='alert alert-danger alert-dismissible fade show shadow' role='alert'>
                        ❌ <b>Falta Stock:</b> No hay suficiente <b>$insumo_insuficiente</b> en bodega para producir esa cantidad.
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>";
    }
}

$productos = mysqli_query($conexion, "SELECT * FROM productos_terminados ORDER BY nombre ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Producción | Creesiente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <?php echo $mensaje; ?>
                
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-success text-white py-3 text-center">
                        <h4 class="mb-0 fw-bold"><i class="bi bi-hammer me-2"></i> Orden de Producción</h4>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">Producto a Elaborar:</label>
                                <select name="producto_id" class="form-select form-select-lg" required>
                                    <option value="">Seleccione un producto...</option>
                                    <?php while($p = mysqli_fetch_assoc($productos)): ?>
                                        <option value="<?php echo $p['id']; ?>" <?php echo ($id_preseleccionado == $p['id']) ? 'selected' : ''; ?>>
                                            <?php echo strtoupper($p['nombre']); ?> (Hay: <?php echo $p['stock_actual']; ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">Cantidad Producida:</label>
                                <div class="input-group input-group-lg">
                                    <input type="number" step="0.01" name="cantidad" class="form-control" placeholder="0.00" required autofocus>
                                    <span class="input-group-text bg-white"><i class="bi bi-box-seam text-muted"></i></span>
                                </div>
                                <div class="form-text mt-2 text-primary">
                                    <i class="bi bi-info-circle-fill"></i> Al finalizar, el sistema restará automáticamente los insumos correspondientes.
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg shadow-sm fw-bold py-2.5">
                                    <i class="bi bi-check-circle me-1"></i> CARGAR PRODUCCIÓN
                                </button>
                                <div class="row g-2 mt-1">
                                    <div class="col-6">
                                        <a href="stock_producido.php" class="btn btn-outline-primary w-100 py-2 btn-sm fw-bold">
                                            <i class="bi bi-shop"></i> Ver Mostrador
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <a href="index.php" class="btn btn-outline-secondary w-100 py-2 btn-sm">
                                            <i class="bi bi-house"></i> Inicio
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>