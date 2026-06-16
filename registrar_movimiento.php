<?php
include 'conexion.php';

// 1. Validar que tengamos un contacto asociado
if (!isset($_GET['contacto_id']) || empty($_GET['contacto_id'])) {
    header("Location: contactos.php");
    exit();
}

$id_contacto = intval($_GET['contacto_id']);
$mensaje = "";

// 2. Traer los datos del contacto para mostrar su nombre en el título
$consulta_contacto = mysqli_query($conexion, "SELECT nombre, tipo FROM contactos WHERE id = $id_contacto");
$contacto = mysqli_fetch_assoc($consulta_contacto);

if (!$contacto) {
    header("Location: contactos.php");
    exit();
}

// 3. PROCESAR EL FORMULARIO CUANDO SE HACE CLIC EN REGISTRAR
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo_movimiento = mysqli_real_escape_string($conexion, $_POST['tipo_movimiento']);
    $monto = floatval($_POST['monto']);
    $fecha = !empty($_POST['fecha']) ? mysqli_real_escape_string($conexion, $_POST['fecha']) : date('Y-m-d');
    $detalle = mysqli_real_escape_string($conexion, $_POST['detalle']); // Viene del textarea

    if ($monto > 0) {
        // Consulta adaptada con tus columnas reales: tipo_movimiento y descripcion
        $sql_insert = "INSERT INTO movimientos_contactos (contacto_id, tipo_movimiento, monto, fecha, descripcion) 
                       VALUES ($id_contacto, '$tipo_movimiento', $monto, '$fecha', '$detalle')";

        if (mysqli_query($conexion, $sql_insert)) {
            // Redireccionar de vuelta a la ficha del contacto con un mensaje de éxito
            header("Location: contacto_ficha.php?id=$id_contacto&msg=ok");
            exit();
        } else {
            $mensaje = "<div class='alert alert-danger shadow-sm'>❌ Error al guardar en la base de datos: " . mysqli_error($conexion) . "</div>";
        }
    } else {
        $mensaje = "<div class='alert alert-warning shadow-sm'>⚠️ Por favor, ingrese un monto mayor a cero.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Movimiento | Creesiente</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #1e293b; }
        .card-custom { border: none; border-radius: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); background: white; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                
                <div class="mb-3">
                    <a href="contacto_ficha.php?id=<?php echo $id_contacto; ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Volver a la Ficha
                    </a>
                </div>

                <?php echo $mensaje; ?>

                <div class="card card-custom p-4 shadow-sm">
                    <div class="text-center mb-4">
                        <span class="badge bg-light text-primary border border-primary border-opacity-25 mb-2 px-3 py-2 text-uppercase">
                            <?php echo htmlspecialchars($contacto['tipo']); ?>
                        </span>
                        <h3 class="fw-bold text-dark mb-1">Registrar Movimiento</h3>
                        <p class="text-muted mb-0">Contacto: <strong><?php echo htmlspecialchars($contacto['nombre']); ?></strong></p>
                    </div>

                    <form method="POST">
                        
                        <div class="mb-3">
                            <label class="small fw-bold mb-1">Tipo de Movimiento:</label>
                            <select name="tipo_movimiento" class="form-select border-2" required>
                                <option value="Entrega de Mercadería">📦 Entrega de Mercadería</option>
                                <option value="Pago Recibido">💵 Pago Recibido / Abono</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="small fw-bold mb-1">Monto ($):</label>
                            <input type="number" step="0.01" name="monto" class="form-control form-control-lg border-2 border-primary border-opacity-50" placeholder="0.00" required>
                        </div>

                        <div class="mb-3">
                            <label class="small fw-bold mb-1">Fecha del Movimiento:</label>
                            <input type="date" name="fecha" class="form-control border-2" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="small fw-bold mb-1">Detalle / Observación:</label>
                            <textarea name="detalle" class="form-control border-2" rows="3" placeholder="Ej: Pago parcial factura N° 1024, entrega de 50 bolsas, etc." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2.5 rounded-pill shadow-sm">
                            <i class="bi bi-check-circle me-1"></i> REGISTRAR MOVIMIENTO
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>