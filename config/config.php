<?php
// config/config.php - INTELIGENTE

// Si existe la variable de entorno de Railway, estamos en la nube
if (getenv('MYSQLHOST')) {
    define('BASE_URL', '/'); // En la nube, es la raíz
} else {
    define('BASE_URL', '/Proyecto_CDMomil/'); // En local, es tu carpeta
}
?>