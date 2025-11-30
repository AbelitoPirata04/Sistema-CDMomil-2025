<?php
// config/conexion.php - VERSIÓN FINAL CORREGIDA

// 1. Intentar leer variables (Probamos ambas formas: con y sin guion bajo)
$db_host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST');
$db_user = getenv('MYSQLUSER') ?: getenv('MYSQL_USER');
$db_pass = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD');
$db_port = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT');

// AQUÍ ESTABA EL ERROR: Railway usa MYSQL_DATABASE (con guion bajo)
$db_name = getenv('MYSQL_DATABASE') ?: getenv('MYSQLDATABASE'); 

// Si no hay host de nube, usamos Local (XAMPP)
if (!$db_host) {
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'cdmomil';
    $db_port = 3306;
}

// 2. Conectar al Servidor (Sin seleccionar DB todavía)
$conn = new mysqli($db_host, $db_user, $db_pass, "", $db_port);

if ($conn->connect_error) {
    die("Fallo al conectar al servidor SQL: " . $conn->connect_error);
}

// 3. Seleccionar la Base de Datos
// Intentamos con el nombre que trajimos de la variable
$db_selected = false;
if ($db_name && $conn->select_db($db_name)) {
    $db_selected = true;
} 
// Si falló, intentamos con el nombre por defecto de Railway: 'railway'
elseif ($conn->select_db('railway')) {
    $db_selected = true;
}

if (!$db_selected) {
    // Si llega aquí, mostramos el error en pantalla para que sepas qué pasó
    die("ERROR CRÍTICO: No se pudo seleccionar la base de datos. <br>" .
        "Intentamos conectar a: " . ($db_name ? $db_name : '(Vacío)') . "<br>" .
        "Error MySQL: " . $conn->error);
}

$conn->set_charset("utf8");
?>