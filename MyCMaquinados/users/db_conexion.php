<?php
/* Definir las variables para la conexion al PDO */
define('DB_HOST', 'localhost');
define('DB_NAME', 'proyecto');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

$utf8 = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');

try {
    $cnnPDO = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASSWORD,
        $utf8
    );
    // Modo seguro de errores
    $cnnPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Registrar el error en un archivo de log
    error_log("[".date('Y-m-d H:i:s')."] Error de conexión: " . $e->getMessage() . PHP_EOL, 3, __DIR__ . '/../errors.log');
    // Mensaje genérico para el usuario
    die("Error de conexión a la base de datos. Intente más tarde.");
}