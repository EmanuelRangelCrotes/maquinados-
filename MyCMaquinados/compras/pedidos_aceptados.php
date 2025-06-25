<?php
require_once './db_conexion.php';
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

// Consulta para obtener los pedidos aceptados
$sql_search = "SELECT p.id_pedidos, p.cantidad, p.fecha_pedido, pr.nombre FROM pedidos p 
JOIN productos pr ON p.id_productos = pr.id_productos
 WHERE p.id_usuario = :id_usuario AND estatus = 'aceptado'";
$query = $cnnPDO->prepare($sql_search);
$query->bindParam(':id_usuario', $usuario_id);
$query->execute();
$pedidos_aceptados = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</head>

<body>
    <nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
        <div class="container-fluid">
            <h1 class="navbar-brand">Bienvenido <?php echo $name; ?></h1>
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
                        <a class="nav-link" href="/M&CMaquinados/logout.php">Cerrar Sesión</a>
                    </li>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 style="text-align: center;">Pedidos Aceptados</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID Pedido</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Fecha de Aceptación</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($pedidos_aceptados as $pedido) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($pedido['id_pedidos']) . "</td>";
                    echo "<td>" . htmlspecialchars($pedido['nombre']) . "</td>";
                    echo "<td>" . htmlspecialchars($pedido['cantidad']) . "</td>";
                    echo "<td>" . htmlspecialchars($pedido['fecha_pedido']) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
</body>

</html>