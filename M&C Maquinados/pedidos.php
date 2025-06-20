<?php
require_once 'bd/db_conexion.php';
session_start();

// Tiempo límite de inactividad (30 minutos)
$tiempo_limite = 1800;
if (isset($_SESSION['ultimo_acceso'])) {
    $inactividad = time() - $_SESSION['ultimo_acceso'];
    if ($inactividad > $tiempo_limite) {
        session_unset(); // Eliminar datos de la sesión
        session_destroy(); // Destruir la sesión
        header('Location: login.php');
        exit();
    }
}
$_SESSION['ultimo_acceso'] = time(); // Actualizar el tiempo de acceso

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['name'])) {
    header('Location: login.php');
    exit();
}

$name = htmlspecialchars($_SESSION['name'], ENT_QUOTES, 'UTF-8'); // Escapar caracteres especiales
$usuario_id = $_SESSION['id_usuario'];


$sql = "SELECT * FROM productos";
$query = $cnnPDO->prepare($sql);
$query->execute();
if ($query->rowCount() == 0) {
    $_SESSION['toastr'] = [
        'type' => 'error',
        'message' => 'No hay productos disponibles.'
    ];
    header("Location: productos.php");
    exit();
}
$productos = $query->fetchAll(PDO::FETCH_ASSOC);


if (isset($_POST['agregar_carrito'])) {
    $id_productos = $_POST['id_productos'];

    // Convertir a entero por seguridad
    $id_productos = intval($id_productos);

    // Si no existe en el carrito, lo agregamos con cantidad 1
    if (!isset($_SESSION['carrito'][$id_productos])) {
        $_SESSION['carrito'][$id_productos] = 1;
        $_SESSION['toastr'] = [
            'type' => 'success',
            'message' => 'Producto agregado al carrito.'
        ];
    } else {
        $_SESSION['toastr'] = [
            'type' => 'info',
            'message' => 'El producto ya está en el carrito.'
        ];
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


</head>

<body>
    <nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
        <div class="container-fluid">
            <h1 class="navbar-brand">Bienvenido <?php echo $name; ?></h1>
            <div class="collapse navbar-collapse" id="navbarColor01">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="sesion_usuario.php">Pagina Principal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="productos.php">Solicitar Material</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="carrito.php">carrito</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ver_pedidos.php">Solicitud de Pedidos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Cerrar Sesión</a>
                    </li>
            </div>
        </div>
    </nav>

    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>SKU</th>
                <th>Clase</th>
                <th>Descripción</th>
                <th>Unidad de Medida</th>
                <th>Precio</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($productos)): ?>
                <tr>
                    <td colspan="6" class="text-center text-danger">No hay productos disponibles.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($productos as $producto): ?>
                    <tr>
                        <input type="hidden" name="id_productos" value="<?php echo $producto['id_productos']; ?>">
                        <td><?php echo htmlspecialchars($producto['nombre']); ?> b4-b</td>
                        <td><?php echo htmlspecialchars($producto['sku']); ?></td>
                        <td><?php echo htmlspecialchars($producto['clase']); ?></td>
                        <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                        <td><?php echo htmlspecialchars($producto['unidad_medida']); ?></td>
                        <td><?php echo htmlspecialchars($producto['precio']); ?></td>

                        <td>
                            <form method="post">
                                <input type="hidden" name="id_productos" value="<?php echo $producto['id_productos']; ?>">
                                <form method="post">
                                    <input type="hidden" name="id_productos" value="<?php echo $producto['id_productos']; ?>">
                                    <button type="submit" name="agregar_carrito" class="btn btn-outline-success">Agregar al Carrito</button>
                                </form>

                            </form>
                        </td>

                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>;

    <!-- Script para mostrar las alertas de Toastr -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>

    <script>
        // Configuración global de Toastr
        toastr.options = {
            closeButton: true, // Agrega el botón de cerrar
            progressBar: true, // Muestra una barra de progreso
            timeOut: 3000, // Tiempo antes de que desaparezca (3s)
            extendedTimeOut: 2000,
            positionClass: "toast-top-right"
        };

        $(document).ready(function() {
            <?php if (isset($_SESSION['toastr'])): ?>
                toastr.<?= $_SESSION['toastr']['type'] ?>("<?= addslashes($_SESSION['toastr']['message']) ?>");
                <?php unset($_SESSION['toastr']); ?>
            <?php endif; ?>
        });
    </script>
</body>

</html>
