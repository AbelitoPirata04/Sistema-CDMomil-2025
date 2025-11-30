<?php
// config/conexion.php - CORREGIDO PARA RAILWAY

// Detectar si estamos en Railway usando una variable de entorno
// Si MYSQLHOST existe, estamos en la nube.
$host_nube = getenv('MYSQLHOST');

if ($host_nube) {
    // ESTAMOS EN RAILWAY (Nube)
    $db_host = getenv('MYSQLHOST');
    $db_user = getenv('MYSQLUSER');
    $db_pass = getenv('MYSQLPASSWORD');
    $db_name = getenv('MYSQLDATABASE');
    $db_port = getenv('MYSQLPORT');
} else {
    // ESTAMOS EN LOCAL (XAMPP)
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'cdmomil';
    $db_port = 3306;
}

// Crear conexión
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

// Verificar errores
if ($conn->connect_error) {
    // Esto imprimirá el error en los logs de Railway si falla
    error_log("Error de conexión a BD: " . $conn->connect_error);
    die("Error de conexión a la base de datos.");
}

$conn->set_charset("utf8");
?>