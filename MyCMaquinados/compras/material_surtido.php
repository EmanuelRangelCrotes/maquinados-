<?php
// compras/seguimiento_solicitudes.php
require_once 'db_conexion.php';
session_start();
// Obtener el nombre de usuario de la sesión
$name = isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'compras';

if (!isset($_SESSION['name']) || $_SESSION['rol'] != 'compras') {
    header('Location: ../login.php');
    exit();
}

// Obtener solicitudes con filtro
$filtro = $_GET['filtro'] ?? 'todos';
$filtro_sql = match ($filtro) {
    'pendientes' => "WHERE sm.estatus = 'Pendiente'",
    'parciales' => "WHERE sm.estatus = 'Parcial'",
    'surtidos' => "WHERE sm.estatus = 'Surtido'",
    default => ""
};

$sql = "SELECT sm.*, p.nombre, p.sku, p.clase
        FROM solicitar_material sm
        JOIN productos p ON sm.id_productos = p.id_productos
        $filtro_sql
        AND (sm.estatus != 'Surtido' OR YEARWEEK(sm.fecha_surtido, 1) = YEARWEEK(CURDATE(), 1))
        ORDER BY 
            CASE sm.estatus 
                WHEN 'Pendiente' THEN 1
                WHEN 'Parcial' THEN 2
                ELSE 3
            END,
            sm.fecha DESC";
$solicitudes = $cnnPDO->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
    <div class="container-fluid">
        <h1 class="navbar-brand">Compras</h1>
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
                    <a class="nav-link" href="material_surtido.php">Material Surtido</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Cerrar Sesión</a>
                </li>
        </div>
    </div>
</nav>
<div class="row">
    <div class="col-lg-12">
        <div class="card card-default rounded-0 shadow">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Seguimiento de Solicitudes</h3>
                    <div class="btn-group">
                        <a href="?filtro=todos" class="btn btn-sm btn-outline-secondary <?= $filtro == 'todos' ? 'active' : '' ?>">Todos</a>
                        <a href="?filtro=pendientes" class="btn btn-sm btn-outline-danger <?= $filtro == 'pendientes' ? 'active' : '' ?>">Pendientes</a>
                        <a href="?filtro=parciales" class="btn btn-sm btn-outline-warning <?= $filtro == 'parciales' ? 'active' : '' ?>">Parciales</a>
                        <a href="?filtro=surtidos" class="btn btn-sm btn-outline-success <?= $filtro == 'surtidos' ? 'active' : '' ?>">Surtidos</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Producto</th>
                            <th>SKU</th>
                            <th>Solicitado</th>
                            <th>Surtido</th>
                            <th>Estatus</th>
                            <th>Fecha Solicitud</th>
                            <th>Fecha Surtido</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitudes as $solicitud):
                            $porcentaje = $solicitud['cantidad'] > 0 ?
                                round(($solicitud['cantidad_surtida'] / $solicitud['cantidad']) * 100) : 0;
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($solicitud['id_solicitud']) ?></td>
                                <td><?= htmlspecialchars($solicitud['nombre']) ?></td>
                                <td><?= htmlspecialchars($solicitud['sku']) ?></td>
                                <td><?= htmlspecialchars($solicitud['cantidad']) ?></td>
                                <td><?= htmlspecialchars($solicitud['cantidad_surtida']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $solicitud['estatus'] == 'Surtido' ? 'success' : ($solicitud['estatus'] == 'Parcial' ? 'warning' : 'danger') ?>">
                                        <?= htmlspecialchars($solicitud['estatus']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($solicitud['fecha']) ?></td>
                                <td><?= $solicitud['fecha_surtido'] ? htmlspecialchars($solicitud['fecha_surtido']) : 'N/A' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>

</html>