<?php

require_once 'bd/db_conexion.php';
session_start();

$name = htmlspecialchars($_SESSION['name'], ENT_QUOTES, 'UTF-8'); // Escapar caracteres especiales
$usuario_id = $_SESSION['id_usuario'];

// Validar si el formulario fue enviado
if (isset($_POST['agregar'])) {
    // Sanitizar y validar los datos ingresados por el usuario
    $nombre = htmlspecialchars(trim($_POST['nombre']), ENT_QUOTES, 'UTF-8');
    $sku = htmlspecialchars(trim($_POST['sku']), ENT_QUOTES, 'UTF-8');
    $clase = htmlspecialchars(trim($_POST['clase']), ENT_QUOTES, 'UTF-8');
    $descripcion = htmlspecialchars(trim($_POST['descripcion']), ENT_QUOTES, 'UTF-8');
    $unidad_medida = htmlspecialchars(trim($_POST['unidad_medida']), ENT_QUOTES, 'UTF-8');
    $precio = trim($_POST['precio']);

    // Validar que los campos no estén vacíos
    if (
        empty($nombre) ||
        empty($sku) ||
        empty($clase) ||
        empty($descripcion) ||
        empty($unidad_medida) ||
        empty($precio)
    ) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Todos los campos son obligatorios.'
        ];
        header("Location: productos.php");
        exit();
    }

    // Validar que el precio sea numérico
    if (!is_numeric($precio)) {
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'El precio debe ser un valor numérico.'
        ];
        header("Location: productos.php");
        exit();
    }

    try {
        // Insertar el producto en la base de datos
        $sql = "INSERT INTO productos (nombre, sku, clase, descripcion, unidad_medida, precio) VALUES (?, ?, ?, ?, ?, ?)";
        $query = $cnnPDO->prepare($sql);
        $query->execute([$nombre, $sku, $clase, $descripcion, $unidad_medida, $precio]);

        // Mensaje de éxito
        $_SESSION['toastr'] = [
            'type' => 'success',
            'message' => 'Producto agregado correctamente.'
        ];
    } catch (PDOException $e) {
        error_log($e->getMessage(), 3, 'errors.log');
        $_SESSION['toastr'] = [
            'type' => 'error',
            'message' => 'Error al agregar el producto. Intente nuevamente.'
        ];
    }

    // Redirigir para evitar reenvío de formulario
    header("Location: productos.php");
    exit();
}

// Inicializar variables para mantener los valores del formulario tras error
$nombre = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
$sku = isset($_POST['sku']) ? htmlspecialchars($_POST['sku']) : '';
$clase = isset($_POST['clase']) ? htmlspecialchars($_POST['clase']) : '';
$descripcion = isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '';
$unidad_medida = isset($_POST['unidad_medida']) ? htmlspecialchars($_POST['unidad_medida']) : '';
$precio = isset($_POST['precio']) ? htmlspecialchars($_POST['precio']) : '';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['name'])) {
    header('Location: login.php');
    exit();
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css" rel="stylesheet">
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
                        <a class="nav-link" href="pedidos.php">Pedidos de Material</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="carrito.php">Carrito</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ver_pedidos.php">Solicitud de Pedidos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="col-md-4" style="margin: 0 auto; margin-top: 50px;">
        <div class="card">
            <div class="card-header">
                Datos de los productos
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="form-group">
                        <label for="name">Nombre:</label>
                        <input type="text" class="form-control" name="nombre" id="nombre">
                    </div>
                    <div class="form-group">
                        <label for="sku">SKU</label>
                        <input type="text" class="form-control" name="sku" id="sku">
                    </div>
                    <div class="form-group">
                        <label for="clase">Clase</label>
                        <select type="text" class="form-control" name="clase" id="clase">
                            <option value="">Seleccione una clase</option>
                            <option value="Acero E">Acero E</option>
                            <option value="Administrativo">Administrativo</option>
                            <option value="Consumible">Consumible</option>
                            <option value="Decoracion">Decoración</option>
                            <option value="EHS">EHS</option>
                            <option value="Electrico">Electrico</option>
                            <option value="EPP">EPP</option>
                            <option value="Equipo">Equipo</option>
                            <option value="Herrajes">Herrajes</option>
                            <option value="Herramienta">Herramienta</option>
                            <option value="IT">IT</option>
                            <option value="Limpieza">Limpieza</option>
                            <option value="Medico">Medico</option>
                            <option value="Papeleria">Papeleria</option>
                            <option value="Perfil">Perfil</option>
                            <option value="Quimico">Quimico</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <select type="text" class="form-control" name="descripcion" id="descripcion">
                            <option value="">Seleccione una descripción</option>
                            <option value="Accesorios">Accesorios</option>
                            <option value="Alcoholimetro">Alcoholimetro</option>
                            <option value="Angulo">Angulo</option>
                            <option value="Arandela">Arandela</option>
                            <option value="Armella">Armella</option>
                            <option value="Atomizador">Atomizador</option>
                            <option value="Balero">Balero</option>
                            <option value="Bateria">Bateria</option>
                            <option value="Bisagra">Bisagra</option>
                            <option value="Bolsa">Bolsa</option>
                            <option value="Bomba">Bomba</option>
                            <option value="Boquilla">Boquilla</option>
                            <option value="Broca">Broca</option>
                            <option value="Brocha">Brocha</option>
                            <option value="Bruje">Bruje</option>
                            <option value="Burill">Burill</option>
                            <option value="Cable">Cable</option>
                            <option value="Cadena">Cadena</option>
                            <option value="Caja">Caja</option>
                            <option value="Camara">Camara</option>
                            <option value="Camisola">Camisola</option>
                            <option value="Candado">Candado</option>
                            <option value="Carda">Carda</option>
                            <option value="Casco">Casco</option>
                            <option value="Casquillo">Casquillo</option>
                            <option value="Cinchos">Cinchos</option>
                            <option value="Cinta">Cinta</option>
                            <option value="Clavija">Clavija</option>
                            <option value="Codo Soldable">Codo Soldable</option>
                            <option value="Conector">Conector</option>
                            <option value="Cortadora">Cortadora</option>
                            <option value="Cuadrado">Cuadrado</option>
                            <option value="Cubeta">Cubeta</option>
                            <option value="Cuter">Cuter</option>
                            <option value="Dado">Dado</option>
                            <option value="DC3">DC3</option>
                            <option value="Desengrasante">Desengrasante</option>
                            <option value="Destorcedor">Destorcedor</option>
                            <option value="Disco">Disco</option>
                            <option value="Dispensador">Dispensador</option>
                            <option value="Emplaye">Emplaye</option>
                            <option value="Envase">Envase</option>
                            <option value="Envio">Envio</option>
                            <option value="Espejo">Espejo</option>
                            <option value="Estufa">Estufa</option>
                            <option value="Extensión">Extensión</option>
                            <option value="Figura">Figura</option>
                            <option value="Flexometro">Flexometro</option>
                            <option value="Foco">Foco</option>
                            <option value="Folder">Folder</option>
                            <option value="Gancho">Gancho</option>
                            <option value="Garantia">Garantia</option>
                            <option value="Gas">Gas</option>
                            <option value="Grapadora">Grapadora</option>
                            <option value="Guardacabo">Guardacabo</option>
                            <option value="Hoja">Hoja</option>
                            <option value="Iman">Iman</option>
                            <option value="Lamina">Lamina</option>
                            <option value="Lente de Seguridad">Lente de Seguridad</option>
                            <option value="Lija">Lija</option>
                            <option value="Llave">Llave</option>
                            <option value="Lona">Lona</option>
                            <option value="Maletin">Maletin</option>
                            <option value="Marcador">Marcador</option>
                            <option value="Matraca">Matraca</option>
                            <option value="Matraz">Matraz</option>
                            <option value="Medicamento">Medicamento</option>
                            <option value="Mezcla">Mezcla</option>
                            <option value="Microalambre">Microalambre</option>
                            <option value="Mouse">Mouse</option>
                            <option value="Niple">Niple</option>
                            <option value="Perno">Perno</option>
                            <option value="Pija">Pija</option>
                            <option value="Pintura">Pintura</option>
                            <option value="Pinzas">Pinzas</option>
                            <option value="Pistola">Pistola</option>
                            <option value="Placa">Placa</option>
                            <option value="Pluma">Pluma</option>
                            <option value="Portaelectrodo">Portaelectrodo</option>
                            <option value="Protector">Protector</option>
                            <option value="PTR">PTR</option>
                            <option value="Pulidor">Pulidor</option>
                            <option value="Punta">Punta</option>
                            <option value="Receptaculo">Receptaculo</option>
                            <option value="Redondo">Redondo</option>
                            <option value="Regaton">Regaton</option>
                            <option value="Regulador">Regulador</option>
                            <option value="Resistol">Resistol</option>
                            <option value="Resorte">Resorte</option>
                            <option value="Rondamiento">Rondamiento</option>
                            <option value="Rondana">Rondana</option>
                            <option value="Sacagrapa">Sacagrapa</option>
                            <option value="Seguidores">Seguidores</option>
                            <option value="Sensor">Sensor</option>
                            <option value="Servicio">Servicio</option>
                            <option value="Sierracinta">Sierracinta</option>
                            <option value="Silicon">Silicon</option>
                            <option value="Soldadura">Soldadura</option>
                            <option value="Solera">Solera</option>
                            <option value="Solvente">Solvente</option>
                            <option value="Tabla">Tabla</option>
                            <option value="Tapa">Tapa</option>
                            <option value="Tapon seg">Tapon seg</option>
                            <option value="Tarquete">Tarquete</option>
                            <option value="Tarjeta">Tarjeta</option>
                            <option value="Television">Television</option>
                            <option value="Tijera">Tijera</option>
                            <option value="Toalla">Toalla</option>
                            <option value="Tobera">Tobera</option>
                            <option value="Tornillo">Tornillo</option>
                            <option value="Torre">Torre</option>
                            <option value="Tramite">Tramite</option>
                            <option value="Trapeador">Trapeador</option>
                            <option value="Trapo">Trapo</option>
                            <option value="Tubo">Tubo</option>
                            <option value="Tuerca">Tuerca</option>
                            <option value="Valvula">Valvula</option>
                            <option value="Varilla">Varilla</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="unidad_medida">Unidad de Medida</label>
                        <select type="text" class="form-control" name="unidad_medida" id="unidad_medida">
                            <option value="">Seleccione la Unida de Medida</option>
                            <option value="KG">KG</option>
                            <option value="LT">LT</option>
                            <option value="PZ">PZ</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="precio">Precio</label>
                        <input type="text" class="form-control" name="precio" id="precio">
                    </div>
                    <div class="btn-group" role="group" aria-label="">
                        <button type="submit" name="agregar" class="btn btn-success">Agregar</button>
                    </div>
                </form>
            </div>
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