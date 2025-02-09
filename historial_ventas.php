<?php
session_start();
require 'conexion.php'; 

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener todas las ventas
$query = $conn->prepare("SELECT ventas.*, productos.nombre FROM ventas INNER JOIN productos ON ventas.id_producto = productos.id ORDER BY ventas.fecha DESC");
$query->execute();
$ventas = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Ventas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Historial de Ventas</h2>
    
    <table border="1">
        <thead>
            <tr>
                <th>ID Venta</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Total</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ventas as $venta): ?>
                <tr>
                    <td><?php echo $venta['id']; ?></td>
                    <td><?php echo $venta['nombre']; ?></td>
                    <td><?php echo $venta['cantidad']; ?></td>
                    <td><?php echo $venta['precio_unitario']; ?></td>
                    <td><?php echo $venta['cantidad'] * $venta['precio_unitario']; ?></td>
                    <td><?php echo $venta['fecha']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
