<?php
// config/conexion.php MODIFICADO PARA DESPLIEGUE

// Credenciales por defecto (XAMPP Local)
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'cdmomil';
$db_port = 3306;

// Si estamos en Railway (o producción), usamos sus variables de entorno
if (getenv('RAILWAY_ENVIRONMENT')) {
    $db_host = $_ENV['MYSQLHOST'];
    $db_user = $_ENV['MYSQLUSER'];
    $db_pass = $_ENV['MYSQLPASSWORD'];
    $db_name = $_ENV['MYSQLDATABASE'];
    $db_port = $_ENV['MYSQLPORT'];
}

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>