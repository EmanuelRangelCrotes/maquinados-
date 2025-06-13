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

    foreach ($carrito as $id_producto => $cantidad) {
        $sql = "INSERT INTO pedidos (id_usuario, id_productos, cantidad, fecha_pedido, estatus) 
                VALUES (:id_usuario, :id_productos, :cantidad, :fecha_pedido, :estatus)";
        $stmt = $cnnPDO->prepare($sql);
        $stmt->bindParam(':id_usuario', $usuario_id);
        $stmt->bindParam(':id_productos', $id_producto);
        $stmt->bindParam(':cantidad', $cantidad);
        $stmt->bindParam(':fecha_pedido', $fecha_pedido);
        $stmt->bindParam(':estatus', $estatus);
        $stmt->execute();
    }

    $cnnPDO->commit();

    unset($_SESSION['carrito']);

    $_SESSION['toastr'] = [
        'type' => 'success',
        'message' => 'Pedido enviado correctamente. Esperando aprobación.'
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
