<?php
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $desc = $_POST['descripcion'];
    $frecuencia = $_POST['frecuencia'];
    $tarea = $_POST['tarea_tecnica'];
    $ultimo = $_POST['ultimo_service'];

    // LÓGICA PARA SUBIR LA IMAGEN
    $nombre_imagen = 'default_machine.png'; // Imagen por defecto

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $ruta_destino = 'img_maquinas/';
        
        // Creamos un nombre único para evitar duplicados (ej: 1617234567_horno.jpg)
        $nombre_archivo_original = $_FILES['imagen']['name'];
        $ext = pathinfo($nombre_archivo_original, PATHINFO_EXTENSION);
        $nombre_unico = time() . '_' . strtolower(str_replace(' ', '_', $nombre)) . '.' . $ext;
        
        $ruta_completa = $ruta_destino . $nombre_unico;

        // Intentamos mover el archivo subido a la carpeta de destino
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_completa)) {
            $nombre_imagen = $nombre_unico; // Guardamos el nuevo nombre para la BD
        } else {
            echo "<div class='alert alert-danger'>Error al subir la imagen. Se usará la imagen por defecto.</div>";
        }
    }

    // INSERTAMOS EN LA BASE DE DATOS (incluyendo la imagen)
    $sql = "INSERT INTO maquinarias (nombre, descripcion, frecuencia_dias, tarea_tecnica, fecha_ultimo_mantenimiento, estado, imagen) 
            VALUES ('$nombre', '$desc', '$frecuencia', '$tarea', '$ultimo', 'Operativo', '$nombre_imagen')";
    
    if (mysqli_query($conexion, $sql)) {
        echo "<div class='alert alert-success text-center'>✅ Máquina '$nombre' añadida correctamente al inventario.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . mysqli_error($conexion) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario | Alta de Maquinaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4" style="max-width: 700px;">
    <div class="card shadow-lg border-dark">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">📦 Nueva Máquina al Inventario</h5>
            <a href="maquinaria.php" class="btn btn-sm btn-outline-light">Ver Lista</a>
        </div>
        
        <form method="POST" class="card-body row g-3" enctype="multipart/form-data">
            
            <div class="col-md-12">
                <label class="form-label fw-bold small">Nombre del Equipo</label>
                <input type="text" name="nombre" class="form-control form-control-sm" required placeholder="Ej: Amasadora Industrial 50Kg">
            </div>

            <div class="col-md-12">
                <label class="form-label fw-bold small">Uso principal / Ubicación</label>
                <input type="text" name="descripcion" class="form-control form-control-sm" placeholder="Ej: Panadería - Sector Pastelería">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold small">Subir Foto (Opcional)</label>
                <input type="file" name="imagen" class="form-control form-control-sm" accept="image/*">
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold small">Frecuencia (Días)</label>
                <input type="number" name="frecuencia" class="form-control form-control-sm" value="30">
            </div>
            
            <div class="col-md-3">
                <label class="form-label fw-bold small">Último Service</label>
                <input type="date" name="ultimo_service" class="form-control form-control-sm" value="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="col-md-12">
                <label class="form-label fw-bold small">Tarea Técnica Específica</label>
                <textarea name="tarea_tecnica" class="form-control form-control-sm" rows="3" placeholder="Ej: Engrase de rulemanes y revisión de quemadores"></textarea>
            </div>

            <div class="col-12 d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-primary fw-bold">Guardar Equipo en Inventario</button>
                <a href="index.php" class="btn btn-link text-decoration-none text-muted">Volver al Inicio</a>
            </div>
        </form>
    </div>
</body>
</html>