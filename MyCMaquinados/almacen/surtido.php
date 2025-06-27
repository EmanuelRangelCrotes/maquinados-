<?php
// almacen/surtir_material.php
include_once './templates/header.php';
require_once 'db_conexion.php';
session_start();

if (!isset($_SESSION['name']) || $_SESSION['rol'] != 'almacen') {
    header('Location: ../login.php');
    exit();
}

// Procesar surtido
if (isset($_POST['surtir'])) {
    $id_solicitud = filter_input(INPUT_POST, 'id_solicitud', FILTER_SANITIZE_NUMBER_INT);
    $cantidad_surtir = filter_input(INPUT_POST, 'cantidad_surtir', FILTER_SANITIZE_NUMBER_INT);
    $accion = filter_input(INPUT_POST, 'accion');

    try {
        // Obtener datos actuales
        $sql_select = "SELECT cantidad, cantidad_surtida FROM solicitar_material WHERE id_solicitud = ?";
        $stmt_select = $cnnPDO->prepare($sql_select);
        $stmt_select->execute([$id_solicitud]);
        $solicitud = $stmt_select->fetch(PDO::FETCH_ASSOC);

        $nueva_cantidad_surtida = ($accion == 'agregar') ?
            $solicitud['cantidad_surtida'] + $cantidad_surtir :
            $cantidad_surtir;

        // Determinar estatus
        if ($nueva_cantidad_surtida >= $solicitud['cantidad']) {
            $estatus = 'Surtido';
        } elseif ($nueva_cantidad_surtida > 0) {
            $estatus = 'Parcial';
        } else {
            $estatus = 'Pendiente';
        }

        // Actualizar solicitud
        $sql = "UPDATE solicitar_material 
                SET cantidad_surtida = ?, estatus = ?, fecha_surtido = NOW()
                WHERE id_solicitud = ?";
        $stmt = $cnnPDO->prepare($sql);
        $stmt->execute([$nueva_cantidad_surtida, $estatus, $id_solicitud]);

        // Actualizar inventario si se surte
        if ($cantidad_surtir > 0) {
            $sql_inventario = "UPDATE productos p
                              JOIN solicitar_material sm ON p.id_productos = sm.id_productos
                              SET p.existencia = p.existencia - ?
                              WHERE sm.id_solicitud = ?";
            $stmt_inventario = $cnnPDO->prepare($sql_inventario);
            $stmt_inventario->execute([$cantidad_surtir, $id_solicitud]);
        }

        $_SESSION['toastr'] = [
            'type' => 'success',
            'message' => 'Material surtido correctamente. Estatus: ' . $estatus
        ];

        header("Location: surtido.php");
        exit();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Error al surtir el material: ' . $e->getMessage()
        ];
    }
}

// Obtener solicitudes
$sql = "SELECT sm.*, p.nombre, p.sku, p.clase, p.existencia
        FROM solicitar_material sm
        JOIN productos p ON sm.id_productos = p.id_productos
        WHERE sm.estatus != 'Surtido' OR sm.fecha_surtido >= CURDATE() - INTERVAL 7 DAY
        ORDER BY 
            CASE sm.estatus 
                WHEN 'Pendiente' THEN 1
                WHEN 'Parcial' THEN 2
                ELSE 3
            END,
            sm.fecha DESC";
$solicitudes = $cnnPDO->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card card-default rounded-0 shadow">
            <div class="card-header">
                <h3 class="card-title">Surtir Material Solicitado</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>SKU</th>
                            <th>Solicitado</th>
                            <th>Surtido</th>
                            <th>Existencia</th>
                            <th>Estatus</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitudes as $solicitud): ?>
                            <tr class="<?= $solicitud['estatus'] == 'Surtido' ? 'table-success' : ($solicitud['estatus'] == 'Parcial' ? 'table-warning' : 'table-light') ?>">
                                <td><?= htmlspecialchars($solicitud['id_solicitud']) ?></td>
                                <td><?= htmlspecialchars($solicitud['nombre']) ?></td>
                                <td><?= htmlspecialchars($solicitud['sku']) ?></td>
                                <td><?= htmlspecialchars($solicitud['cantidad']) ?></td>
                                <td><?= htmlspecialchars($solicitud['cantidad_surtida']) ?></td>
                                <td><?= htmlspecialchars($solicitud['existencia']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $solicitud['estatus'] == 'Surtido' ? 'success' : ($solicitud['estatus'] == 'Parcial' ? 'warning' : 'danger') ?>">
                                        <?= htmlspecialchars($solicitud['estatus']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($solicitud['fecha']) ?></td>
                                <td>
                                    <form method="post" class="row g-2">
                                        <input type="hidden" name="id_solicitud" value="<?= $solicitud['id_solicitud'] ?>">
                                        <div class="col-5">
                                            <select name="accion" class="form-select form-select-sm">
                                                <option value="agregar">Agregar</option>
                                                <option value="actualizar">Actualizar</option>
                                            </select>
                                        </div>
                                        <div class="col-5">
                                            <input type="number" name="cantidad_surtir"
                                                class="form-control form-control-sm"
                                                min="0" max="<?= $solicitud['existencia'] ?>"
                                                value="<?= $solicitud['cantidad'] - $solicitud['cantidad_surtida'] ?>">
                                        </div>
                                        <div class="col-2">
                                            <button type="submit" name="surtir" class="btn btn-sm btn-primary">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once './templates/footer.php'; ?>