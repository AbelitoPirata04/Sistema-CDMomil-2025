<?php
// config/conexion.php - VERSIÓN BLINDADA (Soluciona "No database selected")

$env_host = getenv('MYSQLHOST');

if ($env_host) {
    // NUBE (Railway)
    $db_host = getenv('MYSQLHOST');
    $db_user = getenv('MYSQLUSER');
    $db_pass = getenv('MYSQLPASSWORD');
    $db_name = getenv('MYSQLDATABASE'); 
    $db_port = getenv('MYSQLPORT');
} else {
    // LOCAL (XAMPP)
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'cdmomil';
    $db_port = 3306;
}

// 1. Conectamos SOLO al servidor (dejamos el nombre de la DB vacío por ahora: "")
$conn = new mysqli($db_host, $db_user, $db_pass, "", $db_port);

if ($conn->connect_error) {
    error_log("Error conectando al servidor: " . $conn->connect_error);
    die("Error de conexión al servidor.");
}

// 2. AHORA SÍ, seleccionamos la base de datos explícitamente
// Si falla con el nombre de la variable, intentamos con 'railway' que es el nombre por defecto
if (!$conn->select_db($db_name)) {
    if (!$conn->select_db('railway')) {
        die("Error fatal: No se pudo seleccionar la base de datos '$db_name' ni 'railway'.");
    }
}

$conn->set_charset("utf8");
?>