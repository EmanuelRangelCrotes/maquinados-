<?php
session_start();
require_once './db_conexion.php';

// Validación de datos
if (!isset($_POST['id_productos'], $_POST['cantidad']) || empty($_POST['id_productos']) || empty($_POST['cantidad'])) {
    $_SESSION['toastr'] = ['type' => 'error', 'message' => 'Datos incompletos'];
    header('Location: pedir_material_user.php');
    exit();
}

// Sanitización de datos
$id_usuario = $_SESSION['id_usuario'];
$id_producto = filter_var($_POST['id_productos'], FILTER_SANITIZE_NUMBER_INT);
$cantidad = filter_var($_POST['cantidad'], FILTER_SANITIZE_NUMBER_INT);
$transaccion_activa = false;

try {
    // Verificar stock disponible
    $sql_verificar = "SELECT existencia, nombre FROM productos WHERE id_productos = ?";
    $stmt_verificar = $cnnPDO->prepare($sql_verificar);
    $stmt_verificar->execute([$id_producto]);
    $producto = $stmt_verificar->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        $_SESSION['toastr'] = ['type' => 'error', 'message' => 'Producto no encontrado o no disponible'];
        header('Location: pedir_material_user.php');
        exit();
    }

    if ($cantidad <= 0) {
        $_SESSION['toastr'] = ['type' => 'error', 'message' => 'Cantidad inválida'];
        header('Location: pedir_material_user.php');
        exit();
    }

    if ($cantidad > $producto['existencia']) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Stock insuficiente de ' . htmlspecialchars($producto['nombre']) . '. Disponible: ' . $producto['existencia']
        ];
        header('Location: pedir_material_user.php');
        exit();
    }

    // Iniciar transacción
    $cnnPDO->beginTransaction();
    $transaccion_activa = true;

    // Registrar la solicitud
    $sql_insert = "INSERT INTO solicitudes (id_usuario, id_productos, cantidad, estado, fecha_solicitud) 
                  VALUES (?, ?, ?, 'Pendiente', NOW())";
    $stmt_insert = $cnnPDO->prepare($sql_insert);
    $stmt_insert->execute([$id_usuario, $id_producto, $cantidad]);


    $cnnPDO->commit();

    $_SESSION['toastr'] = [
        'type' => 'success',
        'message' => 'Solicitud registrada correctamente. Cantidad: ' . $cantidad
    ];
} catch (PDOException $e) {
    if ($transaccion_activa) $cnnPDO->rollBack();
    $_SESSION['toastr'] = [
        'type' => 'error',
        'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
    ];
} catch (Exception $e) {
    if ($transaccion_activa) $cnnPDO->rollBack();
    $_SESSION['toastr'] = [
        'type' => 'error',
        'message' => 'Error inesperado: ' . $e->getMessage()
    ];
}

header('Location: pedir_material_user.php');
exit();
