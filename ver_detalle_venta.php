<?php
session_start();
require 'conexion.php'; 

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener el ID de la venta
$venta_id = isset($_GET['id']) ? $_GET['id'] : '';

// Obtener los detalles de la venta
$query = $conn->prepare("SELECT v.*, c.nombre as cliente_nombre FROM ventas v JOIN clientes c ON v.cliente_id = c.id WHERE v.id = ?");
$query->execute([$venta_id]);
$venta = $query->fetch(PDO::FETCH_ASSOC);

// Obtener los productos vendidos en esta venta
$queryProductos = $conn->prepare("SELECT p.nombre, dv.cantidad, p.precio, (dv.cantidad * p.precio) AS total FROM detalle_ventas dv JOIN productos p ON dv.producto_id = p.id WHERE dv.venta_id = ?");
$queryProductos->execute([$venta_id]);
$productosVendidos = $queryProductos->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalles de Venta</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Detalles de la Venta</h2>

    <h3>Cliente: <?php echo $venta['cliente_nombre']; ?></h3>
    <p>Fecha: <?php echo $venta['fecha']; ?></p>
    <p>Total: $<?php echo number_format($venta['total'], 2); ?></p>
    <p>Estado: <?php echo $venta['estado']; ?></p>

    <h4>Productos Vendidos:</h4>
    <table border="1">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productosVendidos as $producto): ?>
                <tr>
                    <td><?php echo $producto['nombre']; ?></td>
                    <td><?php echo $producto['cantidad']; ?></td>
                    <td><?php echo number_format($producto['precio'], 2); ?></td>
                    <td><?php echo number_format($producto['total'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
