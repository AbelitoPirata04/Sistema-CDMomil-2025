<?php
session_start();

// --- SEGURIDAD ---
if (!isset($_SESSION['jugador_logged_in']) || $_SESSION['jugador_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$nombre_jugador = $_SESSION['jugador_nombre'];
$id_jugador = $_SESSION['jugador_id'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - <?php echo htmlspecialchars($nombre_jugador); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../recursos/css/style.css">
    <style>
        /* Estilo para los 'placeholders' mientras cargan los datos */
        .loading-placeholder {
            background-color: #f0f0f0;
            border-radius: 4px;
            min-height: 20px;
            width: 80%;
            animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">

    <header class="bg-green-900 text-white shadow-lg">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center space-x-4">
                <img src="../recursos/img/logoCD.png" alt="Logo CD Momil" class="w-12 h-12" />
                <h1 class="text-xl font-bold">Perfil de Jugador - C.D. Momil</h1>
            </div>
            <div class="flex items-center space-x-4 relative">
                <span class="text-base hidden sm:block">¡Hola, <?php echo htmlspecialchars($nombre_jugador); ?>!</span>
                <a href="../authe/logout.php" class="px-4 py-2 bg-yellow-600 rounded-md hover:bg-yellow-500 transition font-semibold">
                    Cerrar Sesión
                </a>
            </div>
        </div>
    </header>

    <main class="p-4 md:p-8">
        <div class="bg-white rounded-lg shadow-md max-w-5xl mx-auto overflow-hidden">
            
            <div class="p-6 md:p-8 bg-gray-50 border-b border-gray-200">
                <div class="flex flex-col md:flex-row items-center">
                    <div id="perfil-dorsal" class="w-24 h-24 bg-green-600 text-white rounded-full flex items-center justify-center text-5xl font-bold mb-4 md:mb-0 md:mr-6 flex-shrink-0">
                        <span class="loading-placeholder w-12 h-12"></span>
                    </div>
                    <div>
                        <h2 id="perfil-nombre-completo" class="text-3xl font-bold text-gray-900 loading-placeholder"></h2>
                        <div class="flex space-x-4 mt-2">
                            <span id="perfil-posicion" class="text-lg text-green-700 font-semibold loading-placeholder"></span>
                            <span class="text-gray-400">|</span>
                            <span id="perfil-categoria" class="text-lg text-gray-600 loading-placeholder"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6 md:p-8">
                <h3 class="text-2xl font-semibold text-gray-900 mb-6">Rendimiento</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 text-center">
                    <div class="bg-green-50 p-4 rounded-lg shadow-sm">
                        <span class="text-sm text-gray-500 uppercase">Partidos</span>
                        <p id="stats-partidos" class="text-4xl font-bold text-green-700 loading-placeholder"></p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg shadow-sm">
                        <span class="text-sm text-gray-500 uppercase">Goles</span>
                        <p id="stats-goles" class="text-4xl font-bold text-green-700 loading-placeholder"></p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg shadow-sm">
                        <span class="text-sm text-gray-500 uppercase">Minutos</span>
                        <p id="stats-minutos" class="text-4xl font-bold text-green-700 loading-placeholder"></p>
                    </div>
                    <div class="bg-yellow-50 p-4 rounded-lg shadow-sm">
                        <span class="text-sm text-gray-500 uppercase">Amarillas</span>
                        <p id="stats-amarillas" class="text-4xl font-bold text-yellow-600 loading-placeholder"></p>
                    </div>
                    <div class="bg-red-50 p-4 rounded-lg shadow-sm">
                        <span class="text-sm text-gray-500 uppercase">Rojas</span>
                        <p id="stats-rojas" class="text-4xl font-bold text-red-600 loading-placeholder"></p>
                    </div>
                </div>

                <h3 class="text-2xl font-semibold text-gray-900 mt-10 mb-6">Información Personal</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                    <div class="border-b py-2">
                        <span class="text-sm text-gray-500">Cédula</span>
                        <p id="info-cedula" class="text-lg text-gray-800 loading-placeholder"></p>
                    </div>
                    <div class="border-b py-2">
                        <span class="text-sm text-gray-500">Teléfono</span>
                        <p id="info-telefono" class="text-lg text-gray-800 loading-placeholder"></p>
                    </div>
                    <div class="border-b py-2">
                        <span class="text-sm text-gray-500">Fecha de Nacimiento</span>
                        <p id="info-nacimiento" class="text-lg text-gray-800 loading-placeholder"></p>
                    </div>
                    <div class="border-b py-2">
                        <span class="text-sm text-gray-500">Edad</span>
                        <p id="info-edad" class="text-lg text-gray-800 loading-placeholder"></p>
                    </div>
                    <div class="border-b py-2">
                        <span class="text-sm text-gray-500">Género</span>
                        <p id="info-genero" class="text-lg text-gray-800 loading-placeholder"></p>
                    </div>
                    <div class="border-b py-2">
                        <span class="text-sm text-gray-500">Físico (Altura / Peso)</span>
                        <p id="info-fisico" class="text-lg text-gray-800 loading-placeholder"></p>
                    </div>
                    <div class="border-b py-2">
                        <span class="text-sm text-gray-500">Fecha de Ingreso</span>
                        <p id="info-ingreso" class="text-lg text-gray-800 loading-placeholder"></p>
                    </div>
                    <div class="border-b py-2">
                        <span class="text-sm text-gray-500">Usuario</span>
                        <p id="info-usuario" class="text-lg text-gray-800 loading-placeholder"></p>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <div id="datos-jugador" data-id="<?php echo $id_jugador; ?>"></div>

    <script src="../recursos/js/perfilJugador.js"></script>

</body>
</html>