<?php
include 'conexion.php';

$mensaje = "";

// --- 1. LÓGICA PARA CREAR UN PRODUCTO NUEVO ---
if (isset($_POST['crear_producto_nuevo'])) {
    $nom_prod = mysqli_real_escape_string($conexion, $_POST['nombre_nuevo_prod']);
    $u_medida = $_POST['unidad_medida_prod'];
    $s_minimo = $_POST['stock_minimo_prod'];

    $sql_nuevo_p = "INSERT INTO productos_terminados (nombre, stock_actual, stock_minimo, unidad_medida) 
                    VALUES ('$nom_prod', 0, '$s_minimo', '$u_medida')";
    
    if (mysqli_query($conexion, $sql_nuevo_p)) {
        $mensaje = "<div class='alert alert-success alert-dismissible fade show shadow-sm' role='alert'>
                        ✨ Producto <b>$nom_prod</b> creado. Ya puedes asignarle ingredientes.
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
    }
}

// --- 2. LÓGICA PARA VINCULAR INGREDIENTE (CORREGIDO ERROR DE SINTAXIS) ---
if (isset($_POST['guardar_ingrediente'])) {
    $id_producto = $_POST['id_producto'];
    $id_insumo = $_POST['id_insumo'];
    $cantidad = $_POST['cantidad'];

    // Evitar duplicados en la receta
    $check = mysqli_query($conexion, "SELECT id FROM recetas WHERE id_producto = '$id_producto' AND id_insumo = '$id_insumo'");
    if (mysqli_num_rows($check) > 0) {
        $mensaje = "<div class='alert alert-warning alert-dismissible fade show shadow-sm' role='alert'>
                        ⚠️ Este ingrediente ya forma parte de la receta seleccionada.
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
    } else {
        $sql = "INSERT INTO recetas (id_producto, id_insumo, cantidad_necesaria) 
                VALUES ('$id_producto', '$id_insumo', '$cantidad')";
        if (mysqli_query($conexion, $sql)) {
            $mensaje = "<div class='alert alert-success alert-dismissible fade show shadow-sm' role='alert'>
                            ✅ Ingrediente vinculado con éxito.
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        </div>";
        }
    }
}

// Consultas para los selectores
$productos = mysqli_query($conexion, "SELECT id, nombre FROM productos_terminados ORDER BY nombre ASC");
$insumos = mysqli_query($conexion, "SELECT id, nombre, unidad_medida FROM stock_materia_prima ORDER BY nombre ASC");

// --- 3. CONSULTA Y AGRUPACIÓN PARA EL RECETARIO ESTANDARIZADO ---
$sql_ver_recetas = "SELECT r.id, r.id_producto, p.nombre as producto, i.nombre as insumo, r.cantidad_necesaria, i.unidad_medida 
                    FROM recetas r
                    JOIN productos_terminados p ON r.id_producto = p.id
                    JOIN stock_materia_prima i ON r.id_insumo = i.id
                    ORDER BY p.nombre ASC, i.nombre ASC";
$res_recetas = mysqli_query($conexion, $sql_ver_recetas);

// Estructuramos las recetas en un array asociativo agrupado por producto
$recetario_agrupado = [];
if ($res_recetas && mysqli_num_rows($res_recetas) > 0) {
    while ($row = mysqli_fetch_assoc($res_recetas)) {
        $id_p = $row['id_producto'];
        if (!isset($recetario_agrupado[$id_p])) {
            $recetario_agrupado[$id_p] = [
                'nombre_producto' => $row['producto'],
                'ingredientes' => []
            ];
        }
        $recetario_agrupado[$id_p]['ingredientes'][] = [
            'insumo' => $row['insumo'],
            'cantidad' => $row['cantidad_necesaria'],
            'unidad' => $row['unidad_medida']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Libro de Recetas | Creesiente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .card-receta { transition: transform 0.2s, box-shadow 0.2s; }
        .card-receta:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.08)!important; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4 pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark mb-0"><i class="bi bi-book-half me-2 text-primary"></i> Libro de Recetas</h2>
            <a href="index.php" class="btn btn-outline-secondary btn-sm shadow-sm"><i class="bi bi-arrow-left"></i> Volver al Inicio</a>
        </div>

        <?php echo $mensaje; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-dark text-white fw-bold py-3">
                        1. Registrar Producto Final
                    </div>
                    <form method="POST" class="card-body">
                        <div class="mb-3">
                            <label class="small fw-bold text-muted">Nombre del Producto:</label>
                            <input type="text" name="nombre_nuevo_prod" class="form-control" placeholder="Ej: Pan de Campo" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-7">
                                <label class="small fw-bold text-muted">Unidad de Venta:</label>
                                <select name="unidad_medida_prod" class="form-select">
                                    <option value="unidades">unidades</option>
                                    <option value="kg">kg</option>
                                    <option value="docenas">docenas</option>
                                </select>
                            </div>
                            <div class="col-5">
                                <label class="small fw-bold text-muted">Alerta Mín:</label>
                                <input type="number" name="stock_minimo_prod" class="form-control" value="10">
                            </div>
                        </div>
                        <button type="submit" name="crear_producto_nuevo" class="btn btn-dark w-100 shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i> Crear Producto
                        </button>
                    </form>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white fw-bold py-3">
                        2. Agregar Insumos a Receta
                    </div>
                    <form method="POST" class="card-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">¿Para qué producto?</label>
                            <select name="id_producto" class="form-select border-primary" required>
                                <option value="" disabled selected>Elegir producto...</option>
                                <?php mysqli_data_seek($productos, 0); while($p = mysqli_fetch_assoc($productos)): ?>
                                    <option value="<?php echo $p['id']; ?>"><?php echo $p['nombre']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">¿Qué insumo usa?</label>
                            <select name="id_insumo" class="form-select" required>
                                <option value="" disabled selected>Elegir insumo...</option>
                                <?php mysqli_data_seek($insumos, 0); while($i = mysqli_fetch_assoc($insumos)): ?>
                                    <option value="<?php echo $i['id']; ?>"><?php echo $i['nombre']; ?> (<?php echo $i['unidad_medida']; ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Cantidad por cada unidad:</label>
                            <div class="input-group">
                                <input type="number" step="0.001" name="cantidad" class="form-control" placeholder="0.500" required>
                                <span class="input-group-text bg-light">cant.</span>
                            </div>
                            <div class="form-text">Ej: Para 1kg de Pan usa 0.600 kg de Harina.</div>
                        </div>
                        <button type="submit" name="guardar_ingrediente" class="btn btn-primary w-100 shadow-sm fw-bold">
                            VINCULAR INGREDIENTE
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0 bg-transparent">
                    <div class="card-header bg-white py-3 border-0 rounded shadow-sm mb-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-journal-text me-2 text-success"></i>Estructuras de Producción Estandarizadas</h5>
                        <span class="badge bg-success rounded-pill"><?php echo count($recetario_agrupado); ?> Productos Configurados</span>
                    </div>

                    <?php if(!empty($recetario_agrupado)): ?>
                        <div class="row g-3">
                            <?php foreach($recetario_agrupado as $id_producto => $receta): ?>
                                <div class="col-md-6">
                                    <div class="card card-receta border-0 shadow-sm h-100 bg-white">
                                        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center border-bottom-0">
                                            <span class="fw-bold text-dark text-uppercase small tracking-wide">
                                                🍞 <?php echo $receta['nombre_producto']; ?>
                                            </span>
                                            <a href="produccion.php?id_prod=<?php echo $id_producto; ?>" class="btn btn-sm btn-outline-primary px-2 py-0 fw-bold" style="font-size: 0.8rem;">
                                                <i class="bi bi-gear-fill"></i> Producir
                                            </a>
                                        </div>
                                        
                                        <div class="card-body p-0">
                                            <ul class="list-group list-group-flush mb-0">
                                                <?php foreach($receta['ingredientes'] as $ingrediente): ?>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2 border-light">
                                                        <span class="small text-secondary">
                                                            <i class="bi bi-box-seam me-1 text-muted"></i> <?php echo $ingrediente['insumo']; ?>
                                                        </span>
                                                        <span class="fw-bold text-primary small">
                                                            <?php echo number_format($ingrediente['cantidad'], 3); ?> 
                                                            <small class="text-muted fw-normal"><?php echo $ingrediente['unidad']; ?></small>
                                                        </span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="card shadow-sm border-0 text-center py-5 bg-white rounded">
                            <div class="card-body">
                                <i class="bi bi-journal-x display-4 text-muted mb-3"></i>
                                <h6 class="text-muted fw-bold">No hay recetas estandarizadas</h6>
                                <p class="text-muted small mb-0">Usa los módulos de la izquierda para registrar y asociar materias primas.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>