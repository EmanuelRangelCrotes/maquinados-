<?php
session_start();
require_once 'bd/db_conexion.php';

if (!isset($_SESSION['name']) || !isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header('Location: carrito.php');
    exit();
}

$usuario_id = $_SESSION['id_usuario'];
$carrito = $_SESSION['carrito'];

try {
    $cnnPDO->beginTransaction();

    $fecha_pedido = date('Y-m-d H:i:s');
    $estatus = 'pendiente';

    // 1. Insertar en tabla "ordenes"
    $sql_orden = "INSERT INTO ordenes (id_usuario, fecha, estatus) 
                  VALUES (:id_usuario, :fecha, :estatus)";
    $stmt_orden = $cnnPDO->prepare($sql_orden);
    $stmt_orden->bindParam(':id_usuario', $usuario_id);
    $stmt_orden->bindParam(':fecha', $fecha_pedido);
    $stmt_orden->bindParam(':estatus', $estatus);
    $stmt_orden->execute();

    // Obtener el ID de la orden insertada
    $id_orden = $cnnPDO->lastInsertId();

    // 2. Insertar cada producto del carrito en "orden_detalles"
    $sql_detalle = "INSERT INTO orden_detalles (id_orden, id_productos, cantidad)
                    VALUES (:id_orden, :id_productos, :cantidad)";
    $stmt_detalle = $cnnPDO->prepare($sql_detalle);

    foreach ($carrito as $id_producto => $cantidad) {
        $stmt_detalle->bindParam(':id_orden', $id_orden);
        $stmt_detalle->bindParam(':id_productos', $id_producto);
        $stmt_detalle->bindParam(':cantidad', $cantidad);
        $stmt_detalle->execute();
    }

    $cnnPDO->commit();

    unset($_SESSION['carrito']);

    $_SESSION['toastr'] = [
        'type' => 'success',
        'message' => 'Pedido enviado correctamente como una sola orden.'
    ];

    header('Location: pedidos.php');
    exit();

} catch (Exception $e) {
    $cnnPDO->rollBack();
    $_SESSION['toastr'] = [
        'type' => 'error',
        'message' => 'Error al procesar el pedido: ' . $e->getMessage()
    ];
    header('Location: carrito.php');
    exit();
}
?>
