<?php
require_once './bd/db_conexion.php';
require_once './auth_functions.php';
session_start();

// Si ya está logueado, redirige según su rol
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    redirigirSegunRol();
    exit();
}

if (isset($_POST['login'])) {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);

    // Validación de campos vacíos
    if (empty($email) || empty($password)) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Por favor, complete todos los campos.'
        ];
        header('Location: login.php');
        exit();
    }

    // Validación de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'El email ingresado no es válido.'
        ];
        header('Location: login.php');
        exit();
    }

    try {
        $stmt = $cnnPDO->prepare('SELECT id_usuario, name, password, rol FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION = [
                'id_usuario' => $user['id_usuario'],
                'name' => htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'),
                'rol' => $user['rol'],
                'logged_in' => true,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'last_activity' => time()
            ];
            redirigirSegunRol();
            exit();
        } else {
            $_SESSION['toastr'] = [
                'type' => 'error',
                'message' => 'Credenciales incorrectas.'
            ];
            header('Location: login.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Error de conexión a la base de datos: ' . $e->getMessage()
        ];
        header('Location: login.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Error: ' . $e->getMessage()
        ];
        header('Location: login.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
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
                </ul>
            </div>
        </div>
    </nav>

    <div class="col-md-4" style="margin: 0 auto; margin-top: 50px;">
        <div>
            <form method="post">
                <label class="form-label mt-4">Inicia Sesión</label>
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" name="email" id="floatingInput" placeholder="name@example.com" required>
                    <label for="floatingInput">Email</label>
                </div>
                <div class="form-floating">
                    <input type="password" class="form-control" name="password" id="floatingPassword" placeholder="Password" autocomplete="off" required>
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
        toastr.options = {
            closeButton: true,
            progressBar: true,
            timeOut: 3000,
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