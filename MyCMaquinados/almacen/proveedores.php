<?php include_once './templates/header.php';

require_once './db_conexion.php';
session_start();

$name = htmlspecialchars($_SESSION['name'], ENT_QUOTES, 'UTF-8'); // Escapar caracteres especiales
$usuario_id = $_SESSION['id_usuario'];

// Validar si el formulario fue enviado
if (isset($_POST['agregar'])) {
    // Sanitizar y validar los datos ingresados por el usuario
    $nombre = htmlspecialchars(trim($_POST['nombre']), ENT_QUOTES, 'UTF-8');
    $telefono = htmlspecialchars(trim($_POST['telefono']), ENT_QUOTES, 'UTF-8');
    $direccion = htmlspecialchars(trim($_POST['direccion']), ENT_QUOTES, 'UTF-8');

    // Validar que los campos no estén vacíos
    if (
        empty($nombre) ||
        empty($telefono) ||
        empty($direccion)
    ) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Todos los campos son obligatorios.'
        ];
        header("Location: proveedores.php");
        exit();
    }


    try {
        // Insertar el producto en la base de datos
        $sql = "INSERT INTO proveedor (nombre, telefono, direccion) VALUES (?, ?, ?)";
        $query = $cnnPDO->prepare($sql);
        $query->execute([$nombre, $telefono, $direccion]);

        // Mensaje de éxito
        $_SESSION['toastr'] = [
            'type' => 'success',
            'message' => 'Proveedor agregado correctamente.'
        ];
    } catch (PDOException $e) {
        error_log($e->getMessage(), 3, 'errors.log');
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Error al agregar al proveedor. Intente nuevamente.'
        ];
    }

    // Redirigir para evitar reenvío de formulario
    header("Location: proveedores.php");
    exit();
}

// Inicializar variables para mantener los valores del formulario tras error
$nombre = isset($_POST['nombre']) ? htmlspecialchars($_POST['name']) : '';
$telefono = isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : '';
$direccion = isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion']) : '';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['name'])) {
    header('Location: login.php');
    exit();
}

$sql_search = "SELECT nombre, telefono, direccion FROM proveedor";
$query_search = $cnnPDO->prepare($sql_search);
$query_search->execute();
$proveedores = $query_search->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card card-default rounded-0 shadow">
            <div class="card-header">
                <div class="row">
                    <div class="col-lg-10 col-md-10 col-sm-8 col-xs-6">
                        <h3 class="card-title">Lista de Proveedores</h3>
                    </div>

                    <div class="col-lg-2 col-md-2 col-sm-4 col-xs-6 text-end">
                        <button type="button" name="addPurchase" id="addPurchase"
                            class="btn btn-primary btn-sm rounded-0"
                            data-bs-toggle="modal" data-bs-target="#purchaseModal">
                            <i class="far fa-plus-square"></i> Agregar Proveedor
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
                                    <th>Nombre</th>
                                    <th>Telefono</th>
                                    <th>Dirección</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proveedores as $proveedor): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($proveedor['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($proveedor['telefono'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($proveedor['direccion'], ENT_QUOTES, 'UTF-8') ?></td>
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
                <h4 class="modal-title"><i class="fa fa-plus"></i> Agregar Compra</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="col-md-8" style="margin: 0 auto; margin-top: 50px;">
                    <div class="card">
                        <div class="card-header">
                            Datos de los productos
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <label for="name">Nombre:</label>
                                    <input type="text" class="form-control" name="nombre" id="nombre">
                                </div>
                                <div class="mb-3">
                                    <label for="Telefono">Telefono</label>
                                    <input type="text" class="form-control" name="telefono" id="telefono">
                                </div>
                                <div class="mb-3">
                                    <label for="Direccion">Direccion</label>
                                    <textarea type="text" class="form-control" name="direccion" id="direccion"></textarea>
                                </div>
                                <div class="btn-group" role="group" aria-label="">
                                    <button type="submit" name="agregar" class="btn btn-success">Agregar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="submit" name="action" id="action" class="btn btn-primary btn-sm rounded-0" value="Agregar" form="purchaseForm" />
                <button type="button" class="btn btn-default border btn-sm rounded-0" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>



<?php include_once './templates/footer.php'; ?>