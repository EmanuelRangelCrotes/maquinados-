<?php
require_once './db_conexion.php';
session_start();

$id_usuario = $_SESSION['id_usuario'];
$name = $_SESSION['name'];

$sql = "SELECT o.id_orden, o.fecha, o.estatus, 
               d.id_productos, d.cantidad,
               p.nombre AS nombre_producto, p.precio
        FROM ordenes o
        JOIN orden_detalles d ON o.id_orden = d.id_orden
        JOIN productos p ON d.id_productos = p.id_productos
        join users u ON u.id_usuario = u.id_usuario
        WHERE u.id_usuario = :id_usuario
        ORDER BY o.id_orden DESC";

$stmt = $cnnPDO->prepare($sql);
$stmt->bindParam(':id_usuario', $id_usuario);
$stmt->execute();
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ordenes_agrupadas = [];
foreach ($pedidos as $pedido) {
    $id_orden = $pedido['id_orden'];
    if (!isset($ordenes_agrupadas[$id_orden])) {
        $ordenes_agrupadas[$id_orden] = [
            'fecha' => $pedido['fecha'],
            'estatus' => $pedido['estatus'],
            'productos' => []
        ];
    }
    $ordenes_agrupadas[$id_orden]['productos'][] = [
        'id_productos' => $pedido['id_productos'],
        'nombre' => $pedido['nombre_producto'],
        'cantidad' => $pedido['cantidad'],
        'precio' => $pedido['precio'],
        'subtotal' => $pedido['cantidad'] * $pedido['precio']
    ];
}

if (isset($_POST['insertar'])) {
    $id_orden = $_POST['id_orden'];
    $id_usuario = $_SESSION['id_usuario'];
    $fecha_pedido = $_POST['fecha_pedido'];
    $estatus = 'aceptado';

    // Filtrar los productos de esta orden
    foreach ($ordenes_agrupadas[$id_orden]['productos'] as $producto) {
        $id_productos = $producto['id_productos'];
        $cantidad = $producto['cantidad'];

        $sql_insert = "INSERT INTO pedidos (id_usuario, id_productos, fecha_pedido, estatus, cantidad) 
                       VALUES (:id_usuario, :id_productos, :fecha_pedido, :estatus, :cantidad)";
        $query_insert = $cnnPDO->prepare($sql_insert);
        $query_insert->bindParam(':id_usuario', $id_usuario);
        $query_insert->bindParam(':id_productos', $id_productos);
        $query_insert->bindParam(':fecha_pedido', $fecha_pedido);
        $query_insert->bindParam(':estatus', $estatus);
        $query_insert->bindParam(':cantidad', $cantidad);
        $query_insert->execute(); // No hace falta verificar en cada paso
    }

    $sql_delete = "DELETE FROM ordenes WHERE id_orden = :id_orden";
    $query_delete = $cnnPDO->prepare($sql_delete);
    $query_delete->bindParam(':id_orden', $id_orden);
    $query_delete->execute();



    $_SESSION['toastr'] = [
        'type' => 'success',
        'message' => 'Pedido aceptado con éxito'
    ];
    header("Location: ver_pedidos.php");
    exit();
} else {
    $_SESSION['toastr'] = [
        'type' => 'error',
        'message => Error al aceptar el pedido'
    ];
}


if (isset($_POST['eliminar'])) {
    $id_orden = $_POST['id_orden'];

    $sql_delete = "DELETE FROM ordenes WHERE id_orden = :id_orden";
    $query_delete = $cnnPDO->prepare($sql_delete);
    $query_delete->bindParam(':id_orden', $id_orden);

    if ($query_delete->execute()) {
        $_SESSION['toastr'] = [
            'type' => 'success',
            'message' => 'Pedido rechazado'
        ];
        header("Location: ver_pedidos.php");
        exit();
    } else {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message => Error al rechazar el pedido'
        ];
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</head>

<body>
    <nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
        <div class="container-fluid">
            <h1 class="navbar-brand">Bienvenido <?php echo $name; ?></h1>
            <div class="collapse navbar-collapse" id="navbarColor01">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="./sesion_usuario.php">Pagina Principal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./pedidos.php">Pedidos de Material</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./carrito.php">carrito</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./ver_pedidos.php">Solicitud de Pedidos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="./pedidos_aceptados.php">Pedidos Aceptados</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/M&CMaquinados/logout.php">Cerrar Sesión</a>
                    </li>
            </div>
        </div>
    </nav>

    <h2 style="text-align: center;">Mis Pedidos</h2>
    <br>
    <?php if (empty($ordenes_agrupadas)): ?>
        <h2 class="text-danger text-center">No hay pedidos registrados.</h2>
    <?php else: ?>
        <?php foreach ($ordenes_agrupadas as $id_orden => $orden): ?>
            <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;">
                <h3>Orden #<?= $id_orden ?> - <?= $orden['fecha'] ?> (<?= $orden['estatus'] ?>)</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        foreach ($orden['productos'] as $producto):
                            $total += $producto['subtotal'];
                        ?>
                            <tr>
                                <td><?= $producto['nombre'] ?></td>
                                <td><?= $producto['cantidad'] ?></td>
                                <td>$<?= number_format($producto['precio'], 2) ?></td>
                                <td>$<?= number_format($producto['subtotal'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="3"><strong>Total</strong></td>
                            <td><strong>$<?= number_format($total, 2) ?></strong></td>
                            
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>

</html>
