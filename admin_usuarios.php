<?php
session_start();
require 'conexion.php';

// Verificar si el usuario es admin
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Obtener todos los usuarios
$query = $conn->prepare("SELECT * FROM usuarios");
$query->execute();
$usuarios = $query->fetchAll(PDO::FETCH_ASSOC);

// Manejar eliminación de usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
    $id = $_POST['id'];
    if ($_SESSION['id'] == $id) {
        $mensaje = "No puedes eliminar tu propio usuario.";
    } else {
        $query = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $query->execute([$id]);
        header("Location: admin_usuarios.php");
        exit();
    }
}

// Manejar la creación de un usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $usuario = $_POST['usuario'];
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_BCRYPT);
    $rol = $_POST['rol'];

    $query = $conn->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $query->execute([$usuario]);
    if ($query->rowCount() > 0) {
        $mensaje = "El usuario ya existe.";
    } else {
        $query = $conn->prepare("INSERT INTO usuarios (usuario, contrasena, rol) VALUES (?, ?, ?)");
        $query->execute([$usuario, $contrasena, $rol]);
        header("Location: admin_usuarios.php");
        exit();
    }
}

// Manejar la edición de un usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar'])) {
    $id = $_POST['id'];
    $usuario = $_POST['usuario'];
    $rol = $_POST['rol'];
    $nueva_contrasena = $_POST['nueva_contrasena'];

    $queryStr = "UPDATE usuarios SET usuario = ?, rol = ?";
    $params = [$usuario, $rol];
    if (!empty($nueva_contrasena)) {
        $queryStr .= ", contrasena = ?";
        $params[] = password_hash($nueva_contrasena, PASSWORD_BCRYPT);
    }
    $queryStr .= " WHERE id = ?";
    $params[] = $id;

    $query = $conn->prepare($queryStr);
    $query->execute($params);
    header("Location: admin_usuarios.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #b8b8b8 0%, #ffffff 20%, #ffffff 80%, #b8b8b8 100%);
        }
    </style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="admin_dashboard.php">Gestión de Usuarios</a>
    </div>
</nav>

<!-- Contenedor Principal -->
<div class="container bg-white shadow-lg rounded p-4">
    <h2 class="text-center mb-4">Gestión de Usuarios</h2>

    <!-- Mensaje de alerta -->
    <?php if (isset($mensaje)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <!-- Tabla de Usuarios -->
    <h4>Usuarios Existentes</h4>
    <table class="table table-striped table-hover table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?php echo $usuario['id']; ?></td>
                    <td><?php echo htmlspecialchars($usuario['usuario']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['rol'] ?: 'Empleado'); ?></td>
                    <td>
                        <!-- Botón Eliminar -->
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                            <button type="submit" name="eliminar" class="btn btn-danger btn-sm"
                                onclick="return confirm('¿Estás seguro de eliminar este usuario?');">Eliminar</button>
                        </form>
                        <!-- Botón Editar -->
                        <button class="btn btn-primary btn-sm" onclick="mostrarFormulario(<?php echo $usuario['id']; ?>, '<?php echo $usuario['usuario']; ?>', '<?php echo $usuario['rol']; ?>')">
                            Editar
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Formulario Agregar Usuario -->
    <h4 class="mt-4">Agregar Usuario</h4>
    <form method="POST">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Usuario</label>
                <input type="text" name="usuario" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="contrasena" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Rol</label>
                <select name="rol" class="form-select" required>
                    <option value="admin">Admin</option>
                    <option value="empleado1">Empleado</option>
                </select>
            </div>
        </div>
        <button type="submit" name="agregar" class="btn btn-success">Agregar Usuario</button>
    </form>

    <!-- Formulario Editar Usuario -->
    <div id="formulario-editar" style="display:none;">
        <h4 class="mt-4">Editar Usuario</h4>
        <form method="POST">
            <input type="hidden" name="id" id="editar-id">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="usuario" id="editar-usuario" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nueva Contraseña (opcional)</label>
                    <input type="password" name="nueva_contrasena" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Rol</label>
                    <select name="rol" id="editar-rol" class="form-select" required>
                        <option value="admin">Admin</option>
                        <option value="empleado1">Empleado</option>
                    </select>
                </div>
            </div>
            <button type="submit" name="editar" class="btn btn-primary">Guardar Cambios</button>
            <button type="button" class="btn btn-secondary" onclick="ocultarFormulario()">Cancelar</button>
        </form>
    </div>
</div>

<script>
    function mostrarFormulario(id, usuario, rol) {
        document.getElementById('editar-id').value = id;
        document.getElementById('editar-usuario').value = usuario;
        document.getElementById('editar-rol').value = rol;
        document.getElementById('formulario-editar').style.display = 'block';
    }

    function ocultarFormulario() {
        document.getElementById('formulario-editar').style.display = 'none';
    }
</script>

</body>
</html>
