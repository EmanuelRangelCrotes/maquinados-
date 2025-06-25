<?php
require_once './templates/header.php';
require_once './db_conexion.php';
session_start();

$id_usuario = $_SESSION['id_usuario'];
$name = $_SESSION['name'];


$sql_pendientes = "SELECT s.id_solicitud, s.id_usuario, s.id_productos, s.cantidad, s.fecha_solicitud, s.estado,
                        p.nombre AS nombre_producto, p.sku, p.existencia, u.name AS nombre_usuario
                  FROM solicitudes s
                  JOIN productos p ON s.id_productos = p.id_productos
                  JOIN users u ON s.id_usuario = u.id_usuario
                  WHERE s.estado = 'Pendiente' AND u.id_usuario = :id_usuario
                  ORDER BY s.fecha_solicitud DESC";
$query_pendientes = $cnnPDO->prepare($sql_pendientes);
$query_pendientes->bindParam(':id_usuario', $id_usuario);
$query_pendientes->execute();
$pendientes = $query_pendientes->fetchAll(PDO::FETCH_ASSOC);

$sql_aceptadas = "SELECT s.id_solicitud, s.id_usuario, s.id_productos, s.cantidad, s.fecha_solicitud, s.estado,
                        p.nombre AS nombre_producto, p.sku, p.existencia, u.name AS nombre_usuario
                  FROM solicitudes s
                  JOIN productos p ON s.id_productos = p.id_productos
                  JOIN users u ON s.id_usuario = u.id_usuario
                  WHERE s.estado = 'Aceptada' AND u.id_usuario = :id_usuario
                  ORDER BY s.fecha_solicitud DESC";
$query_aceptadas = $cnnPDO->prepare($sql_aceptadas);
$query_aceptadas->bindParam(':id_usuario', $id_usuario);
$query_aceptadas->execute();
$aceptadas = $query_aceptadas->fetchAll(PDO::FETCH_ASSOC);

$sql_rechazadas = "SELECT s.id_solicitud, s.id_usuario, s.id_productos, s.cantidad, s.fecha_solicitud, s.estado,
                        p.nombre AS nombre_producto, p.sku, p.existencia, u.name AS nombre_usuario
                  FROM solicitudes s
                  JOIN productos p ON s.id_productos = p.id_productos
                  JOIN users u ON s.id_usuario = u.id_usuario
                  WHERE s.estado = 'Rechazada' AND u.id_usuario = :id_usuario
                  ORDER BY s.fecha_solicitud DESC";
$query_rechazadas = $cnnPDO->prepare($sql_rechazadas);
$query_rechazadas->bindParam(':id_usuario', $id_usuario);
$query_rechazadas->execute();
$rechazadas = $query_rechazadas->fetchAll(PDO::FETCH_ASSOC);

?>


<body>
    
    <br>
    <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle" type="button" id="triggerId" data-bs-toggle="dropdown" aria-expanded="false">
            Solicitudes
        </button>
        <div class="dropdown-menu" aria-labelledby="triggerId">
            <a class="dropdown-item" href="pedidos_user.php">
                <h6>Pendientes</h6>
            </a>
            <a class="dropdown-item" href="pedidos_aceptados_user.php">
                <h6>Aceptadas</h6>
            </a>
            <a class="dropdown-item" href="pedidos_rechazados_user.php">
                <h6>Rechazadas</h6>
            </a>
            <div class="dropdown-divider"></div>
        </div>
    </div>
    <br>
    <h2 style="text-align: center;">Mis Pedidos</h2>
    <br>

    <?php if (empty($rechazadas)): ?>
        <h2 class="text-danger text-center">No hay solicitudes rechazadas.</h2>
    <?php else: ?>
        <?php foreach ($rechazadas as $solicitud): ?>
            <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;">
                <h3>Orden #<?= $solicitud['id_solicitud'] ?> - <?= $solicitud['fecha_solicitud'] ?> (<?= $solicitud['estado'] ?>)</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>SKU</th>
                            <th>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= $solicitud['nombre_producto'] ?></td>
                            <td><?= $solicitud['sku'] ?></td>
                            <td><?= $solicitud['cantidad'] ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>

</html>