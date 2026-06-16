<?php
include 'conexion.php';

// --- 1. CONSULTAS DE DATOS ORIGINALES CORREGIDAS ---
// Novedades: Pendientes, ordenadas por fecha (columna original: 'novedad')
$res_novedades = mysqli_query($conexion, "SELECT * FROM agenda WHERE estado = 'Pendiente' ORDER BY fecha ASC LIMIT 8");

// Alerta de Maquinaria
$res_maquinaria = mysqli_query($conexion, "SELECT COUNT(*) as total FROM maquinarias WHERE estado = 'Mantenimiento'");
$mant_pendientes = 0; 
if ($res_maquinaria) {
    $fila = mysqli_fetch_assoc($res_maquinaria);
    $mant_pendientes = $fila['total'];
}

// Alertas de Stock (Productos y Materia Prima usando las columnas exactas de tu script)
$res_alertas = mysqli_query($conexion, "SELECT nombre, stock_actual, stock_minimo FROM productos_terminados WHERE stock_actual <= stock_minimo");
$res_materia_critica = mysqli_query($conexion, "SELECT nombre, cantidad, stock_minimo FROM stock_materia_prima WHERE cantidad <= stock_minimo");

// Consulta rápida de empleados para el indicador
$q_emp = mysqli_query($conexion, "SELECT COUNT(*) as total FROM empleados");
$total_empleados = ($q_emp) ? mysqli_fetch_assoc($q_emp)['total'] : 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Central | Potrerillos</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #1e293b; }
        .hero-section { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); border-radius: 24px; padding: 30px; color: white; margin-bottom: 30px; }
        .card-custom { border: none; border-radius: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); background: white; }
        .btn-modern { border-radius: 12px; font-weight: 600; padding: 12px; transition: all 0.3s; text-align: left; display: flex; align-items: center; width: 100%; margin-bottom: 8px; border: none; text-decoration: none; }
        .btn-modern:hover { transform: translateX(5px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .icon-box { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 12px; font-size: 1.1rem; flex-shrink: 0; }
        .section-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em; margin-bottom: 12px; margin-top: 15px; }
    </style>
</head>
<body>

<div class="container py-4">
    <!-- HEADER -->
    <div class="hero-section shadow-lg">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="fw-bold mb-1">SISTEMA CREESIENTE</h2>
                <p class="opacity-75 mb-0">Panadería & Soja • Panel General</p>
            </div>
            <div class="col-md-4 text-end d-none d-md-block">
                <span class="badge bg-white text-dark rounded-pill px-3 py-2"><i class="bi bi-calendar3 me-1"></i> <?php echo date('d/m/Y'); ?></span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- COLUMNA IZQUIERDA: ALERTAS Y AGENDA -->
        <div class="col-lg-7">
            
            <!-- ALERTAS DE STOCK CRÍTICO (Productos Elaborados y Materia Prima) -->
            <?php if(mysqli_num_rows($res_alertas) > 0 || mysqli_num_rows($res_materia_critica) > 0): ?>
            <div class="card card-custom mb-4 p-3 border-start border-danger border-5">
                <h6 class="fw-bold text-danger small mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>⚠️ ¡ATENCIÓN! STOCK CRÍTICO</h6>
                <div class="row g-2">
                    <!-- Productos elaborados -->
                    <?php while($a = mysqli_fetch_assoc($res_alertas)): ?>
                        <div class="col-6 small text-muted"><i class="bi bi-dot"></i> <?php echo $a['nombre']; ?> (Quedan: <?php echo $a['stock_actual']; ?>)</div>
                    <?php endwhile; ?>
                    <!-- Materia prima -->
                    <?php while($mp = mysqli_fetch_assoc($res_materia_critica)): ?>
                        <div class="col-6 small text-muted"><i class="bi bi-dot"></i> Insumo: <?php echo $mp['nombre']; ?> (<?php echo $mp['cantidad']; ?>)</div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- AGENDA -->
            <div class="card card-custom overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-0">
                    <h5 class="fw-bold mb-0 text-dark">Últimas Novedades en Agenda</h5>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Novedad</th>
                                <th class="text-end pe-4">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($res_novedades) > 0): ?>
                                <?php 
                                $hoy = date('Y-m-d');
                                while($reg = mysqli_fetch_assoc($res_novedades)): 
                                    $vencida = ($reg['fecha'] < $hoy);
                                    $texto_color = $vencida ? 'text-danger fw-bold' : 'text-dark';
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="small <?php echo $texto_color; ?>">
                                            <?php echo ($reg['empresa_id'] == 1) ? '🥖 ' : '🌱 '; ?>
                                            <?php echo $reg['novedad']; ?> 
                                        </div>
                                        <div class="text-muted small" style="font-size: 0.7rem;">
                                            Fecha: <?php echo date('d/m', strtotime($reg['fecha'])); ?> 
                                            • <span class="badge <?php echo ($reg['prioridad'] == 'Alta') ? 'bg-danger' : 'bg-secondary'; ?>" style="font-size: 0.85em;"><?php echo $reg['prioridad']; ?></span>
                                            <?php if($vencida): ?> • <span class="text-danger fw-bold">VENCIDA</span> <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="marcar_realizado.php?id=<?php echo $reg['id']; ?>" class="btn btn-sm btn-outline-success border-0"><i class="bi bi-check-circle-fill fs-5"></i></a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="2" class="text-center py-4 text-muted">No hay tareas pendientes</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: TODOS LOS ACCESOS -->
        <div class="col-lg-5">
            <div class="card card-custom p-4">
                
                <!-- PRODUCCIÓN -->
                <div class="section-title">Producción y Stock</div>
                <a href="stock.php" class="btn btn-modern bg-warning bg-opacity-10 text-warning-emphasis">
                    <div class="icon-box bg-warning text-white"><i class="bi bi-box-seam"></i></div>
                    <div><span class="d-block">Stock Materia Prima</span></div>
                </a>
                <a href="stock_producido.php" class="btn btn-modern bg-info bg-opacity-10 text-info-emphasis">
                    <div class="icon-box bg-info text-white"><i class="bi bi-egg-fried"></i></div>
                    <div><span class="d-block">Stock Producido</span></div>
                </a>
                <a href="produccion.php" class="btn btn-modern bg-success bg-opacity-10 text-success-emphasis">
                    <div class="icon-box bg-success text-white"><i class="bi bi-lightning-charge"></i></div>
                    <div><span class="d-block">Producción</span></div>
                </a>
                <a href="recetas.php" class="btn btn-modern bg-secondary bg-opacity-10 text-secondary-emphasis">
                    <div class="icon-box bg-secondary text-white"><i class="bi bi-journal-text"></i></div>
                    <div><span class="d-block">Recetas</span></div>
                </a>

                <!-- ADMINISTRACIÓN -->
                <div class="section-title">Administración y Caja</div>
                <a href="nomina.php" class="btn btn-modern bg-primary bg-opacity-10 text-primary-emphasis">
                    <div class="icon-box bg-primary text-white"><i class="bi bi-people"></i></div>
                    <div><span class="d-block">Nómina Empleados</span></div>
                </a>
                <a href="caja.php" class="btn btn-modern bg-success bg-opacity-10 text-success-emphasis">
                    <div class="icon-box bg-success text-white"><i class="bi bi-cash-coin"></i></div>
                    <div><span class="d-block">Ventas / Caja</span></div>
                </a>
                <a href="novedades.php" class="btn btn-modern bg-dark bg-opacity-10 text-dark">
                    <div class="icon-box bg-dark text-white"><i class="bi bi-calendar-check"></i></div>
                    <div><span class="d-block">Agenda Diaria</span></div>
                </a>

                <!-- INFRAESTRUCTURA -->
                <div class="section-title">Personas e Infraestructura</div>
                <a href="maquinaria.php" class="btn btn-modern bg-danger bg-opacity-10 text-danger-emphasis">
                    <div class="icon-box bg-danger text-white"><i class="bi bi-gear-wide-connected"></i></div>
                    <div class="flex-grow-1"><span class="d-block">Maquinaria</span></div>
                    <?php if($mant_pendientes > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?php echo $mant_pendientes; ?></span>
                    <?php endif; ?>
                </a>
                <a href="contactos.php" class="btn btn-modern bg-secondary bg-opacity-10 text-secondary-emphasis">
                    <div class="icon-box bg-secondary text-white"><i class="bi bi-truck"></i></div>
                    <div><span class="d-block">Proveedores / Clientes</span></div>
                </a>

            </div>
        </div>
    </div>
</div>

</body>
</html>