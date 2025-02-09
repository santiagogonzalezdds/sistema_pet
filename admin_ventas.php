<?php
session_start();
require 'conexion.php';
require 'fpdf/fpdf.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Manejar la generación del PDF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generar_pdf_limpiar'])) {
    $query = $conn->prepare("
        SELECT 
            DATE(v.fecha) AS fecha_venta, 
            TIME(v.fecha) AS hora_venta, 
            dv.nombre_producto, 
            dv.descripcion, 
            dv.cantidad, 
            dv.precio_compra, 
            dv.precio_venta, 
            (dv.precio_venta - dv.precio_compra) AS ganancia_unitaria,
            (dv.precio_venta - dv.precio_compra) * dv.cantidad AS ganancia_total
        FROM ventas v
        JOIN detalle_venta dv ON v.id = dv.venta_id
        WHERE v.visible = 1
        ORDER BY v.fecha DESC
    ");
    $query->execute();
    $ventas = $query->fetchAll(PDO::FETCH_ASSOC);

    if (empty($ventas)) {
        echo "<p>No hay ventas disponibles para generar el PDF.</p>";
    } else {
        // Crear el PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 12);

        $pdf->Cell(0, 10, 'Reporte de Ventas', 0, 1, 'C');
        $pdf->Ln(10);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(25, 10, 'Fecha', 1);
        $pdf->Cell(25, 10, 'Hora', 1);
        $pdf->Cell(40, 10, 'Producto', 1);
        $pdf->Cell(20, 10, 'Cantidad', 1);
        $pdf->Cell(25, 10, 'Costo Compra', 1);
        $pdf->Cell(25, 10, 'Costo Venta', 1);
        $pdf->Cell(30, 10, 'Ganancia', 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 10);
        $totalGanancia = 0;

        foreach ($ventas as $venta) {
            $pdf->Cell(25, 10, $venta['fecha_venta'], 1);
            $pdf->Cell(25, 10, $venta['hora_venta'], 1);
            $pdf->Cell(40, 10, substr($venta['nombre_producto'], 0, 20), 1);
            $pdf->Cell(20, 10, $venta['cantidad'], 1);
            $pdf->Cell(25, 10, '$' . number_format($venta['precio_compra'], 2), 1);
            $pdf->Cell(25, 10, '$' . number_format($venta['precio_venta'], 2), 1);
            $pdf->Cell(30, 10, '$' . number_format($venta['ganancia_total'], 2), 1);
            $pdf->Ln();
            $totalGanancia += $venta['ganancia_total'];
        }

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(160, 10, 'Ganancia Total: ', 1, 0, 'R');
        $pdf->Cell(30, 10, '$' . number_format($totalGanancia, 2), 1, 0, 'R');

        $mes_actual = date('m');
        $meses = ['01'=>'enero','02'=>'febrero','03'=>'marzo','04'=>'abril','05'=>'mayo','06'=>'junio','07'=>'julio','08'=>'agosto','09'=>'septiembre','10'=>'octubre','11'=>'noviembre','12'=>'diciembre'];
        $directorio_mes = "reportes/{$meses[$mes_actual]}";

        if (!file_exists($directorio_mes)) {
            mkdir($directorio_mes, 0777, true);
        }

        $nombreArchivo = "$directorio_mes/reporte_" . date('Y-m-d_H-i-s') . ".pdf";
        $pdf->Output('F', $nombreArchivo);

        echo "<div class='alert alert-success mt-3'>Reporte generado exitosamente. <a href='$nombreArchivo'>Descargar Reporte</a></div>";
    }
}

// Manejar el botón "Limpiar Tabla"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generar_pdf_limpiar'])) {
    $query = $conn->prepare("UPDATE ventas SET visible = 0 WHERE visible = 1");
    $query->execute();
}


// Obtener las ventas visibles
$query = $conn->prepare("
    SELECT 
        DATE(v.fecha) AS fecha_venta, 
        TIME(v.fecha) AS hora_venta, 
        dv.nombre_producto, 
        dv.descripcion, 
        dv.cantidad, 
        dv.precio_compra, 
        dv.precio_venta, 
        (dv.precio_venta - dv.precio_compra) * dv.cantidad AS ganancia_total
    FROM ventas v
    JOIN detalle_venta dv ON v.id = dv.venta_id
    WHERE v.visible = 1
    ORDER BY v.fecha DESC
");
$query->execute();
$ventas = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Ventas</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #b8b8b8 0%, #ffffff 20%, #ffffff 80%, #b8b8b8 100%);
            margin: 0;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="admin_dashboard.php">Sistema de Ventas</a>
    </div>
</nav>

<!-- Contenedor Principal -->
<div class="container bg-white shadow-lg rounded p-4">
    <h2 class="mb-4 text-center">Gestión de Ventas</h2>

    <!-- Botones de Acción -->
    <form method="POST">
    <button type="submit" name="generar_pdf_limpiar" class="btn btn-success me-2">Generar PDF y Limpiar Tabla</button>
    </form>


    <!-- Tabla -->
    <div class="table-responsive mt-4">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Producto</th>
                    <th>Descripción</th>
                    <th>Cantidad</th>
                    <th>Costo Compra</th>
                    <th>Costo Venta</th>
                    <th>Ganancia</th>
                </tr>
            </thead>
            <tbody>
                <?php $totalGanancia = 0; ?>
                <?php foreach ($ventas as $venta): ?>
                    <tr>
                        <td><?php echo $venta['fecha_venta']; ?></td>
                        <td><?php echo $venta['hora_venta']; ?></td>
                        <td><?php echo $venta['nombre_producto']; ?></td>
                        <td><?php echo $venta['descripcion']; ?></td>
                        <td><?php echo $venta['cantidad']; ?></td>
                        <td>$<?php echo number_format($venta['precio_compra'], 2); ?></td>
                        <td>$<?php echo number_format($venta['precio_venta'], 2); ?></td>
                        <td>$<?php echo number_format($venta['ganancia_total'], 2); ?></td>
                    </tr>
                    <?php $totalGanancia += $venta['ganancia_total']; ?>
                <?php endforeach; ?>
                <tr>
                    <td colspan="7" class="text-end fw-bold">Ganancia Total:</td>
                    <td class="fw-bold">$<?php echo number_format($totalGanancia, 2); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
