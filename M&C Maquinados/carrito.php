<?php
session_start();
require_once 'bd/db_conexion.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['name'])) {
    header('Location: login.php');
    exit();
}

$name = htmlspecialchars($_SESSION['name'], ENT_QUOTES, 'UTF-8');
$usuario_id = $_SESSION['id_usuario'];

// Obtener carrito de la sesión, si no existe es un array vacío
$carrito = isset($_SESSION['carrito']) && is_array($_SESSION['carrito']) ? $_SESSION['carrito'] : [];

// Procesar actualización de cantidad
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = $_POST['id_producto'];
    $accion = $_POST['accion'];

    if (isset($_SESSION['carrito'][$id_producto])) {
        if ($accion === 'incrementar') {
            $_SESSION['carrito'][$id_producto]++;
        } elseif ($accion === 'disminuir') {
            if ($_SESSION['carrito'][$id_producto] > 1) {
                $_SESSION['carrito'][$id_producto]--;
            } else {
                unset($_SESSION['carrito'][$id_producto]);
            }
        }
    }

    header('Location: carrito.php');
    exit();
}

// Obtener productos del carrito (solo los que están en la sesión)
$productos = [];
if (!empty($carrito)) {
    $ids = implode(',', array_map('intval', array_keys($carrito)));
    $sql = "SELECT * FROM productos WHERE id_productos IN ($ids)";
    $stmt = $cnnPDO->prepare($sql);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Calcular total
$total = 0;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Carrito de Compras</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
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
                        <a class="nav-link" href="pedidos.php">Pedidos de Material</a>
                    </li>
                    <li class="nav-item">
                    <li class="nav-item">
                        <a class="nav-link" href="ver_pedidos.php">Solicitud de Pedidos</a>
                    </li>
                    <a class="nav-link" href="logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <h2>Carrito de Compras</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
                <th>Modificar</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($productos)): ?>
                <tr>
                    <td colspan="5" class="text-center">
                        <h2 class="text-danger">El carrito está vacío.</h2>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($productos as $producto):
                    $id = $producto['id_productos'];
                    $cantidad = $carrito[$id];
                    $precio = $producto['precio'];
                    $subtotal = $cantidad * $precio;
                    $total += $subtotal;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($producto['nombre']) ?></td>
                        <td><?= $cantidad ?></td>
                        <td>$<?= number_format($precio, 2) ?></td>
                        <td>$<?= number_format($subtotal, 2) ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="id_producto" value="<?= $id ?>">
                                <input type="hidden" name="accion" value="incrementar">
                                <button type="submit" class="btn btn-sm btn-success">+</button>
                            </form>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="id_producto" value="<?= $id ?>">
                                <input type="hidden" name="accion" value="disminuir">
                                <button type="submit" class="btn btn-sm btn-danger">−</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">Total:</th>
                <th>$<?= number_format($total, 2) ?></th>
                <th></th>
            </tr>
        </tfoot>
    </table>
    <a href="finalizar_pedido.php" class="btn btn-success">Finalizar Pedido</a>
</body>

</html>