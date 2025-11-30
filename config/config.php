<?php
// config/config.php

// Detectamos si estamos en Railway revisando si existe una variable de entorno de la nube
if (getenv('MYSQLHOST')) {
    // ESTAMOS EN RAILWAY (Nube)
    // Allá la ruta es la raíz del sitio
    define('BASE_URL', '/');
} else {
    // ESTAMOS EN XAMPP (Local)
    // Aquí seguimos usando tu carpeta
    define('BASE_URL', '/Proyecto_CDMomil/');
}
?>