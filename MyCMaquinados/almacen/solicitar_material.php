<?php include_once './templates/header.php';

require_once './db_conexion.php';
session_start();

$name = htmlspecialchars($_SESSION['name'], ENT_QUOTES, 'UTF-8'); // Escapar caracteres especiales
$usuario_id = $_SESSION['id_usuario'];

// Validar si el formulario fue enviado
if (isset($_POST['agregar'])) {
    // Sanitizar y validar los datos ingresados por el usuario
    $id_productos = isset($_POST['id_productos']) ? htmlspecialchars(trim($_POST['id_productos']), ENT_QUOTES, 'UTF-8') : '';
    $cantidad = isset($_POST['cantidad']) ? htmlspecialchars(trim($_POST['cantidad']), ENT_QUOTES, 'UTF-8') : '';
    $fecha = date('Y-m-d'); // Fecha actual

    // Validar que los campos no estén vacíos
    if (
        empty($cantidad)
    ) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Todos los campos son obligatorios.'
        ];
        header("Location: solicitar_material.php");
        exit();
    }

    try {
        // Insertar el producto en la base de datos
        $sql = "INSERT INTO solicitar_material (id_productos,cantidad, fecha) VALUES (?, ?, ?)";
        $query = $cnnPDO->prepare($sql);
        $query->execute([$id_productos, $cantidad, $fecha]);

        // Mensaje de éxito
        $_SESSION['toastr'] = [
            'type' => 'success',
            'message' => 'Solicitud agregada correctamente.'
        ];
    } catch (PDOException $e) {
        error_log($e->getMessage(), 3, 'errors.log');
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Error al agregar la solicitud. Intente nuevamente.'
        ];
    }

    // Redirigir para evitar reenvío de formulario
    header("Location: solicitar_material.php");
    exit();
}

// Inicializar variables para mantener los valores del formulario tras error
$nombre = isset($_POST['nombre']) ? htmlspecialchars($_POST['name']) : '';
$sku = isset($_POST['sku']) ? htmlspecialchars($_POST['sku']) : '';
$clase = isset($_POST['clase']) ? htmlspecialchars($_POST['clase']) : '';
$descripcion = isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '';
$cantidad = isset($_POST['cantidad']) ? htmlspecialchars($_POST['cantidad']) : '';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['name'])) {
    header('Location: login.php');
    exit();
}

$sql_search = "SELECT sm.id_solicitud, sm.cantidad, sm.fecha, p.id_productos, p.nombre,p.sku, p.clase, p.descripcion, p.existencia
FROM solicitar_material sm
JOIN productos p ON sm.id_productos = p.id_productos
ORDER BY id_solicitud DESC";
$query_search = $cnnPDO->prepare($sql_search);
$query_search->execute();
$solicitudes = $query_search->fetchAll(PDO::FETCH_ASSOC);


$sql_productos = "SELECT id_productos, nombre, sku, clase, descripcion, existencia FROM productos ORDER BY nombre ASC";
$query_productos = $cnnPDO->prepare($sql_productos);
$query_productos->execute();
$productos = $query_productos->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card card-default rounded-0 shadow">
            <div class="card-header">
                <div class="row">
                    <div class="col-lg-10 col-md-10 col-sm-8 col-xs-6">
                        <h3 class="card-title">Lista de Solicitudes</h3>
                    </div>

                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 text-end">
                        <button type="button" name="addPurchase" id="addPurchase"
                            class="btn btn-primary btn-sm rounded-0" data-bs-toggle="modal"
                            data-bs-target="#purchaseModal">
                            Solicitar Material
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
                                    <th>SKU</th>
                                    <th>Clase</th>
                                    <th>Descripción</th>
                                    <th>Cantidad</th>
                                    <th>Existencia</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($solicitudes as $solicitud): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($solicitud['id_solicitud'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($solicitud['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($solicitud['sku'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($solicitud['clase'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($solicitud['descripcion'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($solicitud['cantidad'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($solicitud['existencia'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($solicitud['fecha'], ENT_QUOTES, 'UTF-8') ?></td>


                                    </tr>
                                <?php endforeach; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>


<div id="purchaseModal" class="modal fade">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Solicitar Material</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="col-md-8" style="margin: 0 auto; margin-top: 50px;">
                    <div class="card">
                        <div class="card-header">
                            Datos de los productos
                        </div>
                        <div class="card-body">
                            <form method="post" id="solicitudForm">
                                <div class="mb-3">
                                    <label for="sku">SKU:</label>
                                    <select class="form-control" name="id_productos" id="sku">
                                        <option value="">Seleccione un Material</option>
                                        <?php foreach ($productos as $producto): ?>
                                            <option
                                                value="<?= htmlspecialchars($producto['id_productos'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-nombre="<?= htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-clase="<?= htmlspecialchars($producto['clase'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-descripcion="<?= htmlspecialchars($producto['descripcion'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-existencia="<?= htmlspecialchars($producto['existencia'], ENT_QUOTES, 'UTF-8') ?>">
                                                <?= htmlspecialchars($producto['sku'], ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="nombre">Nombre:</label>
                                    <input type="text" class="form-control" name="nombre" id="material_nombre">
                                </div>

                                <div class="mb-3">
                                    <label for="clase">Clase:</label>
                                    <input type="text" class="form-control" name="clase" id="material_clase">
                                </div>
                                <div class="mb-3">
                                    <label for="descripcion">Descripción:</label>
                                    <input type="text" class="form-control" name="descripcion"
                                        id="material_descripcion">
                                </div>
                                <div class="mb-3">
                                    <label for="cantidad">Cantidad:</label>
                                    <input type="number" class="form-control" name="cantidad" id="cantidad">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="agregar" class="btn btn-primary" form="solicitudForm">Solicitar</button>
                <button type="button" class="btn btn-default border btn-sm rounded-0"
                    data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('sku').addEventListener('change', function() {
        var selected = this.options[this.selectedIndex];
        document.getElementById('material_nombre').value = selected.getAttribute('data-nombre');
        document.getElementById('material_clase').value = selected.getAttribute('data-clase');
        document.getElementById('material_descripcion').value = selected.getAttribute('data-descripcion');
    });
</script>

<?php include_once './templates/footer.php'; ?>