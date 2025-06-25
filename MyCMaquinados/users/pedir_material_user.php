<?php
include_once './templates/header.php';
session_start();
require_once './db_conexion.php';

// Verificación de sesión mejorada
if (!isset($_SESSION['logged_in'], $_SESSION['id_usuario'], $_SESSION['rol']) || $_SESSION['rol'] !== 'user') {
    header('Location: login.php');
    exit();
}

// Obtener productos disponibles
$sql = "SELECT p.id_productos, p.sku, p.nombre, p.clase, p.descripcion, p.existencia 
        FROM productos p";
$stmt = $cnnPDO->prepare($sql);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($productos)) {
    $_SESSION['toastr'] = [
        'type' => 'error',
        'message' => 'No hay productos disponibles.'
    ];
    exit();
}
?>
<div class="row">
    <div class="col-lg-12">
        <div class="card card-default rounded-0 shadow">
            <div class="card-header">
                <div class="row">
                    <div class="col-lg-10 col-md-10 col-sm-8 col-xs-6">
                        <h3 class="card-title">Productos Disponibles</h3>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 text-end">
                        <button type="button" name="addPurchase" id="addPurchase"
                            class="btn btn-primary btn-sm rounded-0"
                            data-bs-toggle="modal" data-bs-target="#purchaseModal">
                            <i class="far fa-plus-square"></i> Solicitar Material
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-12 table-responsive">
                        <table id="purchaseList" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Existencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as $producto): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($producto['id_productos'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($producto['existencia'], ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para solicitar material -->
<div id="purchaseModal" class="modal fade">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Solicitar Material</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    <form method="post" action="procesar_solicitud.php" id="solicitudForm">
                        <input type="hidden" name="id_usuario" value="<?= $_SESSION['id_usuario'] ?>">
                        <div class="mb-3">
                            <label for="sku">Material (SKU):</label>
                            <select name="id_productos" id="sku" class="form-control" required>
                                <option value="">Seleccione un material</option>
                                <?php foreach ($productos as $producto): ?>
                                    <?php if ($producto['existencia'] > 0): ?>
                                        <option
                                            value="<?= htmlspecialchars($producto['id_productos'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-nombre="<?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-clase="<?= htmlspecialchars($producto['clase'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-descripcion="<?= htmlspecialchars($producto['descripcion'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-existencia="<?= htmlspecialchars($producto['existencia'], ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($producto['sku'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Nombre:</label>
                            <input type="text" class="form-control" id="material_nombre" readonly>
                        </div>
                        <div class="mb-3">
                            <label>Clase:</label>
                            <input type="text" class="form-control" id="material_clase" readonly>
                        </div>
                        <div class="mb-3">
                            <label>Descripción:</label>
                            <input type="text" class="form-control" id="material_descripcion" readonly>
                        </div>
                        <div class="mb-3">
                            <label>Existencia:</label>
                            <input type="text" class="form-control" id="material_existencia" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="cantidad">Cantidad a solicitar:</label>
                            <input type="number" class="form-control" name="cantidad" id="cantidad_solicitud" min="1" required>
                            <small class="text-muted">Máximo disponible: <span id="max_disponible">0</span></small>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" form="solicitudForm" class="btn btn-primary">Solicitar</button>
                <button type="button" class="btn btn-default border btn-sm rounded-0" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Autocompletar datos del material seleccionado
    document.getElementById('sku').addEventListener('change', function() {
        var selected = this.options[this.selectedIndex];
        const existencia = parseInt(selected.getAttribute('data-existencia')) || 0;

        document.getElementById('material_nombre').value = selected.getAttribute('data-nombre') || '';
        document.getElementById('material_clase').value = selected.getAttribute('data-clase') || '';
        document.getElementById('material_descripcion').value = selected.getAttribute('data-descripcion') || '';
        document.getElementById('material_existencia').value = existencia;
        document.getElementById('max_disponible').textContent = existencia;
        document.getElementById('cantidad_solicitud').max = existencia;
        document.getElementById('cantidad_solicitud').value = 1;
    });

    // Validación del formulario
    document.getElementById('solicitudForm').addEventListener('submit', function(e) {
        const cantidad = parseInt(document.getElementById('cantidad_solicitud').value);
        const existencia = parseInt(document.getElementById('material_existencia').value);

        if (cantidad > existencia) {
            e.preventDefault();
            toastr.error(`La cantidad solicitada (${cantidad}) supera el stock disponible (${existencia})`);
            return false;
        }

        return true;
    });
</script>

<?php include_once './templates/footer.php'; ?>