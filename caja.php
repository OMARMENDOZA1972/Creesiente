<?php
include 'conexion.php';

$mensaje = "";
if (isset($_GET['msg']) && $_GET['msg'] == 'ok') {
    $mensaje = "<div class='alert alert-success shadow-sm'>✨ Movimiento registrado con éxito.</div>";
}

// --- LÓGICA 1: PROCESAR COMPRA RÁPIDA DE INSUMO (Viene desde stock.php) ---
if (isset($_POST['procesar_compra'])) {
    $insumo_id = intval($_POST['insumo_id']);
    $cantidad_comprada = floatval($_POST['cantidad_compra']);
    $costo_total = floatval($_POST['costo_total']);
    $empresa_id = intval($_POST['empresa_id']);
    $nombre_insumo = mysqli_real_escape_string($conexion, $_POST['nombre_insumo']);

    if ($insumo_id > 0 && $cantidad_comprada > 0 && $costo_total > 0) {
        // 1. Sumar stock al insumo correspondiente
        $sql_stock = "UPDATE stock_materia_prima SET cantidad = cantidad + $cantidad_comprada WHERE id = $insumo_id";
        
        // 2. Registrar el egreso en la tabla caja (Monto POSITIVO, la consulta se encarga de restarlo)
        $descripcion_caja = mysqli_real_escape_string($conexion, "[Compra Insumo] $nombre_insumo x $cantidad_comprada");
        
        $sql_caja = "INSERT INTO caja (empresa_id, tipo, monto, descripcion, fecha) 
                     VALUES ('$empresa_id', 'Egreso', '$costo_total', '$descripcion_caja', NOW())";

        if (mysqli_query($conexion, $sql_stock) && mysqli_query($conexion, $sql_caja)) {
            $mensaje = "<div class='alert alert-success alert-dismissible fade show shadow-sm' role='alert'>
                            ✨ <strong>¡Compra registrada con éxito!</strong> El stock se actualizó y el total de la caja diaria fue recalculado correctamente.
                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                        </div>";
        } else {
            $mensaje = "<div class='alert alert-danger'>❌ Error al procesar la transacción: " . mysqli_error($conexion) . "</div>";
        }
    }
}

// --- LÓGICA 2: PROCESAR FORMULARIO MANUAL DE CAJA ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['procesar_compra'])) {
    $empresa = $_POST['empresa'];
    $tipo = $_POST['tipo'];
    $monto = $_POST['monto'];
    $motivo = $_POST['motivo'];
    
    $producto_id = ($motivo == 'Venta' && isset($_POST['producto_id'])) ? $_POST['producto_id'] : 0;
    $cantidad = ($motivo == 'Venta' && isset($_POST['cantidad_prod'])) ? $_POST['cantidad_prod'] : 0;
    
    $nota_detalle = isset($_POST['nota_detalle']) ? mysqli_real_escape_string($conexion, $_POST['nota_detalle']) : '';
    $desc_final = ($motivo == 'Venta') ? $nota_detalle : "[$motivo] " . $nota_detalle;

    $sql_caja = "INSERT INTO caja (empresa_id, tipo, monto, descripcion, fecha) 
                 VALUES ('$empresa', '$tipo', '$monto', '$desc_final', NOW())";
    
    if (mysqli_query($conexion, $sql_caja)) {
        if ($motivo == 'Venta' && $producto_id > 0 && $cantidad > 0) {
            mysqli_query($conexion, "UPDATE productos_terminados SET stock_actual = stock_actual - $cantidad WHERE id = $producto_id");
        }
        
        header("Location: caja.php?msg=ok");
        exit();
    } else {
        $mensaje = "<div class='alert alert-danger'>❌ Error en la base de datos: " . mysqli_error($conexion) . "</div>";
    }
}

// 2. CONSULTAS PARA LA VISTA
$res_movimientos = mysqli_query($conexion, "SELECT * FROM caja ORDER BY fecha DESC LIMIT 30");
$res_productos = mysqli_query($conexion, "SELECT id, nombre, stock_actual FROM productos_terminados ORDER BY nombre ASC");

// Cálculo rápido de saldos del día de hoy (Sincronizado usando CURDATE de MySQL)
$totales_hoy = mysqli_query($conexion, "
    SELECT 
        SUM(CASE WHEN empresa_id = 1 AND tipo = 'Ingreso' THEN ABS(monto) ELSE 0 END) - 
        SUM(CASE WHEN empresa_id = 1 AND tipo = 'Egreso' THEN ABS(monto) ELSE 0 END) as neto_panaderia,
        SUM(CASE WHEN empresa_id = 2 AND tipo = 'Ingreso' THEN ABS(monto) ELSE 0 END) - 
        SUM(CASE WHEN empresa_id = 2 AND tipo = 'Egreso' THEN ABS(monto) ELSE 0 END) as neto_soja
    FROM caja 
    WHERE DATE(fecha) = CURDATE()
");
$fila_totales = mysqli_fetch_assoc($totales_hoy);
$neto_pan = $fila_totales['neto_panaderia'] ?? 0;
$neto_soja = $fila_totales['neto_soja'] ?? 0;
$res_productos = mysqli_query($conexion, "SELECT id, nombre, stock_actual FROM productos_terminados ORDER BY nombre ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caja Diaria | Creesiente</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #1e293b; }
        .card-custom { border: none; border-radius: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); background: white; }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark mb-0"><i class="bi bi-cash-stack text-success me-2"></i> Caja y Ventas</h2>
            <a href="index.php" class="btn btn-outline-secondary rounded-pill px-3"><i class="bi bi-arrow-left me-1"></i> Volver</a>
        </div>

        <?php echo $mensaje; ?>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="p-3 bg-white border-start border-warning border-5 rounded shadow-sm d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-bold d-block" style="font-size:0.75rem;">Caja Panadería Hoy</small>
                        <h4 class="fw-bold mb-0 text-dark">$<?php echo number_format($neto_pan, 2); ?></h4>
                    </div>
                    <span class="fs-2">🥖</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 bg-white border-start border-success border-5 rounded shadow-sm d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-bold d-block" style="font-size:0.75rem;">Caja Planta Soja Hoy</small>
                        <h4 class="fw-bold mb-0 text-dark">$<?php echo number_format($neto_soja, 2); ?></h4>
                    </div>
                    <span class="fs-2">🌱</span>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card card-custom p-4">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-plus-circle me-1"></i>Nuevo Movimiento</h5>
                    
                    <form method="POST" id="formCaja">
                        <div class="mb-3">
                            <label class="small fw-bold mb-1">Unidad de Negocio:</label>
                            <select name="empresa" class="form-select">
                                <option value="1">🥖 Panadería Potrerillos</option>
                                <option value="2">🌱 Planta de Soja</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="small fw-bold mb-1">Motivo:</label>
                            <select name="motivo" id="motivo_selector" class="form-select" onchange="actualizarFormulario()">
                                <option value="Venta">Venta de Producto</option>
                                <option value="Gasto">Gasto / Compra Insumo</option>
                                <option value="Retiro">Retiro Personal</option>
                            </select>
                        </div>

                        <div id="seccion_venta" class="p-3 bg-light border border-primary border-opacity-25 rounded mb-3">
                            <label class="small fw-bold text-primary mb-1"><i class="bi bi-bag-check me-1"></i>Producto vendido:</label>
                            <select name="producto_id" class="form-select mb-2">
                                <option value="0" selected>Ninguno / Otro</option>
                                <?php if($res_productos): ?>
                                    <?php while($p = mysqli_fetch_assoc($res_productos)): ?>
                                        <option value="<?php echo $p['id']; ?>"><?php echo $p['nombre']; ?> (Stock: <?php echo $p['stock_actual']; ?>)</option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                            
                            <label class="small fw-bold text-muted mb-1">Cantidad Vendida:</label>
                            <input type="number" step="0.01" name="cantidad_prod" class="form-control" value="0">
                        </div>

                        <div class="mb-3">
                            <label class="small fw-bold mb-1">Tipo de Movimiento:</label>
                            <select name="tipo" id="tipo_selector" class="form-select fw-bold">
                                <option value="Ingreso" class="text-success">Ingreso (+)</option>
                                <option value="Egreso" class="text-danger">Egreso (-)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="small fw-bold mb-1">Monto de la Operación ($):</label>
                            <input type="number" step="0.01" name="monto" class="form-control form-control-lg border-success" placeholder="0.00" required>
                        </div>

                        <div class="mb-4">
                            <label class="small fw-bold mb-1">Detalle / Nota:</label>
                            <input type="text" name="nota_detalle" class="form-control" placeholder="Ej: Compra de levadura / Venta mañana" required>
                        </div>

                        <button type="submit" class="btn btn-success w-100 fw-bold py-2 shadow-sm">
                            <i class="bi bi-check-lg me-1"></i> REGISTRAR EN CAJA
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card card-custom overflow-hidden">
                    <div class="card-header bg-white py-3 px-4 border-0">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-muted"></i>Últimos Movimientos</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Fecha / Hora</th>
                                    <th>Detalle</th>
                                    <th class="text-end pe-4">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($res_movimientos) > 0): ?>
                                    <?php while($m = mysqli_fetch_assoc($res_movimientos)): ?>
                                    <tr>
                                        <td class="ps-4 small text-muted"><?php echo date('d/m • H:i', strtotime($m['fecha'])); ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark border me-1"><?php echo ($m['empresa_id'] == 1) ? '🥖' : '🌱'; ?></span>
                                            <span class="text-dark small fw-semibold"><?php echo $m['descripcion']; ?></span>
                                        </td>
                                        <td class="text-end pe-4 fw-bold <?php echo ($m['tipo'] == 'Ingreso') ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo ($m['tipo'] == 'Ingreso' ? '+' : '-') . " $" . number_format(abs($m['monto']), 2); ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center py-4 text-muted">No hay movimientos registrados hoy.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function actualizarFormulario() {
            var motivo = document.getElementById('motivo_selector').value;
            var seccionVenta = document.getElementById('seccion_venta');
            var tipoSelector = document.getElementById('tipo_selector');

            if (motivo === 'Venta') {
                seccionVenta.style.display = 'block';
                tipoSelector.value = 'Ingreso';
            } else {
                seccionVenta.style.display = 'none';
                tipoSelector.value = 'Egreso';
            }
        }
        document.addEventListener("DOMContentLoaded", function() {
            actualizarFormulario();
        });
    </script>
</body>
</html>