<?php
include 'conexion.php';

// --- Lógica de procesamiento (Mantenemos la que ya funcionaba) ---
if (isset($_POST['guardar_empleado'])) {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $puesto = mysqli_real_escape_string($conexion, $_POST['puesto']);
    $empresa_id = $_POST['empresa_id'];
    mysqli_query($conexion, "INSERT INTO empleados (nombre, puesto, empresa_id, estado) VALUES ('$nombre', '$puesto', '$empresa_id', 'Activo')");
    header("Location: nomina.php"); exit();
}

if (isset($_POST['cargar_novedad'])) {
    $id_emp = $_POST['id_empleado'];
    $tipo = $_POST['tipo_novedad'];
    $valor = $_POST['valor'];
    $obs = mysqli_real_escape_string($conexion, $_POST['observaciones']);
    $fecha = date('Y-m-d');
    
    // Doble registro: Legajo + Caja (si es salida de dinero)
    mysqli_query($conexion, "INSERT INTO novedades_empleados (empleado_id, tipo, valor, observaciones, fecha) VALUES ('$id_emp', '$tipo', '$valor', '$obs', '$fecha')");
    
    if ($tipo == 'Vale' || $tipo == 'Jornal') {
        $res_nom = mysqli_query($conexion, "SELECT nombre FROM empleados WHERE id = '$id_emp'");
        $n_emp = mysqli_fetch_assoc($res_nom)['nombre'];
        $det = "Pago $tipo: $n_emp " . ($obs ? "($obs)" : "");
        mysqli_query($conexion, "INSERT INTO caja (fecha, detalle, monto, tipo, categoria) VALUES ('$fecha', '$det', '$valor', 'Egreso', 'Sueldos')");
    }
    header("Location: nomina.php"); exit();
}

$res_emp = mysqli_query($conexion, "SELECT * FROM empleados ORDER BY nombre ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal | Potrerillos</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; color: #334155; }
        .main-card { border: none; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .btn-modern { border-radius: 10px; padding: 10px 20px; font-weight: 600; transition: all 0.3s; }
        .btn-primary-modern { background-color: #4f46e5; border: none; color: white; }
        .btn-primary-modern:hover { background-color: #4338ca; transform: translateY(-1px); }
        .btn-home { background: white; border: 1px solid #e2e8f0; color: #64748b; }
        .table thead th { background-color: #f1f5f9; color: #475569; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; border: none; }
        .table tbody td { border-bottom: 1px solid #f1f5f9; padding: 16px; }
        .badge-puesto { background-color: #e0e7ff; color: #4338ca; font-weight: 500; border-radius: 6px; padding: 5px 10px; }
        .modal-content { border-radius: 20px; border: none; }
        .modal-header { border-bottom: 1px solid #f1f5f9; padding: 25px; }
        .form-control, .form-select { border-radius: 10px; padding: 12px; border: 1px solid #e2e8f0; }
    </style>
</head>
<body>

    <div class="container py-5">
        <div class="row align-items-center mb-5">
            <div class="col">
                <div class="d-flex align-items-center">
                    <a href="index.php" class="btn btn-modern btn-home me-3 shadow-sm">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div>
                        <h2 class="fw-bold mb-0">Personal</h2>
                        <p class="text-muted mb-0">Gestión de nómina y legajos</p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-modern btn-primary-modern shadow-sm"                 data-bs-toggle="modal" data-bs-target="#modalNuevo">
                    <i class="bi bi-person-plus-fill me-2"></i> Nuevo Empleado
                </button>
            </div>
        </div>

        <div class="card main-card overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Empleado</th>
                            <th>Puesto</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($e = mysqli_fetch_assoc($res_emp)): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; color: #4f46e5;">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <span class="fw-semibold"><?php echo $e['nombre']; ?></span>
                                </div>
                            </td>
                            <td><span class="badge-puesto"><?php echo $e['puesto']; ?></span></td>
                            <td class="text-end pe-4">
                                <a href="ver_legajo.php?id=<?php echo $e['id']; ?>" class="btn btn-sm btn-modern btn-home me-1">
                                    <i class="bi bi-file-earmark-text me-1"></i>Legajo
                                </a>
                                <button class="btn btn-sm btn-modern btn-primary-modern" 
                                        onclick="abrirNovedad(<?php echo $e['id']; ?>, '<?php echo $e['nombre']; ?>')">
                                    <i class="bi bi-plus-circle me-1"></i>Novedad
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalNuevo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content shadow-lg border-0" style="border-radius: 20px;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0"><i class="bi bi-person-plus text-primary me-2"></i>Registrar Personal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4">
                    <label class="form-label small fw-bold text-uppercase text-muted">Nombre Completo</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0" style="border-radius: 10px 0 0 10px;">
                            <i class="bi bi-person text-muted"></i>
                        </span>
                        <input type="text" name="nombre" class="form-control bg-light border-start-0" 
                               placeholder="Ej: Juan Pérez" style="border-radius: 0 10px 10px 0;" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-uppercase text-muted">Puesto / Cargo</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0" style="border-radius: 10px 0 0 10px;">
                            <i class="bi bi-briefcase text-muted"></i>
                        </span>
                        <input type="text" name="puesto" class="form-control bg-light border-start-0" 
                               placeholder="Ej: Maestro Panadero" style="border-radius: 0 10px 10px 0;" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-uppercase text-muted">Unidad de Negocio</label>
                    <select name="empresa_id" class="form-select bg-light" style="border-radius: 10px; padding: 12px;">
                        <option value="1">Panadería Potrerillos</option>
                        <option value="2">Unidad Soja</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" name="guardar_empleado" class="btn btn-modern btn-primary-modern w-100 py-3 shadow">
                    Confirmar Registro
                </button>
            </div>
        </form>
    </div>
</div>                        
    <div class="modal fade" id="modalNovedad" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" class="modal-content shadow-lg">
                <div class="modal-header">
                    <h5 class="fw-bold mb-0">Cargar Novedad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted">Empleado: <span id="span_nombre" class="fw-bold text-dark"></span></p>
                    <input type="hidden" name="id_empleado" id="input_id">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Tipo de Movimiento</label>
                        <select name="tipo_novedad" class="form-select" required>
                            <option value="Vale">Vale de Efectivo</option>
                            <option value="Jornal">Pago de Jornal</option>
                            <option value="Inasistencia">Inasistencia</option>
                            <option value="Hora Extra">Hora Extra</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Monto / Valor</label>
                        <input type="number" step="0.01" name="valor" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="3" placeholder="Detalles opcionales..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" name="cargar_novedad" class="btn btn-modern btn-primary-modern w-100 py-3">Registrar Movimiento</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function abrirNovedad(id, nombre) {
            document.getElementById('input_id').value = id;
            document.getElementById('span_nombre').innerText = nombre;
            var myModal = new bootstrap.Modal(document.getElementById('modalNovedad'));
            myModal.show();
        }
    </script>
</body>
</html>