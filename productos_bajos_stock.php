<?php
session_start();
require 'conexion.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Consultar productos con stock bajo
$query_bajos_stock = $conn->prepare("SELECT * FROM productos WHERE stock < 5");
$query_bajos_stock->execute();
$productos_bajos_stock = $query_bajos_stock->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos Bajos en Stock</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Productos Bajos en Stock</h2>
    <table border="1">
        <tr>
            <th>Producto</th>
            <th>Stock Actual</th>
        </tr>
        <?php foreach ($productos_bajos_stock as $producto): ?>
        <tr>
            <td><?php echo $producto['nombre']; ?></td>
            <td><?php echo $producto['stock']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
