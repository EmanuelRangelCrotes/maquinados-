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


$sql = "SELECT p.id_pedidos, p.fecha_pedido, p.estatus, 
               pr.nombre AS nombre_producto, pr.sku, pr.clase, pr.descripcion, pr.unidad_medida, pr.precio,
               u.name AS nombre_usuario
        FROM pedidos p
        JOIN productos pr ON p.id_productos = pr.id_productos
        JOIN users u ON p.id_usuario = u.id_usuario
        ORDER BY p.fecha_pedido DESC";
$query = $cnnPDO->prepare($sql);
$query->execute();
$pedidos = $query->fetchAll(PDO::FETCH_ASSOC);

if ($query->rowCount() == 0) {
    $_SESSION['toastr'] = [
        'type' => 'error',
        'message' => 'No hay pedidos realizados.'
    ];
    header("Location: sesion_usuario.php");
    exit();
}

if(isset($_POST['aceptar_pedido'])){
    $id_pedidos = $_POST['id_pedidos'];
    $estatus = 'aceptado';

    $sql_update = "UPDATE pedidos SET estatus = :estatus WHERE id_pedidos = :id_pedidos";
    $query_update = $cnnPDO->prepare($sql_update);
    $query_update->bindParam(':estatus', $estatus);
    $query_update->bindParam(':id_pedidos', $id_pedidos);

    if ($query_update->execute()) {
        $_SESSION['toastr'] = [
            'type' => 'success',
            'message' => 'Pedido aceptado con éxito.'
        ];
        header("Location: ver_pedidos.php");
        exit();
    } else {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Error al aceptar el pedido.'
        ];
    }
}

if(isset($_POST['rechazar_pedido'])){
    $id_pedidos = $_POST['id_pedidos'];

    $sql_delete = "DELETE FROM pedidos WHERE id_pedidos = :id_pedidos";
    $query_rechazar = $cnnPDO->prepare($sql_delete);

    $query_rechazar->bindParam(':id_pedidos', $id_pedidos);

    if ($query_rechazar->execute()) {
        $_SESSION['toastr'] = [
            'type' => 'success',
            'message' => 'Pedido rechazado con éxito.'
        ];
        header("Location: ver_pedidos.php");
        exit();
    } else {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Error al rechazar el pedido.'
        ];
    }
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
    <nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark" style="width: 121%;">
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
                        <a class="nav-link" href="logout.php">Cerrar Sesión</a>
                    </li>
            </div>
        </div>
    </nav>

    <table class="table">
        <thead>
            <tr>
                <th>Pedido #</th>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Producto</th>
                <th>SKU</th>
                <th>Clase</th>
                <th>Descripción</th>
                <th>Unidad de Medida</th>
                <th>Precio</th>
                <th>Estatus</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $pedido): ?>
                <tr>
                    <td><?php echo htmlspecialchars($pedido['id_pedidos']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['fecha_pedido']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['nombre_usuario']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['nombre_producto']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['sku']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['clase']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['descripcion']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['unidad_medida']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['precio']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['estatus']); ?></td>

                    <td>
                        <form method="post">
                            <input type="hidden" name="id_pedidos" value="<?php echo $pedido['id_pedidos']; ?>">
                            <button type="submit" name="aceptar_pedido" class="btn btn-success">Aceptar Pedido</button>
                        </form>
                    </td>

                    <td>
                        <form method="post">
                            <input type="hidden" name="id_pedidos" value="<?php echo $pedido['id_pedidos']; ?>">
                            <button type="submit" name="rechazar_pedido" class="btn btn-danger">Rechazar Pedido</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    

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