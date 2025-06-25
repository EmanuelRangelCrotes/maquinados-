<?php
include_once './templates/header.php';

require_once './db_conexion.php';
session_start();

// Verificar permisos (solo admin o personal autorizado)
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'almacen') {
    $_SESSION['toastr'] = [
        'type' => 'error',
        'message' => 'Acceso no autorizado'
    ];
    header('Location: login.php');
    exit();
}

// Procesar aceptación/rechazo de solicitud
if (isset($_GET['accion']) && in_array($_GET['accion'], ['aceptar', 'rechazar']) && isset($_GET['id'])) {
    $id_solicitud = (int)$_GET['id'];
    $accion = $_GET['accion'];
    $id_autorizador = $_SESSION['id_usuario'];

    try {
        $cnnPDO->beginTransaction();

        if ($accion == 'aceptar') {
            // Obtener datos de la solicitud
            $sql_solicitud = "SELECT id_productos, cantidad FROM solicitudes 
                             WHERE id_solicitud = ? AND estado = 'Pendiente' FOR UPDATE";
            $stmt_solicitud = $cnnPDO->prepare($sql_solicitud);
            $stmt_solicitud->execute([$id_solicitud]);
            $solicitud = $stmt_solicitud->fetch(PDO::FETCH_ASSOC);

            if (!$solicitud) {
                throw new Exception('Solicitud no encontrada o ya procesada');
            }

            // Verificar existencia
            $sql_producto = "SELECT existencia FROM productos WHERE id_productos = ? FOR UPDATE";
            $stmt_producto = $cnnPDO->prepare($sql_producto);
            $stmt_producto->execute([$solicitud['id_productos']]);
            $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);

            if ($producto['existencia'] < $solicitud['cantidad']) {
                throw new Exception('Existencia insuficiente');
            }

            // Actualizar inventario
            $sql_update = "UPDATE productos SET existencia = existencia - ? WHERE id_productos = ?";
            $stmt_update = $cnnPDO->prepare($sql_update);
            $stmt_update->execute([$solicitud['cantidad'], $solicitud['id_productos']]);

            // Marcar solicitud como aceptada
            $sql_aceptar = "UPDATE solicitudes
                           SET estado = 'Aceptada', 
                               fecha_respuesta = NOW()
                           WHERE id_solicitud = ?";
            $stmt_aceptar = $cnnPDO->prepare($sql_aceptar);
            $stmt_aceptar->execute([$id_solicitud]);

            $_SESSION['toastr'] = [
                'type' => 'success',
                'message' => 'Solicitud aceptada e inventario actualizado'
            ];
        } else {
            // Marcar solicitud como rechazada
            $sql_rechazar = "UPDATE solicitudes 
                            SET estado = 'Rechazada', 
                                fecha_respuesta = NOW()
                            WHERE id_solicitud = ?";
            $stmt_rechazar = $cnnPDO->prepare($sql_rechazar);
            $stmt_rechazar->execute([$id_solicitud]);

            $_SESSION['toastr'] = [
                'type' => 'info',
                'message' => 'Solicitud rechazada'
            ];
        }

        $cnnPDO->commit();
    } catch (Exception $e) {
        $cnnPDO->rollBack();
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Error: ' . $e->getMessage()
        ];
    }

    header('Location: gestionar_solicitudes.php');
    exit();
}

// Obtener solicitudes pendientes
$sql = "SELECT s.id_solicitud, s.cantidad, s.fecha_respuesta,s.estado,
               p.nombre AS producto_nombre, p.sku, p.existencia,
               u.name AS usuario_nombre
        FROM solicitudes s
        JOIN productos p ON s.id_productos = p.id_productos
        JOIN users u ON s.id_usuario = u.id_usuario
        WHERE s.estado = 'Rechazada'
        ORDER BY s.fecha_solicitud ASC"; 
$query = $cnnPDO->query($sql);
$pendientes = $query->fetchAll(PDO::FETCH_ASSOC);
?>

 <br>
    <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle" type="button" id="triggerId" data-bs-toggle="dropdown" aria-expanded="false">
            Solicitudes
        </button>
        <div class="dropdown-menu" aria-labelledby="triggerId">
            <a class="dropdown-item" href="gestionar_solicitudes.php">
                <h6>Pendientes</h6>
            </a>
            <a class="dropdown-item " href="pedidos_aceptados.php">
                <h6>Aceptadas</h6>
            </a>
            <a class="dropdown-item" href="pedidos_rechazados.php">
                <h6>Rechazadas</h6>
            </a>
            <div class="dropdown-divider"></div>
        </div>
    </div>
    <br>
    <h2 style="text-align: center;">Pedidos Rechazados</h2>
    <br>
    <?php if (empty($pendientes)): ?>
        <h2 class="text-danger text-center">No hay solicitudes registradas.</h2>
    <?php else: ?>
        <?php foreach ($pendientes as $solicitud): ?>
            <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;">
                <h3>Orden #<?= $solicitud['id_solicitud'] ?> - <?= $solicitud['fecha_respuesta'] ?> (<?= $solicitud['estado'] ?>)</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Solicitante</th>
                            <th>Producto</th>
                            <th>SKU</th>
                            <th>Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= $solicitud['usuario_nombre'] ?></td>
                            <td><?= $solicitud['producto_nombre'] ?></td>
                            <td><?= $solicitud['sku'] ?></td>
                            <td><?= $solicitud['cantidad'] ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

<?php include_once './templates/footer.php'; ?>