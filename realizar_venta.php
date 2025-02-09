<?php
session_start();
require 'conexion.php';

// Verificar si el usuario tiene rol "empleado" o "admin"
if (!isset($_SESSION['usuario']) || !in_array($_SESSION['rol'], ['empleado', 'admin'])) {
    header('Location: login.php');
    exit;
}

// Actualizar stock manualmente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_stock'])) {
    try {
        foreach ($_SESSION['carrito'] as $item) {
            // Actualizar stock del producto
            $stockQuery = $conn->prepare("UPDATE productos SET cantidad = cantidad - ? WHERE id = ?");
            $stockQuery->execute([$item['cantidad'], $item['producto_id']]);

            // Verificar si el stock quedó en negativo
            $stockVerificationQuery = $conn->prepare("SELECT cantidad FROM productos WHERE id = ?");
            $stockVerificationQuery->execute([$item['producto_id']]);
            $stockActual = $stockVerificationQuery->fetchColumn();

            if ($stockActual < 0) {
                throw new Exception("Stock insuficiente para el producto: " . $item['nombre']);
            }
        }

        echo "<div class='alert alert-success mt-3'>El stock se ha actualizado correctamente.</div>";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger mt-3'>Error al actualizar el stock: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Inicializar carrito si no está definido
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Procesar la búsqueda de productos
$buscar = isset($_GET['buscar']) ? $_GET['buscar'] : '';
$query = $conn->prepare("SELECT * FROM productos WHERE nombre LIKE ? OR descripcion LIKE ?");
$query->execute(['%' . $buscar . '%', '%' . $buscar . '%']);
$productos = $query->fetchAll(PDO::FETCH_ASSOC);

// Agregar producto al carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar'])) {
    $productoId = $_POST['producto_id'];

    // Verificar stock disponible
    $queryProducto = $conn->prepare("SELECT id, nombre, descripcion, precio_venta, precio_compra, cantidad FROM productos WHERE id = ?");
    $queryProducto->execute([$productoId]);
    $producto = $queryProducto->fetch(PDO::FETCH_ASSOC);

    if ($producto) {
        if ($producto['cantidad'] <= 0) {
            echo "<div class='alert alert-danger mt-3'>Stock insuficiente para el producto: " . htmlspecialchars($producto['nombre']) . "</div>";
        } else {
            // Verificar si el producto ya está en el carrito
            $index = array_search($productoId, array_column($_SESSION['carrito'], 'producto_id'));

            if ($index !== false) {
                // Incrementar la cantidad del producto existente en el carrito
                if ($_SESSION['carrito'][$index]['cantidad'] < $producto['cantidad']) {
                    $_SESSION['carrito'][$index]['cantidad'] += 1;
                } else {
                    echo "<div class='alert alert-warning mt-3'>No hay suficiente stock para agregar más de este producto.</div>";
                }
            } else {
                // Agregar el producto al carrito como un nuevo elemento
                $_SESSION['carrito'][] = [
                    'producto_id' => $producto['id'],
                    'nombre' => $producto['nombre'],
                    'descripcion' => $producto['descripcion'],
                    'precio_venta' => $producto['precio_venta'],
                    'precio_compra' => $producto['precio_compra'],
                    'cantidad' => 1,
                ];
            }
        }
    }
}

// Eliminar producto del carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
    $productoId = $_POST['producto_id'];
    foreach ($_SESSION['carrito'] as $key => $item) {
        if ($item['producto_id'] == $productoId) {
            unset($_SESSION['carrito'][$key]);
            break;
        }
    }
    $_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reindexar el array
}

// Vaciar carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vaciar_carrito'])) {
    $_SESSION['carrito'] = [];
}

// Finalizar compra
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar'])) {
    $metodos_pago = $_POST['metodo_pago']; 
    $montos_pago = $_POST['monto_pago'];   
    $totalVenta = 0; 

    // Validar que la suma de los montos coincida con el total
    $sumaMontos = array_sum($montos_pago);
    if ($sumaMontos != $totalVenta) {
        echo "<div class='alert alert-danger mt-3'>La suma de los montos ($" . number_format($sumaMontos, 2) . ") no coincide con el total de la compra ($" . number_format($totalVenta, 2) . ").</div>";
        exit();
    }

    try {
        $conn->beginTransaction();

        // Registrar la venta
        $ventaQuery = $conn->prepare(
            "INSERT INTO ventas (fecha, cliente_id, total_venta, ganancia_total) VALUES (NOW(), ?, ?, 0)"
        );
        $clienteGenericoId = 1;
        $ventaQuery->execute([$clienteGenericoId, $totalVenta]);
        $ventaId = $conn->lastInsertId();

        // Registrar los métodos de pago
        foreach ($metodos_pago as $index => $metodo) {
            $monto = $montos_pago[$index];
            $pagoQuery = $conn->prepare(
                "INSERT INTO pagos (venta_id, metodo_pago, monto) VALUES (?, ?, ?)"
            );
            $pagoQuery->execute([$ventaId, $metodo, $monto]);
        }

        // Resto del proceso (actualizar stock, insertar detalles de venta)
        $conn->commit();

        echo "<div class='alert alert-success mt-3'>Compra finalizada exitosamente. Total: $" . number_format($totalVenta, 2) . "</div>";
        $_SESSION['carrito'] = [];
    } catch (Exception $e) {
        $conn->rollBack();
        echo "<div class='alert alert-danger mt-3'>Error al finalizar la compra: " . $e->getMessage() . "</div>";
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Realizar Venta</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="container mt-4 p-4 bg-white shadow-lg rounded">
    <h1>Realizar Venta</h1>
    <nav class="navbar navbar-dark bg-dark mb-4"> 
    <div class="container">
        <a class="navbar-brand" href="<?php echo ($_SESSION['rol'] == 'admin') ? 'admin_dashboard.php' : 'empleado_dashboard.php'; ?>">
            Volver al Dashboard
        </a>
    </div>
</nav>

    <br><br>
    
    <!-- Formulario de búsqueda -->
    <h5 class="mb-3">Buscar producto:</h5>
<div class="input-group mb-4">
    <input type="text" id="buscar" class="form-control" placeholder="Buscar por nombre o descripción" onkeyup="buscarProductos()">
</div>



    <script>
        // Filtrado dinámico del buscador
        function buscarProductos() {
            let input = document.getElementById("buscar").value.toLowerCase();
            let rows = document.querySelectorAll("#tablaProductos tr");

            rows.forEach(row => {
                let nombre = row.getAttribute("data-nombre").toLowerCase();
                let descripcion = row.getAttribute("data-descripcion").toLowerCase();

                // Mostrar u ocultar filas según el filtro
                if (nombre.includes(input) || descripcion.includes(input)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }
    </script>

    <!-- Lista de productos disponibles -->
    <h2>Productos Disponibles</h2>
    <table class="table table-striped table-hover">
    <thead class="table-dark">
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio de Venta</th>
                <th>Cantidad en Stock</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="tablaProductos">
            <?php foreach ($productos as $producto): ?>
            <tr data-nombre="<?php echo htmlspecialchars($producto['nombre']); ?>" data-descripcion="<?php echo htmlspecialchars($producto['descripcion']); ?>">
                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                <td>$<?php echo number_format($producto['precio_venta'], 2); ?></td>
                <td><?php echo htmlspecialchars($producto['cantidad']); ?></td>
                <td>
                    <form method="POST" action="">
                        <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                        <button type="submit" name="agregar" class="btn btn-success btn-sm">Agregar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- Botón Actualizar Stock -->
    <form method="POST" action="">
    <button type="submit" name="actualizar_stock" class="btn btn-info mt-3">Actualizar Stock</button>
    </form>

    <!-- Carrito de compras -->
    <h2>Carrito de Compras</h2>
    <?php if (empty($_SESSION['carrito'])): ?>
        <p>El carrito está vacío.</p>
    <?php else: ?>
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Producto</th>
                    <th>Descripción</th>
                    <th>Precio Unitario</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total = 0;
                foreach ($_SESSION['carrito'] as $item): 
                    $subtotal = $item['cantidad'] * $item['precio_venta'];
                    $total += $subtotal;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($item['descripcion']); ?></td>
                    <td>$<?php echo number_format($item['precio_venta'], 2); ?></td>
                    <td><?php echo htmlspecialchars($item['cantidad']); ?></td>
                    <td>$<?php echo number_format($subtotal, 2); ?></td>
                    <td>
                        <form method="POST" action="">
                            <input type="hidden" name="producto_id" value="<?php echo $item['producto_id']; ?>">
                            <button type="submit" name="eliminar" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="4" style="text-align: right;"><strong>Total:</strong></td>
                    <td colspan="2">$<?php echo number_format($total, 2); ?></td>
                </tr>
            </tbody>
        </table>
        <form method="POST" action="">
    <!-- Sección de Métodos de Pago -->
    <div id="metodos-pago-container" class="mb-4">
        <h5>Métodos de Pago</h5>
        <div class="row mb-2 metodo-pago-item">
            <div class="col-md-6">
                <label for="metodo_pago_0" class="form-label">Método de Pago</label>
                <select class="form-select" name="metodo_pago[]" required>
                    <option value="" selected disabled>Seleccione un método de pago</option>
                    <option value="efectivo">Efectivo</option>
                    <option value="transferencia">Transferencia</option>
                    <option value="codigo_qr">Código QR</option>
                    <option value="debito">Débito</option>
                    <option value="credito">Crédito</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="monto_pago_0" class="form-label">Monto</label>
                <input type="number" step="0.01" class="form-control" name="monto_pago[]" placeholder="Ingrese el monto" required>
            </div>
        </div>
    </div>
    <!-- Botón para agregar más métodos de pago -->
    <button type="button" class="btn btn-info mb-3" onclick="agregarMetodoPago()">Agregar Método</button>

    <!-- Total a Pagar -->
    <div class="mb-3 text-end fw-bold">
        Total: $<span id="total_compra"><?php echo number_format($totalVenta, 2); ?></span>
    </div>

    <!-- Botón Finalizar Compra  y vaciar carrito-->
    <button type="submit" name="vaciar_carrito" class="btn btn-warning">Vaciar Carrito</button>
    <button type="submit" name="finalizar" class="btn btn-primary">Finalizar Compra</button>
</form>

<script>
    function agregarMetodoPago() {
        const container = document.getElementById('metodos-pago-container');
        const nuevoMetodo = document.createElement('div');
        nuevoMetodo.className = 'row mb-2 metodo-pago-item';
        nuevoMetodo.innerHTML = `
            <div class="col-md-6">
                <label class="form-label">Método de Pago</label>
                <select class="form-select" name="metodo_pago[]" required>
                    <option value="" selected disabled>Seleccione un método de pago</option>
                    <option value="efectivo">Efectivo</option>
                    <option value="transferencia">Transferencia</option>
                    <option value="codigo_qr">Código QR</option>
                    <option value="debito">Débito</option>
                    <option value="credito">Crédito</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Monto</label>
                <input type="number" step="0.01" class="form-control" name="monto_pago[]" placeholder="Ingrese el monto" required>
            </div>
        `;
        container.appendChild(nuevoMetodo);
    }
</script>

    <?php endif; ?>
    </div>

</body>
</html>


