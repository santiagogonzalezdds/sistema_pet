<?php
session_start();
require 'conexion.php';

// Verificar si el usuario es admin, de lo contrario redirigirlo al login
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio_compra = $_POST['precio_compra'];
    $precio_venta = $_POST['precio_venta'];
    $cantidad = $_POST['cantidad'];

    // Insertar el nuevo producto en la base de datos
    $query = $conn->prepare("INSERT INTO productos (nombre, descripcion, precio_compra, precio_venta, cantidad, fecha_agregado) VALUES (?, ?, ?, ?, ?, NOW())");
    $query->execute([$nombre, $descripcion, $precio_compra, $precio_venta, $cantidad]);

    // Redirigir a la página de productos
    header('Location: admin_productos.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Producto</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #b8b8b8 0%, #ffffff 20%, #ffffff 80%, #b8b8b8 100%);
            margin: 0;
            padding: 0;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="admin_productos.php">Sistema de Gestión de Productos</a>
    </div>
</nav>

<!-- Contenedor Principal -->
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card p-4">
                <h2 class="text-center mb-4">Agregar Producto</h2>
                <form method="POST" action="">
                    <!-- Nombre del Producto -->
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del Producto</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ingrese el nombre del producto" required>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Ingrese una descripción del producto" required></textarea>
                    </div>

                    <!-- Precio de Compra -->
                    <div class="mb-3">
                        <label for="precio_compra" class="form-label">Precio de Compra</label>
                        <input type="number" class="form-control" id="precio_compra" name="precio_compra" placeholder="0.00" step="0.01" required>
                    </div>

                    <!-- Precio de Venta -->
                    <div class="mb-3">
                        <label for="precio_venta" class="form-label">Precio de Venta</label>
                        <input type="number" class="form-control" id="precio_venta" name="precio_venta" placeholder="0.00" step="0.01" required>
                    </div>

                    <!-- Cantidad -->
                    <div class="mb-3">
                        <label for="cantidad" class="form-label">Cantidad</label>
                        <input type="number" class="form-control" id="cantidad" name="cantidad" placeholder="Ingrese la cantidad disponible" min="1" required>
                    </div>

                    <!-- Botones -->
                    <div class="d-flex justify-content-between">
                        <a href="admin_productos.php" class="btn btn-secondary">Volver</a>
                        <button type="submit" class="btn btn-success">Agregar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>