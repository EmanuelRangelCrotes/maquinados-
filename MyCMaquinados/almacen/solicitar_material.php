<?php include_once './templates/header.php';

require_once './db_conexion.php';
session_start();

$name = htmlspecialchars($_SESSION['name'], ENT_QUOTES, 'UTF-8'); // Escapar caracteres especiales
$usuario_id = $_SESSION['id_usuario'];

// Validar si el formulario fue enviado
if (isset($_POST['agregar'])) {
    // Sanitizar y validar los datos ingresados por el usuario
    $nombre = htmlspecialchars(trim($_POST['nombre']), ENT_QUOTES, 'UTF-8');
    $sku = htmlspecialchars(trim($_POST['sku']), ENT_QUOTES, 'UTF-8');
    $clase = htmlspecialchars(trim($_POST['clase']), ENT_QUOTES, 'UTF-8');
    $descripcion = htmlspecialchars(trim($_POST['descripcion']), ENT_QUOTES, 'UTF-8');
    $cantidad = isset($_POST['cantidad']) ? htmlspecialchars(trim($_POST['cantidad']), ENT_QUOTES, 'UTF-8') : '';
    $fecha = date('Y-m-d'); // Fecha actual

    // Validar que los campos no estén vacíos
    if (
        empty($nombre) ||
        empty($sku) ||
        empty($clase) ||
        empty($descripcion) ||
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
        $sql = "INSERT INTO solicitar_material (nombre, sku, clase, descripcion, cantidad, fecha) VALUES (?, ?, ?, ?, ?, ?)";
        $query = $cnnPDO->prepare($sql);
        $query->execute([$nombre, $sku, $clase, $descripcion, $cantidad, $fecha]);

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

$sql_search = "SELECT id_solicitud, nombre,sku, clase, descripcion, cantidad, fecha FROM solicitar_material ORDER BY id_solicitud DESC";
$query_search = $cnnPDO->prepare($sql_search);
$query_search->execute();
$solicitudes = $query_search->fetchAll(PDO::FETCH_ASSOC);
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
                            class="btn btn-primary btn-sm rounded-0"
                            data-bs-toggle="modal" data-bs-target="#purchaseModal">
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
                                    <label for="nombre">Nombre:</label>
                                    <input type="text" class="form-control" name="nombre" id="nombre">
                                </div>
                                <div class="mb-3">
                                    <label for="sku">SKU:</label>
                                    <input type="text" class="form-control" name="sku" id="sku">
                                </div>
                                <div class="mb-3">
                                    <label for="clase">Clase:</label>
                                    <input type="text" class="form-control" name="clase" id="clase">
                                </div>
                                <div class="mb-3">
                                    <label for="descripcion">Descripción:</label>
                                    <input type="text" class="form-control" name="descripcion" id="descripcion">
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
                <button type="button" class="btn btn-default border btn-sm rounded-0" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>



<?php include_once './templates/footer.php'; ?>