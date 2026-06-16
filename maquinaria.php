<?php
include 'conexion.php';
$res = mysqli_query($conexion, "SELECT *, DATE_ADD(fecha_ultimo_mantenimiento, INTERVAL frecuencia_dias DAY) as proxima_fecha FROM maquinarias");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mantenimiento | Creesiente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>⚙️ Mantenimiento Preventivo</h2>
        <div>
            <a href="maquina_nueva.php" class="btn btn-dark">📦 Inventario</a>
            <a href="index.php" class="btn btn-outline-secondary">Inicio</a>
        </div>
    </div>

    <div class="row">
        <?php while($m = mysqli_fetch_assoc($res)): 
            $proxima = strtotime($m['proxima_fecha']);
            $dias_restantes = round(($proxima - time()) / (60 * 60 * 24));
            $clase_card = ($dias_restantes <= 0) ? "border-danger bg-light" : "bg-white";
        ?>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm <?php echo $clase_card; ?>">
                <img src="img_maquinas/<?php echo $m['imagen']; ?>" class="card-img-top" style="height:150px; object-fit:cover;">
                <div class="card-body">
                    <h5 class="card-title fw-bold"><?php echo $m['nombre']; ?></h5>
                    
                    <div class="p-2 mb-2 bg-white border rounded">
                        <small class="fw-bold text-muted">🛠️ TAREA ACTUAL:</small><br>
                        <small><?php echo $m['tarea_tecnica']; ?></small>
                    </div>

                    <?php if(!empty($m['ultima_nota'])): ?>
                        <div class="small text-primary mb-2"><strong>Nota anterior:</strong> <?php echo $m['ultima_nota']; ?></div>
                    <?php endif; ?>

                    <p class="small mb-1">Próximo en: <strong><?php echo $dias_restantes; ?> días</strong></p>
                    
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success btn-sm fw-bold" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalMantenimiento" 
                                data-id="<?php echo $m['id']; ?>" 
                                data-nombre="<?php echo $m['nombre']; ?>"
                                data-tarea="<?php echo $m['tarea_tecnica']; ?>">
                            Marcar Realizado
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <div class="modal fade" id="modalMantenimiento" tabindex="-1">
        <div class="modal-dialog">
            <form action="procesar_mantenimiento.php" method="POST" class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Finalizar Mantenimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="modal_id">
                    <p>Registrando service para: <strong id="modal_nombre_maquina"></strong></p>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">¿Cambia la tarea técnica para la próxima?</label>
                        <textarea name="tarea_tecnica" id="modal_tarea" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nota sobre el estado actual:</label>
                        <input type="text" name="nota" class="form-control" placeholder="Ej: Se cambió rulemán, observar ruidos">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Confirmar y Reiniciar Ciclo</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Script para pasar los datos de la tarjeta al modal
        var modal = document.getElementById('modalMantenimiento');
        modal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            document.getElementById('modal_id').value = button.getAttribute('data-id');
            document.getElementById('modal_nombre_maquina').textContent = button.getAttribute('data-nombre');
            document.getElementById('modal_tarea').value = button.getAttribute('data-tarea');
        });
    </script>
</body>
</html>