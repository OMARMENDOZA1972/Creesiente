<?php
include 'conexion.php';
$id = $_GET['id'];

// Si se envían los datos nuevos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $encargado = $_POST['encargado'];
    $tel = $_POST['telefono'];
    $banco = $_POST['datos_bancarios'];
    $limite = $_POST['limite_saldo'];

    $sql = "UPDATE contactos SET nombre='$nombre', encargado='$encargado', telefono='$tel', datos_bancarios='$banco', limite_saldo='$limite' WHERE id=$id";
    
    if (mysqli_query($conexion, $sql)) {
        header("Location: contacto_ficha.php?id=$id&msj=editado");
    }
}

$res = mysqli_query($conexion, "SELECT * FROM contactos WHERE id = $id");
$c = mysqli_fetch_assoc($res);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Contacto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning"><strong>✏️ Editar Datos de <?php echo $c['nombre']; ?></strong></div>
        <form method="POST" class="card-body">
            <div class="mb-3"><label>Nombre / Razón Social</label><input type="text" name="nombre" class="form-control" value="<?php echo $c['nombre']; ?>"></div>
            <div class="mb-3"><label>Encargado</label><input type="text" name="encargado" class="form-control" value="<?php echo $c['encargado']; ?>"></div>
            <div class="mb-3"><label>Teléfono</label><input type="text" name="telefono" class="form-control" value="<?php echo $c['telefono']; ?>"></div>
            <div class="mb-3"><label>Datos Bancarios (Alias/CBU)</label><input type="text" name="datos_bancarios" class="form-control" value="<?php echo $c['datos_bancarios']; ?>"></div>
            <div class="mb-3"><label>Límite de Saldo para Alertas ($)</label><input type="number" name="limite_saldo" class="form-control" value="<?php echo $c['limite_saldo']; ?>"></div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="contacto_ficha.php?id=<?php echo $id; ?>" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>