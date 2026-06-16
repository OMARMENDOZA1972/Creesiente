<?php
include 'conexion.php';
$sql = "SELECT * FROM contactos ORDER BY nombre ASC";
$res = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agenda | Creesiente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📋 Agenda de Contactos</h2>
        <div>
            <a href="contacto_nuevo.php" class="btn btn-success">➕ Nuevo</a>
            <a href="index.php" class="btn btn-outline-secondary">Inicio</a>
        </div>
    </div>

    <div class="card shadow">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Nombre / Razón Social</th>
                    <th>Encargado</th>
                    <th>Tipo</th>
                    <th>Empresa</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($c = mysqli_fetch_assoc($res)): ?>
                <tr>
                    <td><strong><?php echo $c['nombre']; ?></strong></td>
                    <td><?php echo $c['encargado']; ?></td>
                    <td>
                        <span class="badge <?php echo ($c['tipo'] == 'Proveedor') ? 'bg-info' : 'bg-primary'; ?>">
                            <?php echo $c['tipo']; ?>
                        </span>
                    </td>
                    <td><?php echo ($c['empresa_vinculo'] == 1) ? '🥖 Pan' : '🌱 Soja'; ?></td>
                    <td class="text-center">
                        <a href="contacto_ficha.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-dark">📂 Ver Ficha</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>