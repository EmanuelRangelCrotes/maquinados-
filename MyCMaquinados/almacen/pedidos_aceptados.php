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

// Obtener solicitudes pendientes
$sql = "SELECT s.id_solicitud, s.cantidad, s.fecha_respuesta,s.estado,
               p.nombre AS producto_nombre, p.sku, p.existencia,
               u.name AS usuario_nombre
        FROM solicitudes s
        JOIN productos p ON s.id_productos = p.id_productos
        JOIN users u ON s.id_usuario = u.id_usuario
        WHERE s.estado = 'Aceptada'
        ORDER BY s.fecha_respuesta DESC"; 
$query = $cnnPDO->query($sql);
$aceptados = $query->fetchAll(PDO::FETCH_ASSOC);
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
    <h2 style="text-align: center;">Pedidos Aceptados</h2>
    <br>
    <?php if (empty($aceptados)): ?>
        <h2 class="text-danger text-center">No hay solicitudes registradas.</h2>
    <?php else: ?>
        <?php foreach ($aceptados as $solicitud): ?>
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