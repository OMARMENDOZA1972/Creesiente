<?php
include 'conexion.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: nomina.php");
    exit();
}

$id_empleado = intval($_GET['id']);
$mensaje = "";

// --- LÓGICA: PROCESAR ACTUALIZACIÓN DEL FORMULARIO Y FOTO ---
if (isset($_POST['actualizar_legajo'])) {
    $dni = mysqli_real_escape_string($conexion, $_POST['dni']);
    $edad = !empty($_POST['edad']) ? intval($_POST['edad']) : "NULL";
    $estado_civil = mysqli_real_escape_string($conexion, $_POST['estado_civil']);
    $obra_social = mysqli_real_escape_string($conexion, $_POST['obra_social']);
    $hijos = intval($_POST['hijos']);
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $domicilio = mysqli_real_escape_string($conexion, $_POST['domicilio']);
    $fecha_ingreso = !empty($_POST['fecha_ingreso']) ? "'" . mysqli_real_escape_string($conexion, $_POST['fecha_ingreso']) . "'" : "NULL";

    // Manejo de la Imagen
    $nombre_foto_db = $_POST['foto_actual']; 
    
    if (isset($_FILES['foto_archivo']) && $_FILES['foto_archivo']['error'] == 0) {
        $dir_subida = 'img_personal/';
        
        if (!file_exists($dir_subida)) {
            mkdir($dir_subida, 0777, true);
        }

        $extension = pathinfo($_FILES['foto_archivo']['name'], PATHINFO_EXTENSION);
        $nuevo_nombre_foto = "empleado_" . $id_empleado . "_" . time() . "." . $extension;
        $ruta_destino = $dir_subida . $nuevo_nombre_foto;

        $formatos_permitidos = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array(strtolower($extension), $formatos_permitidos)) {
            if (move_uploaded_file($_FILES['foto_archivo']['tmp_name'], $ruta_destino)) {
                $nombre_foto_db = $nuevo_nombre_foto;
            } else {
                $mensaje = "<div class='alert alert-danger shadow-sm'>❌ Error al subir el archivo físico al servidor.</div>";
            }
        } else {
            $mensaje = "<div class='alert alert-warning shadow-sm'>⚠️ Formato no permitido (Solo JPG, PNG o WEBP).</div>";
        }
    }

    // Actualización exacta en base a tu estructura de la tabla 'empleados'
    $sql_update = "UPDATE empleados SET 
                    dni = '$dni', 
                    edad = $edad, 
                    estado_civil = '$estado_civil', 
                    obra_social = '$obra_social', 
                    hijos = $hijos, 
                    telefono = '$telefono', 
                    domicilio = '$domicilio', 
                    fecha_ingreso = $fecha_ingreso, 
                    foto = '$nombre_foto_db' 
                  WHERE id = $id_empleado";

    if (mysqli_query($conexion, $sql_update)) {
        $mensaje = "<div class='alert alert-success alert-dismissible fade show shadow-sm' role='alert'>
                        💾 <b>Ficha de legajo actualizada con éxito.</b>
                        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                    </div>";
    } else {
        $mensaje = "<div class='alert alert-danger shadow-sm'>❌ Error al actualizar: " . mysqli_error($conexion) . "</div>";
    }
}

// --- CONSULTA DE DATOS DEL EMPLEADO ---
$consulta_emp = mysqli_query($conexion, "SELECT * FROM empleados WHERE id = $id_empleado");
$empleado = mysqli_fetch_assoc($consulta_emp);

if (!$empleado) {
    header("Location: nomina.php");
    exit();
}

// Lógica de Renderizado de Imagen basada en tu default_avatar.png
if (!empty($empleado['foto']) && file_exists('img_personal/' . $empleado['foto'])) {
    $avatar_render = 'img_personal/' . $empleado['foto'];
} else {
    // Si la foto en la DB es default_avatar.png o está vacía, usamos un fallback limpio
    $avatar_render = 'https://cdn-icons-png.flaticon.com/512/149/149071.png';
}

// --- CONSULTA DEL HISTORIAL (Corregido con columnas reales) ---
$historial = mysqli_query($conexion, "SELECT * FROM novedades_empleados WHERE empleado_id = $id_empleado ORDER BY fecha DESC");

// --- CÁLCULOS DE TOTALES (Corregido: 'tipo' en lugar de 'concepto') ---
$totales_query = mysqli_query($conexion, "SELECT 
    SUM(CASE WHEN tipo = 'Vale' THEN valor ELSE 0 END) as total_vales,
    SUM(CASE WHEN tipo = 'Hora Extra' THEN valor ELSE 0 END) as total_horas 
    FROM novedades_empleados WHERE empleado_id = $id_empleado");
$totales = mysqli_fetch_assoc($totales_query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Legajo: <?php echo htmlspecialchars($empleado['nombre']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .nav-tabs .nav-link { color: #6c757d; border: none; }
        .nav-tabs .nav-link.active { font-weight: bold; border-bottom: 3px solid #0d6efd; color: #0d6efd; background: transparent; }
        .foto-perfil { width: 140px; height: 140px; object-fit: cover; border: 4px solid #f8f9fa; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4 pb-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <a href="nomina.php" class="btn btn-outline-secondary btn-sm me-3"><i class="bi bi-arrow-left"></i> Volver</a>
                <div>
                    <h2 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($empleado['nombre']); ?></h2>
                    <span class="badge bg-primary text-uppercase"><?php echo htmlspecialchars($empleado['puesto']); ?></span>
                </div>
            </div>
            <a href="index.php" class="btn btn-sm btn-light border shadow-sm">Panel Central</a>
        </div>

        <?php echo $mensaje; ?>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 border-start border-success border-4 bg-white">
                    <div class="card-body py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small fw-bold text-uppercase mb-1">Total Vales</p>
                            <h3 class="fw-bold text-success mb-0">$<?php echo number_format($totales['total_vales'] ?? 0, 2); ?></h3>
                        </div>
                        <div class="fs-2 text-success opacity-50"><i class="bi bi-cash-stack"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 border-start border-danger border-4 bg-white">
                    <div class="card-body py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small fw-bold text-uppercase mb-1">Inasistencias</p>
                            <h3 class="fw-bold text-danger mb-0">0 Días</h3>
                        </div>
                        <div class="fs-2 text-danger opacity-50"><i class="bi bi-person-x"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 border-start border-primary border-4 bg-white">
                    <div class="card-body py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small fw-bold text-uppercase mb-1">Horas Extras</p>
                            <h3 class="fw-bold text-primary mb-0"><?php echo number_format($totales['total_horas'] ?? 0, 0); ?> Hrs</h3>
                        </div>
                        <div class="fs-2 text-primary opacity-50"><i class="bi bi-clock"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 bg-white">
            <div class="card-header bg-white pt-3 border-0">
                <ul class="nav nav-tabs card-header-tabs" id="legajoTab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="datos-tab" data-bs-toggle="tab" data-bs-target="#datos" type="button" role="tab"><i class="bi bi-file-earmark-person me-2"></i>Ficha de Datos</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="historial-tab" data-bs-toggle="tab" data-bs-target="#historial" type="button" role="tab"><i class="bi bi-journal-text me-2"></i>Registro de Actividad</button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body p-4 tab-content" id="legajoTabContent">
                
                <div class="tab-pane fade show active" id="datos" role="tabpanel">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="foto_actual" value="<?php echo htmlspecialchars($empleado['foto']); ?>">

                        <div class="row g-4">
                            <div class="col-lg-3 text-center border-end">
                                <div class="p-2">
                                    <div class="position-relative d-inline-block">
                                        <img src="<?php echo $avatar_render; ?>" class="rounded-circle shadow-sm foto-perfil mb-3" id="preview_foto" alt="Foto">
                                    </div>
                                    <div class="mb-2">
                                        <label for="foto_archivo" class="btn btn-sm btn-outline-primary w-100 fw-bold">
                                            <i class="bi bi-camera"></i> Seleccionar Foto
                                        </label>
                                        <input type="file" name="foto_archivo" id="foto_archivo" class="d-none" accept="image/*" onchange="previsualizar(event)">
                                    </div>
                                    <small class="text-muted d-block small">Formatos válidos: JPG, PNG, WEBP</small>
                                </div>
                            </div>

                            <div class="col-lg-9">
                                <h5 class="fw-bold mb-3 text-secondary border-bottom pb-2">Datos Personales</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Nombre Completo:</label>
                                        <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($empleado['nombre']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Número de Documento (DNI):</label>
                                        <input type="text" name="dni" class="form-control" value="<?php echo htmlspecialchars($empleado['dni'] ?? ''); ?>" placeholder="Ej: 35123456">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Edad:</label>
                                        <input type="number" name="edad" class="form-control" value="<?php echo htmlspecialchars($empleado['edad'] ?? ''); ?>" placeholder="Ej: 30">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small fw-bold">Estado Civil:</label>
                                        <select name="estado_civil" class="form-select">
                                            <option value="" <?php echo empty($empleado['estado_civil']) ? 'selected' : ''; ?>>Seleccionar...</option>
                                            <option value="Soltero/a" <?php echo ($empleado['estado_civil'] == 'Soltero/a') ? 'selected' : ''; ?>>Soltero/a</option>
                                            <option value="Casado/a" <?php echo ($empleado['estado_civil'] == 'Casado/a') ? 'selected' : ''; ?>>Casado/a</option>
                                            <option value="Divorciado/a" <?php echo ($empleado['estado_civil'] == 'Divorciado/a') ? 'selected' : ''; ?>>Divorciado/a</option>
                                            <option value="En Unión de Hecho" <?php echo ($empleado['estado_civil'] == 'En Unión de Hecho') ? 'selected' : ''; ?>>En Unión de Hecho</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Hijos a Cargo:</label>
                                        <input type="number" name="hijos" class="form-control" value="<?php echo intval($empleado['hijos']); ?>" min="0">
                                    </div>
                                </div>

                                <h5 class="fw-bold mt-4 mb-3 text-secondary border-bottom pb-2">Información Laboral y Social</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Obra Social:</label>
                                        <input type="text" name="obra_social" class="form-control" value="<?php echo htmlspecialchars($empleado['obra_social'] ?? ''); ?>" placeholder="Ej: OSPRERA">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Fecha de Ingreso:</label>
                                        <input type="date" name="fecha_ingreso" class="form-control" value="<?php echo htmlspecialchars($empleado['fecha_ingreso'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Teléfono de Contacto:</label>
                                        <input type="text" name="telefono" class="form-control" value="<?php echo htmlspecialchars($empleado['telefono'] ?? ''); ?>" placeholder="Ej: 261555555">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label small fw-bold">Domicilio:</label>
                                        <input type="text" name="domicilio" class="form-control" value="<?php echo htmlspecialchars($empleado['domicilio'] ?? ''); ?>" placeholder="Ej: Calle San Martín 123">
                                    </div>
                                </div>

                                <div class="mt-4 pt-2 border-top text-end">
                                    <button type="submit" name="actualizar_legajo" class="btn btn-primary px-4 fw-bold shadow-sm">
                                        <i class="bi bi-floppy"></i> Guardar Cambios
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="tab-pane fade" id="historial" role="tabpanel">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Concepto</th>
                    <th class="text-end">Valor / Importe</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if($historial && mysqli_num_rows($historial) > 0): ?>
                    <?php while($h = mysqli_fetch_assoc($historial)): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($h['fecha'])); ?></td>
                            <td>
                                <span class="badge <?php echo ($h['tipo'] == 'Vale') ? 'bg-success' : 'bg-primary'; ?>">
                                    <?php echo htmlspecialchars($h['tipo']); ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold <?php echo ($h['tipo'] == 'Vale') ? 'text-success' : 'text-primary'; ?>">
                                <?php echo ($h['tipo'] == 'Vale') ? '$' : ''; ?><?php echo number_format($h['valor'], 2); ?>
                            </td>
                            <td class="text-muted small"><?php echo !empty($h['observaciones']) ? htmlspecialchars($h['observaciones']) : '<i>Sin observaciones</i>'; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted p-4">No hay movimientos registrados de actividad.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

            </div>
        </div>
    </div>

    <script>
        function previsualizar(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('preview_foto');
                output.src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>