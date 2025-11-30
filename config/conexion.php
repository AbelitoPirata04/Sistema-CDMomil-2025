<?php
// config/conexion.php

$host_nube = getenv('MYSQLHOST');

if ($host_nube) {
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

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

if ($conn->connect_error) {
    error_log("Error BD: " . $conn->connect_error);
    die("Error de conexión.");
}

$conn->set_charset("utf8");
?>