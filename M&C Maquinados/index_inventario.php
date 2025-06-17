<?php include_once './templates/header.php';

require_once 'bd/db_conexion.php';
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
    $unidad_medida = htmlspecialchars(trim($_POST['unidad_medida']), ENT_QUOTES, 'UTF-8');
    $precio = trim($_POST['precio']);

    // Validar que los campos no estén vacíos
    if (
        empty($nombre) ||
        empty($sku) ||
        empty($clase) ||
        empty($descripcion) ||
        empty($unidad_medida) ||
        empty($precio)
    ) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Todos los campos son obligatorios.'
        ];
        header("Location: productos.php");
        exit();
    }

    // Validar que el precio sea numérico
    if (!is_numeric($precio)) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'El precio debe ser un valor numérico.'
        ];
        header("Location: productos.php");
        exit();
    }

    try {
        // Insertar el producto en la base de datos
        $sql = "INSERT INTO productos (nombre, sku, clase, descripcion, unidad_medida, precio) VALUES (?, ?, ?, ?, ?, ?)";
        $query = $cnnPDO->prepare($sql);
        $query->execute([$nombre, $sku, $clase, $descripcion, $unidad_medida, $precio]);

        // Mensaje de éxito
        $_SESSION['toastr'] = [
            'type' => 'success',
            'message' => 'Producto agregado correctamente.'
        ];
    } catch (PDOException $e) {
        error_log($e->getMessage(), 3, 'errors.log');
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Error al agregar el producto. Intente nuevamente.'
        ];
    }

    // Redirigir para evitar reenvío de formulario
    header("Location: productos.php");
    exit();
}

// Inicializar variables para mantener los valores del formulario tras error
$nombre = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
$sku = isset($_POST['sku']) ? htmlspecialchars($_POST['sku']) : '';
$clase = isset($_POST['clase']) ? htmlspecialchars($_POST['clase']) : '';
$descripcion = isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '';
$unidad_medida = isset($_POST['unidad_medida']) ? htmlspecialchars($_POST['unidad_medida']) : '';
$precio = isset($_POST['precio']) ? htmlspecialchars($_POST['precio']) : '';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['name'])) {
    header('Location: login.php');
    exit();
}
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card card-default rounded-0 shadow">
            <div class="card-header">
                <div class="row">
                    <div class="col-lg-10 col-md-10 col-sm-8 col-xs-6">
                        <h3 class="card-title">Inventario</h3>
                    </div>

                  

                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-12 table-responsive">
                        <table id="purchaseList" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Proveedor</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>






<?php include_once './templates/footer.php'; ?>