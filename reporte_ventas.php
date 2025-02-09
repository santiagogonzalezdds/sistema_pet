<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit();
}

$directorio_base = 'reportes/';
$meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

$mes_filtro = isset($_GET['mes']) ? $_GET['mes'] : '';
$ano_filtro = isset($_GET['ano']) ? $_GET['ano'] : '';

$archivos_filtrados = [];
foreach ($meses as $indice => $mes_nombre) {
    $indice_mes = str_pad($indice + 1, 2, '0', STR_PAD_LEFT);
    $carpeta_mes = "$directorio_base$mes_nombre";

    if (file_exists($carpeta_mes)) {
        $archivos = array_diff(scandir($carpeta_mes), ['.', '..']);
        foreach ($archivos as $archivo) {
            $ano_archivo = substr($archivo, 8, 4);
            $mes_archivo = $indice_mes;
            $dia_archivo = substr($archivo, 13, 2);

            if (
                (!$mes_filtro || $mes_archivo === $mes_filtro) &&
                (!$ano_filtro || $ano_archivo === $ano_filtro)
            ) {
                $archivos_filtrados[] = [
                    'ruta' => "$carpeta_mes/$archivo",
                    'nombre' => $archivo,
                    'fecha_completa' => "$dia_archivo $mes_nombre $ano_archivo",
                    'mes' => $mes_nombre,
                    'ano' => $ano_archivo
                ];
            }
        }
    }
}

usort($archivos_filtrados, function ($a, $b) {
    return strcmp($b['nombre'], $a['nombre']);
});
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes de Ventas</title>
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
        <a class="navbar-brand" href="admin_dashboard.php">Reportes de Ventas</a>
    </div>
</nav>

<!-- Contenido Principal -->
<div class="container bg-white shadow-lg rounded p-4">
    <h2 class="mb-4">Lista de Reportes</h2>

    <!-- Formulario de Filtros -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-5">
            <label for="mes" class="form-label">Mes</label>
            <select name="mes" id="mes" class="form-select">
                <option value="">Todos</option>
                <?php foreach ($meses as $indice => $mes_nombre): ?>
                    <option value="<?php echo str_pad($indice + 1, 2, '0', STR_PAD_LEFT); ?>" 
                        <?php echo ($mes_filtro === str_pad($indice + 1, 2, '0', STR_PAD_LEFT)) ? 'selected' : ''; ?>>
                        <?php echo ucfirst($mes_nombre); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-5">
            <label for="ano" class="form-label">Año</label>
            <select name="ano" id="ano" class="form-select">
                <option value="">Todos</option>
                <?php
                $ano_actual = date('Y');
                for ($ano = $ano_actual; $ano >= $ano_actual - 10; $ano--): ?>
                    <option value="<?php echo $ano; ?>" <?php echo ($ano_filtro == $ano) ? 'selected' : ''; ?>>
                        <?php echo $ano; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>

    <!-- Tabla de Reportes -->
    <?php if (empty($archivos_filtrados)): ?>
        <div class="alert alert-warning text-center">No se encontraron reportes para los filtros seleccionados.</div>
    <?php else: ?>
        <table class="table table-striped table-hover table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Fecha Completa</th>
                    <th>Nombre del Reporte</th>
                    <th>Mes</th>
                    <th>Año</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($archivos_filtrados as $archivo): ?>
                    <tr>
                        <td><?php echo $archivo['fecha_completa']; ?></td>
                        <td><?php echo htmlspecialchars($archivo['nombre']); ?></td>
                        <td><?php echo ucfirst($archivo['mes']); ?></td>
                        <td><?php echo $archivo['ano']; ?></td>
                        <td class="d-flex gap-2">
                            <a href="<?php echo $archivo['ruta']; ?>" class="btn btn-success btn-sm" download>Descargar</a>
                            <form method="POST" action="eliminar_reporte.php" style="display:inline;">
                                <input type="hidden" name="archivo" value="<?php echo htmlspecialchars($archivo['ruta']); ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este reporte?');">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
