<?php
session_start();
require 'conexion.php'; 

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Verificar si se pasó el ID del producto
if (!isset($_GET['id'])) {
    header("Location: admin_productos.php");
    exit();
}

$id = $_GET['id'];

// Eliminar el producto
$query = $conn->prepare("DELETE FROM productos WHERE id = ?");
$query->execute([$id]);

header("Location: admin_productos.php");
exit();
?>
