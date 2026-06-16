<?php
include 'conexion.php';
$id = $_GET['id'];
$res = mysqli_query($conexion, "SELECT * FROM contactos WHERE id = $id");
$c = mysqli_fetch_assoc($res);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha: <?php echo $c['nombre']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between mb-3">
                <h3>📄 Detalle del Contacto</h3>
                <a href="contactos.php" class="btn btn-outline-secondary btn-sm">Volver</a>
            </div>

            <div class="card shadow-sm border-dark">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><?php echo $c['nombre']; ?></h4>
                    <span class="badge bg-light text-dark"><?php echo $c['tipo']; ?></span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6 border-end">
                            <h6 class="text-muted small fw-bold">DATOS GENERALES</h6>
                            <p class="mb-1"><strong>👤 Encargado:</strong> <?php echo $c['encargado']; ?></p>
                            <p class="mb-1"><strong>📞 Teléfono:</strong> <?php echo $c['telefono']; ?></p>
                            <p class="mb-1"><strong>📧 Email:</strong> <?php echo $c['email']; ?></p>
                            <p class="mb-1"><strong>⏰ Horarios:</strong> <?php echo $c['horarios']; ?></p>
                        </div>
                        <div class="col-md-6 px-4">
                            <h6 class="text-muted small fw-bold">INFORMACIÓN ADMINISTRATIVA</h6>
                            <p class="mb-1"><strong>💳 Modalidad Pago:</strong> <?php echo $c['modalidad_pago']; ?></p>
                            <p class="mb-1 text-truncate"><strong>🏦 Banco/Alias:</strong> <?php echo $c['datos_bancarios']; ?></p>
                            <p class="mb-1"><strong>💰 Estado Cuenta:</strong> 
                                <?php if($c['tipo'] == 'Proveedor'): ?>
                                    <span class="<?php echo ($c['estado_cuenta'] < 0) ? 'text-danger' : 'text-success'; ?>">
                                        $<?php echo number_format($c['estado_cuenta'], 2); ?>
                                        <small>(<?php echo ($c['estado_cuenta'] < 0) ? 'Debemos' : 'A favor'; ?>)</small>
                                    </span>
                                <?php else: ?>
                                    <span class="<?php echo ($c['estado_cuenta'] > 0) ? 'text-primary' : 'text-muted'; ?>">
                                        $<?php echo number_format($c['estado_cuenta'], 2); ?>
                                        <small>(<?php echo ($c['estado_cuenta'] > 0) ? 'Debe' : 'Al día'; ?>)</small>
                                    </span>
                                <?php endif; ?>
                            </p>

                    <div class="bg-light p-3 rounded">
                        <h6 class="text-muted small fw-bold">NOTAS ADICIONALES</h6>
                        <p class="mb-0 small text-dark"><?php echo $c['notas']; ?></p>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                        <a href="contacto_editar.php?id=<?php echo $id; ?>" class="btn btn-warning btn-sm   fw-bold">✏️ Editar Datos</a>
                    
                            <a href="registrar_movimiento.php?contacto_id=<?php echo $id; ?>" class="btn        btn-primary">
                        <i class="bi bi-cash-coin me-1"></i> Registrar Movimiento
                        </a>
                </div>
            </div>
        </div>
    </div>
    <?php 
            // Preparamos el mensaje para WhatsApp
            $mensaje_wa = "Hola " . $c['encargado'] . ", te saluda de Creesiente. ";
            $mensaje_wa .= "Tu estado de cuenta actual es: $" . number_format($c['estado_cuenta'], 2) . ". ";
            $mensaje_wa .= "Datos bancarios: " . $c['datos_bancarios'];

            // Limpiamos el número de teléfono (quitamos espacios y guiones)
            $tel_limpio = preg_replace('/[^0-9]/', '', $c['telefono']);
            // Si el número no tiene código de país, le agregamos el de Argentina (54) por defecto
            if (strlen($tel_limpio) == 10) { $tel_limpio = "54" . $tel_limpio; }

            $url_whatsapp = "https://api.whatsapp.com/send?phone=" . $tel_limpio . "&text=" . urlencode($mensaje_wa);
            ?>

            <a href="<?php echo $url_whatsapp; ?>" target="_blank" class="btn btn-success btn-sm fw-bold">
                <i class="bi bi-whatsapp"></i> 📱 Enviar por WhatsApp
            </a>
    <div class="mt-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold text-muted small">ÚLTIMOS MOVIMIENTOS / PAGOS</h6>
        <a href="registrar_movimiento.php?contacto_id=<?php echo $id; ?>" class="btn btn-success">
         <i class="bi bi-plus-circle me-1"></i> + Nuevo Movimiento
        </a>
    </div>
    
    <div class="table-responsive">
        <table class="table table-sm table-bordered small">
            <thead class="table-light">
                <tr>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Tipo</th>
                    <th>Monto</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $movs = mysqli_query($conexion, "SELECT * FROM movimientos_contactos WHERE contacto_id = $id ORDER BY fecha DESC LIMIT 10");
                while($m = mysqli_fetch_assoc($movs)):
                ?>
                <tr>
                    <td><?php echo date('d/m/y H:i', strtotime($m['fecha'])); ?></td>
                    <td><?php echo $m['descripcion']; ?></td>
                    <td>
                        <span class="badge <?php echo ($m['tipo_movimiento'] == 'Abono') ? 'bg-success' : 'bg-warning text-dark'; ?>">
                            <?php echo $m['tipo_movimiento']; ?>
                        </span>
                    </td>
                    <td class="fw-bold">$<?php echo number_format($m['monto'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalMovimiento" tabindex="-1">
    <div class="modal-dialog">
        <form action="registrar_movimiento.php" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Movimiento de Cuenta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="contacto_id" value="<?php echo $id; ?>">
                <div class="mb-3">
                    <label class="form-label">Tipo de movimiento</label>
                    <select name="tipo_movimiento" class="form-select">
                        <option value="Abono">🟢 Abono (Pago realizado/recibido)</option>
                        <option value="Cargo">🟡 Cargo (Nueva compra/deuda)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Monto ($)</label>
                    <input type="number" name="monto" step="0.01" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <input type="text" name="descripcion" class="form-control" placeholder="Ej: Pago factura 001 o Compra de harina">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Guardar Movimiento</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>