<?php
include 'conexion.php';

// Consulta para listar los productos terminados
$sql = "SELECT * FROM productos_terminados ORDER BY nombre ASC";
$res = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Stock Producido | Creesiente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0"><i class="bi bi-shop text-primary"></i> Stock en Mostrador</h2>
            <div>
                <a href="produccion.php" class="btn btn-primary shadow-sm me-2"><i class="bi bi-hammer"></i> Nueva Producción</a>
                <a href="index.php" class="btn btn-outline-secondary shadow-sm">Inicio</a>
            </div>
        </div>

        <div class="mb-3">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="buscadorProductos" class="form-control border-start-0 ps-1" placeholder="Buscar producto por nombre...">
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center" id="tablaProductos">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-start ps-4">Producto Final</th>
                            <th>Stock Actual</th>
                            <th>Mínimo</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($p = mysqli_fetch_assoc($res)): 
                            $alerta = ($p['stock_actual'] <= $p['stock_minimo']);
                        ?>
                        <tr class="<?php echo $alerta ? 'table-warning' : ''; ?>">
                            <td class="text-start fw-bold text-uppercase ps-4 nombre-producto"><?php echo $p['nombre']; ?></td>
                            <td class="fw-bold fs-5">
                                <?php echo $p['stock_actual']; ?> 
                                <small class="text-muted fs-6 fw-normal"><?php echo $p['unidad_medida'] ?? 'un'; ?></small>
                            </td>
                            <td class="text-muted"><?php echo $p['stock_minimo']; ?></td>
                            
                            <td>
                                <?php if($alerta): ?>
                                    <span class="badge bg-danger px-3 py-2">REPOSICIÓN</span>
                                <?php else: ?>
                                    <span class="badge bg-success px-3 py-2">LISTO</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <a href="produccion.php?producto_id=<?php echo $p['id']; ?>&nombre=<?php echo urlencode($p['nombre']); ?>" 
                                   class="btn btn-outline-primary btn-sm px-3 fw-bold rounded-pill">
                                    <i class="bi bi-tools me-1"></i> Producir
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $("#buscadorProductos").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#tablaProductos tbody tr").filter(function() {
                    $(this).toggle($(this).find('.nombre-producto').text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
</body>
</html>