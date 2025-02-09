<?php
session_start();
require 'conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    $query = $conn->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $query->execute([$usuario]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($contrasena, $user['contrasena'])) {
        $_SESSION['usuario'] = $user['usuario'];

        if (empty($user['rol'])) {
            $_SESSION['rol'] = 'empleado';
            header("Location: empleado_dashboard.php");
            exit;
        } elseif ($user['rol'] === 'admin') {
            $_SESSION['rol'] = 'admin';
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $error = "Rol desconocido.";
        }
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
<!-- Gradiente de Fondo -->
<body>
    <div class="bg-light" style="background: linear-gradient(to right, #b8b8b8 0%, #ffffff 20%, #ffffff 80%, #b8b8b8 100%); margin: 0; padding: 0;">
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Sistema de Gestión</a>
        </div>
    </nav>

    <!-- Login Form -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow-lg rounded">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Iniciar Sesión</h2>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="usuario" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Ingrese su usuario" required>
                            </div>
                            <div class="mb-3">
                                <label for="contrasena" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="contrasena" name="contrasena" placeholder="Ingrese su contraseña" required>
                            </div>
                            <button type="submit" class="btn btn-dark w-100">Ingresar</button>
                        </form>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger mt-3"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
