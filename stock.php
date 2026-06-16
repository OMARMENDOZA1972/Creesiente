<?php
include 'conexion.php';

$mensaje = "";

// --- LÓGICA 1: PROCESAR COMPRA RÁPIDA DE INSUMO ---
if (isset($_POST['procesar_compra'])) {
    $insumo_id = intval($_POST['insumo_id']);
    $cantidad_comprada = floatval($_POST['cantidad_compra']);
    $costo_total = floatval($_POST['costo_total']);
    $empresa_id = intval($_POST['empresa_id']);
    $nombre_insumo = mysqli_real_escape_string($conexion, $_POST['nombre_insumo']);

    if ($insumo_id > 0 && $cantidad_comprada > 0 && $costo_total > 0) {
        // 1. Sumar stock al insumo correspondiente
        $sql_stock = "UPDATE stock_materia_prima SET cantidad = cantidad + $cantidad_comprada WHERE id = $insumo_id";
        
        // 2. Registrar el egreso automáticamente en la tabla caja
        $descripcion_caja = mysqli_real_escape_string($conexion, "[Compra Insumo] $nombre_insumo x $cantidad_comprada");
        $sql_caja = "INSERT INTO caja (empresa_id, tipo, monto, descripcion, fecha) 
             VALUES ('$empresa_id', 'Egreso', '$costo_total', '$descripcion_caja', NOW())";

        if (mysqli_query($conexion, $sql_stock) && mysqli_query($conexion, $sql_caja)) {
            $mensaje = "<div class='alert alert-success alert-dismissible fade show shadow-sm' role='alert'>
                            ✨ <strong>¡Compra registrada!</strong> Se agregaron $cantidad_comprada unidades al stock y se descontaron $$costo_total de la caja.
                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                        </div>";
        } else {
            $mensaje = "<div class='alert alert-danger'>❌ Error al procesar la transacción: " . mysqli_error($conexion) . "</div>";
        }
    }
}

// --- LÓGICA 2: PARA GUARDAR NUEVA MATERIA PRIMA ---
if (isset($_POST['crear_insumo'])) {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $unidad = $_POST['unidad_medida'];
    $minimo = $_POST['stock_minimo'];
    $empresa = $_POST['empresa_id'];

    $sql_insert = "INSERT INTO stock_materia_prima (nombre, cantidad, unidad_medida, stock_minimo, empresa_id) 
                   VALUES ('$nombre', 0, '$unidad', '$minimo', '$empresa')";
    
    if (mysqli_query($conexion, $sql_insert)) {
        header("Location: stock.php?msg=success");
        exit();
    }
}

if (isset($_GET['msg']) && $_GET['msg'] == 'success') {
    $mensaje = "<div class='alert alert-success alert-dismissible fade show shadow-sm'>✨ Nuevo insumo creado con éxito en bodega.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
}

// Consulta unificada de stock
$sql = "SELECT * FROM stock_materia_prima ORDER BY nombre ASC";
$res = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Stock Materia Prima | Creesiente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0"><i class="bi bi-box-seam text-success"></i> Materia Prima</h2>
            <div>
                <button class="btn btn-success shadow-sm me-2" data-bs-toggle="modal" data-bs-target="#modalInsumo">
                    <i class="bi bi-plus-circle"></i> Nuevo Insumo
                </button>
                <a href="index.php" class="btn btn-outline-secondary shadow-sm">Inicio</a>
            </div>
        </div>

        <?php echo $mensaje; ?>

        <div class="mb-3">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="buscadorInsumos" class="form-control border-start-0 ps-1" placeholder="Buscar insumo por nombre...">
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaInsumos">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3">Insumo</th>
                            <th>Stock Actual</th>
                            <th>Mínimo</th>
                            <th>Unidad</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($res)): 
                            $critico = ($row['cantidad'] <= $row['stock_minimo']);
                        ?>
                        <tr class="<?php echo $critico ? 'table-danger' : ''; ?>">
                            <td class="fw-bold ps-3 nombre-insumo"><?php echo $row['nombre']; ?></td>
                            <td class="fs-5 fw-semibold"><?php echo number_format($row['cantidad'], 2); ?></td>
                            <td class="text-muted"><?php echo $row['stock_minimo']; ?></td>
                            <td><span class="badge bg-light text-dark border"><?php echo $row['unidad_medida']; ?></span></td>
                            
                            <td class="text-center">
                                <?php if($critico): ?>
                                    <span class="badge bg-danger px-2 py-1.5">FALTA STOCK</span>
                                <?php else: ?>
                                    <span class="badge bg-success px-2 py-1.5">OK</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <button type="button" class="btn btn-outline-primary btn-sm px-3 fw-bold btn-comprar" 
                                        data-id="<?php echo $row['id']; ?>" 
                                        data-nombre="<?php echo $row['nombre']; ?>"
                                        data-unidad="<?php echo $row['unidad_medida']; ?>"
                                        data-empresa="<?php echo $row['empresa_id']; ?>">
                                    <i class="bi bi-cart-plus me-1"></i> Abastecer
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCompraRapida" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-cart-plus"></i> Registrar Ingreso / Compra</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Al guardar, se incrementará el stock actual del almacén y se registrará un gasto en tu módulo de caja diaria.</p>
                    
                    <h4 id="compra_nombre_display" class="fw-bold text-dark mb-3">Insumo</h4>
                    
                    <input type="hidden" name="insumo_id" id="compra_insumo_id">
                    <input type="hidden" name="empresa_id" id="compra_empresa_id">
                    <input type="hidden" name="nombre_insumo" id="compra_nombre_insumo">

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="small fw-bold mb-1">Cantidad comprada:</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="cantidad_compra" class="form-control" placeholder="0.00" required>
                                <span class="input-group-text" id="compra_unidad_badge">ud</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="small fw-bold mb-1">Costo Total Pagado ($):</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="costo_total" class="form-control" placeholder="0.00" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="procesar_compra" class="btn btn-primary fw-bold px-4">INGRESAR Y DEBITAR</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalInsumo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold">Registrar Nuevo Insumo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="fw-bold small mb-1">Nombre del Insumo:</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Harina 000" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="fw-bold small mb-1">Unidad:</label>
                            <select name="unidad_medida" class="form-select">
                                <option value="kg">kg</option>
                                <option value="unidades">unidades</option>
                                <option value="litros">litros</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="fw-bold small mb-1">Stock Mínimo:</label>
                            <input type="number" step="0.01" name="stock_minimo" class="form-control" value="5" required>
                        </div>
                    </div>
                    <div class="mb-1">
                        <label class="fw-bold small mb-1">Asignar a Unidad de Negocio:</label>
                        <select name="empresa_id" class="form-select">
                            <option value="1">🥖 Panadería Potrerillos</option>
                            <option value="2">🌱 Planta de Soja</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="crear_insumo" class="btn btn-success w-100 fw-bold py-2">GUARDAR EN BODEGA</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Manejo dinámico del modal de abastecimiento
            $('.btn-comprar').on('click', function() {
                var id = $(this).data('id');
                var nombre = $(this).data('nombre');
                var unidad = $(this).data('unidad');
                var empresa = $(this).data('empresa');

                $('#compra_insumo_id').val(id);
                $('#compra_empresa_id').val(empresa);
                $('#compra_nombre_insumo').val(nombre);
                $('#compra_nombre_display').text(nombre);
                $('#compra_unidad_badge').text(unidad);

                var modalCompra = new bootstrap.Modal(document.getElementById('modalCompraRapida'));
                modalCompra.show();
            });

            // Buscador en tiempo real de la tabla
            $("#buscadorInsumos").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#tablaInsumos tbody tr").filter(function() {
                    $(this).toggle($(this).find('.nombre-insumo').text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
</body>
</html>