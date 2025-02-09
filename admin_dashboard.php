<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'conexion.php';

// Verificar si el usuario está autenticado como admin
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Obtener el conteo de ventas realizadas hoy
$queryVentasHoy = $conn->prepare("SELECT COUNT(*) AS total_ventas FROM ventas WHERE DATE(fecha) = CURDATE()");
$queryVentasHoy->execute();
$ventasHoy = $queryVentasHoy->fetch(PDO::FETCH_ASSOC)['total_ventas'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Administrador</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="#">Administrador</a>
        <div class="ms-auto">
            <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
        </div>
    </div>
</nav>

<!-- Contenido Principal -->
<div class="container mt-5">
    <!-- Título y Conteo de Ventas -->
    <div class="section-bg shadow-sm mb-5">
        <h1 class="display-6 text-center mb-4">Bienvenido, Administrador</h1>
        <p class="text-center text-muted mb-4">Seleccione una de las opciones disponibles para continuar.</p>
        <p class="text-center text-success fw-bold">Ventas realizadas hoy: <?php echo $ventasHoy; ?></p>
    </div>

    <!-- Tarjetas de Opciones -->
    <div class="row justify-content-center">
        <!-- Gestionar Productos -->
        <div class="col-md-4 mb-4">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Gestionar Productos</h5>
                    <p class="card-text">Añade, edita o elimina productos.</p>
                    <a href="admin_productos.php" class="btn btn-primary w-100">Ir a Productos</a>
                </div>
            </div>
        </div>

        <!-- Realizar Ventas -->
        <div class="col-md-4 mb-4">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Realizar Ventas</h5>
                    <p class="card-text">Registra nuevas operaciones de ventas.</p>
                    <a href="realizar_venta.php" class="btn btn-success w-100">Ir a Ventas</a>
                </div>
            </div>
        </div>

        <!-- Gestionar Ventas -->
        <div class="col-md-4 mb-4">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Gestionar Ventas</h5>
                    <p class="card-text">Revisa y administra las ventas realizadas.</p>
                    <a href="admin_ventas.php" class="btn btn-primary w-100">Ir a Gestionar Ventas</a>
                </div>
            </div>
        </div>

        <!-- Gestionar Stock -->
        <div class="col-md-4 mb-4">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Gestionar Stock</h5>
                    <p class="card-text">Actualiza y revisa el stock de productos.</p>
                    <a href="admin_stock.php" class="btn btn-success w-100">Ir a Stock</a>
                </div>
            </div>
        </div>

        <!-- Gestionar Usuarios -->
        <div class="col-md-4 mb-4">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Gestionar Usuarios</h5>
                    <p class="card-text">Administra los usuarios del sistema.</p>
                    <a href="admin_usuarios.php" class="btn btn-primary w-100">Ir a Usuarios</a>
                </div>
            </div>
        </div>

        <!-- Ver Reportes -->
        <div class="col-md-4 mb-4">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Ver Reportes</h5>
                    <p class="card-text">Accede a reportes de ventas y gestión.</p>
                    <a href="reporte_ventas.php" class="btn btn-info w-100">Ir a Reportes</a>
                </div>
            </div>
        </div>

        <!-- Gestionar Gastos -->
        <div class="col-md-4 mb-4">
            <div class="card text-center border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title mb-3">Gestionar Gastos</h5>
                    <p class="card-text">Registra y revisa los gastos del negocio.</p>
                    <a href="gastos.php" class="btn btn-warning w-100">Ir a Gastos</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
