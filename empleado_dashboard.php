<?php
session_start();
require 'conexion.php';

// Verificar si el usuario está autenticado como empleado
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'empleado') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Empleado</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container d-flex justify-content-between">
        <span class="navbar-brand">Dashboard - Empleado</span>
        <a href="logout.php" class="btn btn-danger btn-sm">Cerrar Sesión</a>
    </div>
</nav>

<!-- Contenido Principal -->
<div class="container my-5 section-bg shadow-sm">
    <h1 class="text-center display-6 mb-4">Bienvenido, Empleado</h1>
    <p class="text-center text-muted mb-5">Seleccione una de las opciones disponibles para continuar.</p>
</div>


    <div class="row justify-content-center">
        <!-- Card Realizar Ventas -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-cart-check-fill text-success fs-1 mb-3"></i>
                    <h5 class="card-title">Realizar Ventas</h5>
                    <p class="card-text text-muted">Gestiona las ventas y registra nuevas operaciones.</p>
                    <a href="realizar_venta.php" class="btn btn-success w-100">Ir a Ventas</a>
                </div>
            </div>
        </div>
        
        <!-- Card Gestionar Stock -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <i class="bi bi-box-seam-fill text-warning fs-1 mb-3"></i>
                    <h5 class="card-title">Gestionar Stock</h5>
                    <p class="card-text text-muted">Revisa y actualiza el stock de productos disponibles.</p>
                    <a href="admin_stock.php" class="btn btn-warning w-100">Ir a Stock</a>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Footer -->
<footer class="bg-dark text-white text-center py-3 mt-5">
    <div class="container">
        <span>&copy; 2024 Sistema de Gestión - Empleados</span>
    </div>
</footer>

</body>
</html>
