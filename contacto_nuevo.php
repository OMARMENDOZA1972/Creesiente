<?php
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $encargado = $_POST['encargado'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $tipo = $_POST['tipo'];
    $empresa = $_POST['empresa'];
    $horarios = $_POST['horarios'];
    $pago = $_POST['modalidad_pago'];
    $banco = $_POST['datos_bancarios'];
    $notas = $_POST['notas'];

    $sql = "INSERT INTO contactos (nombre, encargado, telefono, email, tipo, empresa_vinculo, horarios, modalidad_pago, datos_bancarios, notas) 
            VALUES ('$nombre', '$encargado', '$telefono', '$email', '$tipo', '$empresa', '$horarios', '$pago', '$banco', '$notas')";
    
    if (mysqli_query($conexion, $sql)) {
        echo "<div class='alert alert-success text-center'>✅ Contacto guardado correctamente.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . mysqli_error($conexion) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Contacto | Creesiente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4" style="max-width: 800px;">
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">👤 Registrar Nuevo Contacto</h5>
        </div>
        <form method="POST" class="card-body row g-3">
            <div class="col-md-8">
                <label class="form-label fw-bold">Razón Social / Empresa</label>
                <input type="text" name="nombre" class="form-control" required placeholder="Ej: Molino Cañuelas">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="Cliente">👤 Cliente</option>
                    <option value="Proveedor">🚚 Proveedor</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Persona de Contacto / Encargado</label>
                <input type="text" name="encargado" class="form-control" placeholder="Ej: Juan Pérez">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Horarios de Atención / Entrega</label>
                <input type="text" name="horarios" class="form-control" placeholder="Ej: Lunes a Viernes 08:00 a 16:00">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Teléfono</label>
                <input type="text" name="telefono" class="form-control" placeholder="Ej: 261 1234567">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Email</label>
                <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Modalidad de Pago</label>
                <input type="text" name="modalidad_pago" class="form-control" placeholder="Ej: Contado, Transferencia 7 días">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Alias / CBU / Cuenta</label>
                <input type="text" name="datos_bancarios" class="form-control" placeholder="Alias o número de cuenta">
            </div>
            <div class="col-md-12">
                <label class="form-label fw-bold">Vínculo con Empresa</label>
                <select name="empresa" class="form-select">
                    <option value="1">Panadería Creesiente</option>
                    <option value="2">Soja</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold small text-danger">Límite de Alerta ($)</label>
                <input type="number" name="limite_saldo" class="form-control" placeholder="Ej: 50000" value="<?php echo $c['limite_saldo']; ?>">
                <div class="form-text">El sistema avisará en el inicio cuando el saldo supere este monto.</div>
            </div>
            <div class="col-md-12">
                <label class="form-label fw-bold">Notas Internas</label>
                <textarea name="notas" class="form-control" rows="2" placeholder="Dirección, CUIT, o detalles importantes..."></textarea>
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">Guardar Contacto</button>
                <a href="contactos.php" class="btn btn-outline-secondary">Volver a Agenda</a>
            </div>
        </form>
    </div>
</body>
</html>