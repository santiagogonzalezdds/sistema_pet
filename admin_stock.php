<?php
session_start();
require 'conexion.php';

// Verificar si el usuario tiene rol "empleado" o "admin"
if (!isset($_SESSION['usuario']) || !in_array($_SESSION['rol'], ['empleado', 'admin'])) {
    header('Location: login.php');
    exit;
}

// Verificar si hay productos con stock bajo
$query = $conn->prepare("SELECT COUNT(*) AS total_bajo_stock FROM productos WHERE cantidad <=5");
$query->execute();
$resultado = $query->fetch(PDO::FETCH_ASSOC);
$hay_stock_bajo = $resultado['total_bajo_stock'] > 0; // Variable booleana


// Función para obtener productos (filtrados o todos)
function obtenerProductos($conn, $buscar = '') {
    if (!empty($buscar)) {
        $query = $conn->prepare("
            SELECT id, nombre, descripcion, cantidad, precio_venta 
            FROM productos
            WHERE nombre LIKE ? OR descripcion LIKE ?
        ");
        $query->execute(['%' . $buscar . '%', '%' . $buscar . '%']);
    } else {
        $query = $conn->prepare("SELECT id, nombre, descripcion, cantidad, precio_venta FROM productos");
        $query->execute();
    }
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

// Solicitud AJAX, devolver los datos filtrados
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    $buscar = isset($_GET['q']) ? $_GET['q'] : '';
    $productos = obtenerProductos($conn, $buscar);

    if (empty($productos)) {
        echo '<div class="alert alert-warning">No se encontraron productos que coincidan con la búsqueda.</div>';
    } else {
        echo '<table class="table table-striped table-hover table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Descripción</th>
                    <th>Cantidad en Stock</th>
                    <th>Precio de Venta</th>
                </tr>
            </thead>
            <tbody>';
        foreach ($productos as $producto) {
            $clase_stock_bajo = $producto['cantidad'] <= 5 ? 'table-danger' : '';
            echo "<tr class='$clase_stock_bajo'>
                <td>{$producto['id']}</td>
                <td>" . htmlspecialchars($producto['nombre']) . "</td>
                <td class='text-truncate' style='max-width: 200px;' title='" . htmlspecialchars($producto['descripcion']) . "'>
                    " . htmlspecialchars($producto['descripcion']) . "
                </td>
                <td>" . htmlspecialchars($producto['cantidad']) . "</td>
                <td>$" . htmlspecialchars(number_format($producto['precio_venta'], 2)) . "</td>
            </tr>";
        }
        echo '</tbody></table>';
    }
    exit;
}

$productos = obtenerProductos($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Stock</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS Personalizado -->
    <style>
        body {
            background: linear-gradient(to right, #b8b8b8 0%, #ffffff 20%, #ffffff 80%, #b8b8b8 100%);
            margin: 0;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?php echo ($_SESSION['rol'] == 'admin') ? 'admin_dashboard.php' : 'empleado_dashboard.php'; ?>">
            Sistema de Gestión de Stock
        </a>
    </div>
</nav>

<!-- Contenido Principal -->
<div class="container bg-white shadow-lg rounded p-4">
    <h2 class="mb-4 text-center">Gestión de Stock</h2>

    <!-- Alerta si hay productos con stock bajo -->
    <?php if ($hay_stock_bajo): ?>
    <div class="alert alert-danger mb-4" role="alert">
        Alerta: Hay productos con stock bajo. Revísalos a continuación.
    </div>
    <?php endif; ?>


    <!-- Formulario de búsqueda -->
    <div class="mb-4">
        <h5 class="mb-3">Buscar producto:</h5>
        <div class="input-group">
            <input type="text" class="form-control" id="buscar" placeholder="Ingrese nombre o descripción del producto">
        </div>
    </div>

    <!-- Contenedor de resultados -->
    <div id="resultados">
        <!-- Tabla inicial cargada en PHP -->
        <table class="table table-striped table-hover table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Descripción</th>
                    <th>Cantidad en Stock</th>
                    <th>Precio de Venta</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto): ?>
                    <tr class="<?php echo $producto['cantidad'] < 5 ? 'table-danger' : ''; ?>">
                        <td><?php echo $producto['id']; ?></td>
                        <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                        <td class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($producto['descripcion']); ?>">
                            <?php echo htmlspecialchars($producto['descripcion']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($producto['cantidad']); ?></td>
                        <td>$<?php echo htmlspecialchars(number_format($producto['precio_venta'], 2)); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Script para Búsqueda Dinámica -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById('buscar');
        const resultsContainer = document.getElementById('resultados');

        searchInput.addEventListener('input', function () {
            const query = searchInput.value;

            fetch('admin_stock.php?ajax=1&q=' + encodeURIComponent(query))
                .then(response => response.text())
                .then(html => {
                    resultsContainer.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error al realizar la búsqueda:', error);
                    resultsContainer.innerHTML = '<div class="alert alert-danger mt-3">Error al cargar los resultados.</div>';
                });
        });
    });
</script>

</body>
</html>
