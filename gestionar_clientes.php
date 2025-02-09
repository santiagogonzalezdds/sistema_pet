<?php
session_start();
require 'conexion.php'; 

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Consultar todos los clientes
$query_clientes = $conn->prepare("SELECT * FROM clientes");
$query_clientes->execute();
$clientes = $query_clientes->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Clientes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Clientes Registrados</h2>
    <table border="1">
        <tr>
            <th>Nombre</th>
            <th>Email</th>
            <th>Acciones</th>
        </tr>
        <?php foreach ($clientes as $cliente): ?>
        <tr>
            <td><?php echo $cliente['nombre']; ?></td>
            <td><?php echo $cliente['email']; ?></td>
            <td>
                <a href="editar_cliente.php?id=<?php echo $cliente['id']; ?>">Editar</a>
                <a href="eliminar_cliente.php?id=<?php echo $cliente['id']; ?>">Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
