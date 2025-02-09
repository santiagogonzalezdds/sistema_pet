<?php
session_start();
require 'conexion.php'; 

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener los productos para poder filtrar
$query = $conn->prepare("SELECT * FROM productos");
$query->execute();
$productos = $query->fetchAll(PDO::FETCH_ASSOC);

// Filtrar por producto y fechas
$producto_id = isset($_GET['producto_id']) ? $_GET['producto_id'] : '';
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

// Construir la consulta para el historial de ventas de un producto
$queryStr = "SELECT dv.producto_id, p.nombre, SUM(dv.cantidad) as total_vendido FROM detalle_ventas dv JOIN productos p ON dv.producto_id = p.id";
$params = [];
$whereConditions = [];

if ($producto_id) {
    $whereConditions[] = "dv.producto_id = :producto_id";
    $params[':producto_id'] = $producto_id;
}
if ($fecha_inicio && $fecha_fin) {
    $whereConditions[] = "dv.fecha BETWEEN :fecha_inicio AND :fecha_fin";
    $params[':fecha_inicio'] = $fecha_inicio . ' 00:00:00';
    $params[':fecha_fin'] = $fecha_fin . ' 23:59:59';
}

if ($whereConditions) {
    $queryStr .= " WHERE " . implode(' AND ', $whereConditions);
}

$queryStr .= " GROUP BY dv.producto_id ORDER BY total_vendido DESC"; // Ordenar por cantidad vendida

$query = $conn->prepare($queryStr);
$query->execute($params);
$ventasPorProducto = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Ventas por Producto</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Historial de Ventas por Producto</h2>

    <form method="GET" action="historial_ventas_producto.php">
        <label for="producto_id">Producto:</label>
        <select name="producto_id" id="producto_id">
            <option value="">Todos</option>
            <?php foreach ($productos as $producto): ?>
                <option value="<?php echo $producto['id']; ?>" <?php if ($producto_id == $producto['id']) echo 'selected'; ?>><?php echo $producto['nombre']; ?></option>
            <?php endforeach; ?>
        </select>

        <label for="fecha_inicio">Fecha Inicio:</label>
        <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?php echo $fecha_inicio; ?>">

        <label for="fecha_fin">Fecha Fin:</label>
        <input type="date" name="fecha_fin" id="fecha_fin" value="<?php echo $fecha_fin; ?>">

        <button type="submit">Filtrar</button>
    </form>

    <table border="1">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad Vendida</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ventasPorProducto as $venta): ?>
                <tr>
                    <td><?php echo $venta['nombre']; ?></td>
                    <td><?php echo $venta['total_vendido']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
