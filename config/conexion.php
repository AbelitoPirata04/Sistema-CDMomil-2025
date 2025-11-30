<?php
// config/conexion.php - VERSIÓN A PRUEBA DE FALLOS

// 1. Intentar obtener variables de entorno (Railway)
$env_host = $_ENV['MYSQLHOST'] ?? getenv('MYSQLHOST');
$env_user = $_ENV['MYSQLUSER'] ?? getenv('MYSQLUSER');
$env_pass = $_ENV['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD');
$env_db   = $_ENV['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE');
$env_port = $_ENV['MYSQLPORT'] ?? getenv('MYSQLPORT');

// 2. Decidir si usar Nube o Local
if (!empty($env_host)) {
    // Estamos en Railway
    $db_host = $env_host;
    $db_user = $env_user;
    $db_pass = $env_pass;
    $db_name = $env_db;
    $db_port = $env_port;
} else {
    // Estamos en XAMPP (Local)
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'cdmomil';
    $db_port = 3306;
}

// 3. Conectar
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>