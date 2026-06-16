<?php
include 'conexion.php';

echo "<h2>🔍 Diagnóstico de Base de Datos - Creesiente</h2>";

function revisar_tabla($conexion, $tabla) {
    echo "<h3>Tabla: $tabla</h3>";
    $resultado = mysqli_query($conexion, "DESCRIBE $tabla");
    
    if ($resultado) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>
                <tr style='background: #eee;'>
                    <th>Columna</th>
                    <th>Tipo</th>
                    <th>¿Nulo?</th>
                    <th>Key</th>
                </tr>";
        while ($columna = mysqli_fetch_assoc($resultado)) {
            echo "<tr>
                    <td><strong>{$columna['Field']}</strong></td>
                    <td>{$columna['Type']}</td>
                    <td>{$columna['Null']}</td>
                    <td>{$columna['Key']}</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red;'>❌ Error: La tabla '$tabla' no existe.</p>";
    }
    echo "<hr>";
}

// Revisamos las 3 tablas críticas
revisar_tabla($conexion, 'movimientos_stock');
revisar_tabla($conexion, 'productos_terminados');
revisar_tabla($conexion, 'stock_materia_prima');

echo "<h4>🚀 Próximos pasos:</h4>";
echo "<p>Si en la tabla <strong>movimientos_stock</strong> ves que dice <strong>id_producto</strong>, 
      asegúrate de que en tu código de producción NO estés escribiendo 'producto_id'.</p>";
?>