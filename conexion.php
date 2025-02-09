<?php
$host = 'localhost'; // Cambiar si usas un servidor remoto
$db = 'petshop';
$user = 'root'; // Cambiar si tienes un usuario diferente
$password = ''; // Cambiar si tienes una contraseña configurada

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $password); // Usar $conn en lugar de $conexion
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}
?>
