<?php
function verificarSesion($rol_requerido) {
    session_start();
    
    if (!isset($_SESSION['logged_in'], $_SESSION['id_usuario'], $_SESSION['rol'])) {
        header('Location: ../login.php');
        exit();
    }
    
    if ($_SESSION['rol'] !== $rol_requerido) {
        $_SESSION['toastr'] = ['type' => 'error', 'message' => 'No tienes permisos para acceder a esta sección'];
        header('Location: ../login.php');
        exit();
    }
    
    // Verificar seguridad adicional
    if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT'] || 
        $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
        session_destroy();
        header('Location: ../login.php?error=security');
        exit();
    }
}

function redirigirSegunRol() {
    if ($_SESSION['rol'] === 'compras') {
        header('Location: compras/sesion_usuario.php');
    } elseif ($_SESSION['rol'] === 'almacen') {
        header('Location: almacen/index_inventario.php');
    } else {
        header('Location: users/pedir_material_user.php');
    }
    exit();
}
?>