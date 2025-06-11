<?php
require_once 'bd/db_conexion.php';
session_start();

if (isset($_POST['login'])) {
    // Sanitizar y validar los datos ingresados por el usuario
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL); // Sanitizar el email
    $password = trim($_POST['password']); // Eliminar espacios en blanco al inicio y final

    // Validar que los campos no estén vacíos
    if (empty($email) || empty($password)) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Por favor, complete todos los campos.'
        ];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }

    // Validar que el email sea válido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'El email ingresado no es válido.'
        ];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }

    try {
        // Seleccionar el hash de la contraseña desde la base de datos
        $select = $cnnPDO->prepare('SELECT id_usuario, name, password FROM users WHERE email = ?');
        $select->execute([$email]);
        $campo = $select->fetch(PDO::FETCH_ASSOC);

        if ($campo && password_verify($password, $campo['password'])) {
            // Si la contraseña es válida
            $_SESSION['name'] = htmlspecialchars($campo['name'], ENT_QUOTES, 'UTF-8'); // Escapar caracteres especiales
            $_SESSION['id_usuario'] = $campo['id_usuario'];

            // Redirigir al usuario a la página de inicio
            header('location:sesion_usuario.php');
            exit();
        } else {
            // Si la contraseña o el email son incorrectos
            $_SESSION['toastr'] = [
                'type' => 'error',
                'message' => 'Email o contraseña incorrecta.' // Mensaje de error
            ];
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    } catch (PDOException $e) {
        // Registrar el error en un archivo de log para evitar exponer detalles al usuario
        error_log($e->getMessage(), 3, 'errors.log');

        // Mensaje genérico para el usuario
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Ocurrió un error al iniciar sesión. Intente nuevamente.'
        ];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css" rel="stylesheet">


</head>

<body>
    <nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">M&C Maquinados</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor01"
                aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarColor01">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="registro.php">Registrate</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Inicia Sesion</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">About</a>
                    </li>
            </div>
        </div>
    </nav>

    <div class="col-md-4" style="margin: 0 auto; margin-top: 50px;">
        <div>
            <form method="post">
                <label class="form-label mt-4">Inicia Sesión</label>
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" name="email" id="floatingInput" placeholder="name@example.com">
                    <label for="floatingInput">Email</label>
                </div>
                <div class="form-floating">
                    <input type="password" class="form-control" name="password" id="floatingPassword" placeholder="Password" autocomplete="off">
                    <label for="floatingPassword">Password</label>
                </div>
                <button type="submit" class="btn btn-primary mt-3" name="login">Ingresa</button>
            </form>

        </div>
    </div>

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