<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $archivo = isset($_POST['archivo']) ? $_POST['archivo'] : '';

    // Verificar que el archivo existe
    if (!empty($archivo) && file_exists($archivo)) {
        unlink($archivo); // Eliminar el archivo del servidor
        header("Location: reporte_ventas.php?msg=Reporte eliminado correctamente");
        exit();
    } else {
        header("Location: reporte_ventas.php?error=No se pudo eliminar el reporte. Archivo no encontrado.");
        exit();
    }
}
