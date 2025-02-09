<?php
session_start();
require 'conexion.php';

// Verificar si el usuario está autenticado como admin
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Limpieza automática al inicio del día
$fecha_actual = date('Y-m-d');
$queryLimpieza = $conn->prepare("UPDATE gastos SET visible = 0 WHERE visible = 1 AND DATE(fecha) < ?");
$queryLimpieza->execute([$fecha_actual]);

// Procesar el formulario de agregar un gasto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_gasto'])) {
    $descripcion = $_POST['descripcion'];
    $monto = $_POST['monto'];
    $fecha = $_POST['fecha'];
    $tipo = $_POST['tipo'];
    $usuario_id = $_SESSION['id']; 

    try {
        $query = $conn->prepare("INSERT INTO gastos (descripcion, monto, fecha, tipo, usuario_id, visible) VALUES (?, ?, ?, ?, ?, 1)");
        $query->execute([$descripcion, $monto, $fecha, $tipo, $usuario_id]);

        echo "<div class='alert alert-success mt-3'>Gasto agregado exitosamente.</div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger mt-3'>Error al agregar el gasto: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Obtener los gastos según los filtros de fecha o mes
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';
$mes = isset($_GET['mes']) ? $_GET['mes'] : '';

if (!empty($fecha)) {
    // Filtrar por fecha exacta
    $query = $conn->prepare("SELECT * FROM gastos WHERE DATE(fecha) = ? ORDER BY fecha DESC");
    $query->execute([$fecha]);
} elseif (!empty($mes)) {
    // Filtrar por mes
    $query = $conn->prepare("SELECT * FROM gastos WHERE MONTH(fecha) = ? ORDER BY fecha DESC");
    $query->execute([$mes]);
} else {
    // Mostrar los registros visibles del día actual
    $query = $conn->prepare("SELECT * FROM gastos WHERE visible = 1 ORDER BY fecha DESC");
    $query->execute();
}

$gastos = $query->fetchAll(PDO::FETCH_ASSOC);

// Calcular el total de gastos
$total_gastos = 0;
foreach ($gastos as $gasto) {
    $total_gastos += $gasto['monto'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Gastos</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS Personalizado -->
    <style>
        body {
            background: linear-gradient(to right, #b8b8b8 0%, #ffffff 20%, #ffffff 80%, #b8b8b8 100%);
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="admin_dashboard.php" style="color:black";>Inicio</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4 text-center">Gestión de Gastos</h1>

        <!-- Formulario para filtrar por fecha o mes -->
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <label for="fecha" class="form-label">Filtrar por Día</label>
                    <input type="date" name="fecha" id="fecha" class="form-control" value="<?php echo htmlspecialchars($fecha); ?>">
                </div>
                <div class="col-md-4">
                    <label for="mes" class="form-label">Filtrar por Mes</label>
                    <select name="mes" id="mes" class="form-select">
                        <option value="" selected>Seleccione un mes</option>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo ($mes == $i) ? 'selected' : ''; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </div>
        </form>

        <!-- Formulario para agregar un gasto -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Agregar Gasto</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <input type="text" name="descripcion" id="descripcion" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="monto" class="form-label">Monto</label>
                        <input type="number" step="0.01" name="monto" id="monto" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo de Gasto</label>
                        <select name="tipo" id="tipo" class="form-select" required>
                            <option value="" disabled selected>Seleccione el tipo</option>
                            <option value="Mercadería">Mercadería</option>
                            <option value="Servicios">Servicios</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="fecha" class="form-label">Fecha</label>
                        <input type="date" name="fecha" id="fecha" class="form-control" required>
                    </div>
                    <button type="submit" name="agregar_gasto" class="btn btn-primary">Agregar Gasto</button>
                </form>
            </div>
        </div>

        <!-- Tabla de gastos -->
        <h2 class="mb-4">
            Historial de Gastos
            <?php if (!empty($fecha)): ?>
                (Mostrando gastos del día <?php echo htmlspecialchars($fecha); ?>)
            <?php elseif (!empty($mes)): ?>
                (Mostrando gastos del mes <?php echo date('F', mktime(0, 0, 0, $mes, 1)); ?>)
            <?php else: ?>
                (Mostrando gastos de hoy)
            <?php endif; ?>
        </h2>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>Monto</th>
                        <th>Tipo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gastos as $gasto): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($gasto['fecha']); ?></td>
                            <td><?php echo htmlspecialchars($gasto['descripcion']); ?></td>
                            <td>$<?php echo number_format($gasto['monto'], 2); ?></td>
                            <td><?php echo htmlspecialchars($gasto['tipo']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="text-end fw-bold">Total:</td>
                        <td colspan="2" class="fw-bold">$<?php echo number_format($total_gastos, 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
