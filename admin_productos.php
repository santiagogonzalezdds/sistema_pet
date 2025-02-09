<?php
session_start();
require 'conexion.php';

// Verificar si el usuario es admin
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Función para obtener productos
function obtenerProductos($conn, $buscar = '') {
    if (!empty($buscar)) {
        $query = $conn->prepare("
            SELECT * 
            FROM productos
            WHERE nombre LIKE ? OR descripcion LIKE ?
        ");
        $query->execute(['%' . $buscar . '%', '%' . $buscar . '%']);
    } else {
        $query = $conn->prepare("SELECT * FROM productos");
        $query->execute();
    }
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

// Solicitud AJAX
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    $buscar = isset($_GET['q']) ? $_GET['q'] : '';
    $productos = obtenerProductos($conn, $buscar);

    if (empty($productos)) {
        echo '<div class="alert alert-warning mt-3">No se encontraron productos.</div>';
    } else {
        echo '<table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Precio Compra</th>
                        <th>Precio Venta</th>
                        <th>Cantidad</th>
                        <th>Fecha Agregado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>';
        foreach ($productos as $producto) {
            echo "<tr>
                    <td>{$producto['id']}</td>
                    <td>" . htmlspecialchars($producto['nombre']) . "</td>
                    <td class='text-truncate' style='max-width: 150px;' title='" . htmlspecialchars($producto['descripcion']) . "'>
                        " . htmlspecialchars($producto['descripcion']) . "
                    </td>
                    <td>$" . number_format($producto['precio_compra'], 2) . "</td>
                    <td>$" . number_format($producto['precio_venta'], 2) . "</td>
                    <td>{$producto['cantidad']}</td>
                    <td>{$producto['fecha_agregado']}</td>
                    <td>
                        <a href='editar_producto.php?id={$producto['id']}' class='btn btn-sm btn-primary me-2'>Editar</a>
                        <a href='eliminar_producto.php?id={$producto['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"¿Estás seguro?\");'>Eliminar</a>
                    </td>
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
    <title>Gestionar Productos</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="admin_dashboard.php">Administrador</a>
    </div>
</nav>

<!-- Contenido Principal -->
<div class="container mt-4 p-4 bg-white shadow-lg rounded">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Gestionar Productos</h1>
        <a href="agregar_producto.php" class="btn btn-success">Agregar Producto</a>
    </div>

    <!-- Formulario de búsqueda -->
    <div class="mb-5">
        <h5 class="mb-3">Buscar producto:</h5>
        <div class="input-group">
            <input type="text" class="form-control" id="buscar" placeholder="Ingrese nombre o descripción">
        </div>
    </div>

    <!-- Contenedor de resultados -->
    <div id="resultados">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Precio Compra</th>
                    <th>Precio Venta</th>
                    <th>Cantidad</th>
                    <th>Fecha Agregado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><?php echo $producto['id']; ?></td>
                        <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                        <td class="text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($producto['descripcion']); ?>">
                            <?php echo htmlspecialchars($producto['descripcion']); ?>
                        </td>
                        <td>$<?php echo number_format($producto['precio_compra'], 2); ?></td>
                        <td>$<?php echo number_format($producto['precio_venta'], 2); ?></td>
                        <td><?php echo htmlspecialchars($producto['cantidad']); ?></td>
                        <td><?php echo htmlspecialchars($producto['fecha_agregado']); ?></td>
                        <td>
                            <a href="editar_producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-sm btn-primary me-2">Editar</a>
                            <a href="eliminar_producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById('buscar');
        const resultsContainer = document.getElementById('resultados');

        searchInput.addEventListener('input', function () {
            const query = searchInput.value;

            fetch('admin_productos.php?ajax=1&q=' + encodeURIComponent(query))
                .then(response => response.text())
                .then(html => {
                    resultsContainer.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    resultsContainer.innerHTML = '<div class="alert alert-danger mt-3">Error al cargar los resultados.</div>';
                });
        });
    });
</script>

</body>
</html>
