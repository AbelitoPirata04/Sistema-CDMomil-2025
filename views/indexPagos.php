<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}
$admin_usuario = $_SESSION['admin_usuario'] ?? 'Administrador';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestión de Pagos - C.D. Momil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../recursos/css/style.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
</head>
<body class="bg-gray-100 font-sans">

    <header class="bg-green-900 text-white shadow-lg">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center space-x-4">
                <button id="menuToggle" class="p-2 rounded-md hover:bg-green-800 transition">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <img src="../recursos/img/logoCD.png" alt="Logo CD Momil" class="w-12 h-12" />
                <h1 class="text-xl font-bold">Panel de Administración</h1>
            </div>
            <div class="flex items-center space-x-4 relative">
                <span class="text-base">Hola, <?php echo htmlspecialchars($admin_usuario); ?></span> 
                <div class="relative">
                    <button id="profileToggle" class="w-10 h-10 bg-yellow-600 rounded-full flex items-center justify-center hover:bg-yellow-500 transition">
                        <span class="text-sm font-bold">A</span>
                    </button>
                    <div id="profileDropdown" class="dropdown absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-50 hidden">
                        <div class="py-2">
                            <a href="../authe/logout.php" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-100">Cerrar Sesión</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div id="overlay" class="overlay fixed inset-0 bg-black bg-opacity-50 z-40 hidden"></div>

    <nav id="sidebar" class="sidebar fixed left-0 top-0 h-full w-72 bg-green-800 shadow-lg z-50 pt-20 transform -translate-x-full transition-transform duration-300">
        <div class="py-8">
            <a href="indexAdmin.php" class="flex items-center px-8 py-4 hover:bg-green-700 cursor-pointer group border-b border-green-700">
                <div class="w-8 h-8 bg-yellow-600 rounded-lg mr-5 flex justify-center items-center text-white font-bold">I</div><span class="text-white text-lg">Inicio</span>
            </a>
            <a href="indexUsuarios.php" class="flex items-center px-8 py-4 hover:bg-green-700 cursor-pointer group border-b border-green-700">
                <div class="w-8 h-8 bg-yellow-600 rounded-lg mr-5 flex justify-center items-center text-white font-bold">U</div>
                
      <span class="text-white font-semibold text-lg">Usuarios Asistentes</span>
            </a>
            <a href="indexEntrenador.php" class="flex items-center px-8 py-4 hover:bg-green-700 cursor-pointer group border-b border-green-700">
                <div class="w-8 h-8 bg-yellow-600 rounded-lg mr-5 flex justify-center items-center text-white font-bold">E</div><span class="text-white text-lg">Entrenadores</span>
            </a>
            <a href="indexJugador.php" class="flex items-center px-8 py-4 hover:bg-green-700 cursor-pointer group border-b border-green-700">
                <div class="w-8 h-8 bg-yellow-600 rounded-lg mr-5 flex justify-center items-center text-white font-bold">J</div><span class="text-white text-lg">Jugadores</span>
            </a>
            <a href="indexPartidos.php" class="flex items-center px-8 py-4 hover:bg-green-700 cursor-pointer group border-b border-green-700">
                <div class="w-8 h-8 bg-yellow-600 rounded-lg mr-5 flex justify-center items-center text-white font-bold">P</div><span class="text-white text-lg">Partidos</span>
            </a>
            <a href="indexEstadisticas.php" class="flex items-center px-8 py-4 hover:bg-green-700 cursor-pointer group border-b border-green-700">
                <div class="w-8 h-8 bg-yellow-600 rounded-lg mr-5 flex justify-center items-center text-white font-bold">PL</div><span class="text-white text-lg">Plantilla</span>
            </a>
            <a href="indexPagos.php" class="flex items-center px-8 py-4 bg-green-700 cursor-pointer group border-b border-green-700 border-l-4 border-yellow-500">
                <div class="w-8 h-8 bg-yellow-600 rounded-lg mr-5 flex justify-center items-center text-white font-bold">$</div><span class="text-white text-lg">Pagos</span>
            </a>
        </div>
    </nav>

    <main class="p-8">
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-8 py-6 border-b border-gray-200">
                <div class="flex justify-between items-center flex-wrap gap-4">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900">Gestión de Pagos</h2>
                        <p class="text-gray-600 mt-2">Registra y consulta las mensualidades de los jugadores.</p>
                    </div>
                    <div class="flex space-x-3">
                        <button id="btnGenerarInformePagos" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold shadow-md flex items-center transition transform hover:scale-105">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Informe PDF
                        </button>
                        <button id="btnNuevoPago" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold shadow-md flex items-center transition transform hover:scale-105">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path></svg>
                            Registrar Pago
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jugador</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mes Pagado</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha de Pago</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaPagos" class="bg-white divide-y divide-gray-200">
                        <tr><td colspan="6" class="p-8 text-center text-gray-500">Cargando datos...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="modalPago" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-lg mx-4 flex flex-col max-h-[90vh]">
            
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50 rounded-t-lg">
                <h3 id="modalTitle" class="text-xl font-bold text-gray-900">Registrar Pago</h3>
                <button id="btnCerrarX" class="text-gray-400 hover:text-red-500 font-bold text-2xl transition">&times;</button>
            </div>

            <div class="p-6 overflow-y-auto">
                <form id="formPago">
                    <input type="hidden" id="idPago" name="idPago">

                    <div class="space-y-5">
                        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200 shadow-sm">
                            <label for="filtroCategoriaModal" class="block text-sm font-bold text-yellow-800 uppercase mb-2">
                                1. Filtrar jugadores por Categoría:
                            </label>
                            <select id="filtroCategoriaModal" class="w-full px-3 py-2 border border-yellow-300 rounded-lg focus:ring-2 focus:ring-yellow-500 bg-white">
                                <option value="">Todas las categorías</option>
                            </select>
                        </div>

                        <div>
                            <label for="idJugador" class="block text-sm font-medium text-gray-700 mb-1">2. Seleccionar Jugador <span class="text-red-500">*</span></label>
                            <select id="idJugador" name="idJugador" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 transition">
                                <option value="">Cargando...</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="monto" class="block text-sm font-medium text-gray-700 mb-1">Monto ($) <span class="text-red-500">*</span></label>
                                <input type="number" id="monto" name="monto" step="0.01" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500" placeholder="0">
                            </div>
                            <div>
                                <label for="fechaPago" class="block text-sm font-medium text-gray-700 mb-1">Fecha <span class="text-red-500">*</span></label>
                                <input type="date" id="fechaPago" name="fechaPago" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="mesPagado" class="block text-sm font-medium text-gray-700 mb-1">Mes <span class="text-red-500">*</span></label>
                                <select id="mesPagado" name="mesPagado" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                    <option value="">Seleccionar...</option>
                                    <option value="Enero">Enero</option>
                                    <option value="Febrero">Febrero</option>
                                    <option value="Marzo">Marzo</option>
                                    <option value="Abril">Abril</option>
                                    <option value="Mayo">Mayo</option>
                                    <option value="Junio">Junio</option>
                                    <option value="Julio">Julio</option>
                                    <option value="Agosto">Agosto</option>
                                    <option value="Septiembre">Septiembre</option>
                                    <option value="Octubre">Octubre</option>
                                    <option value="Noviembre">Noviembre</option>
                                    <option value="Diciembre">Diciembre</option>
                                </select>
                            </div>
                            <div>
                                <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado <span class="text-red-500">*</span></label>
                                <select id="estado" name="estado" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                    <option value="Pagado">Pagado</option>
                                    <option value="Pendiente">Pendiente</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-8 pt-4 border-t border-gray-200">
                        <button type="button" id="btnCancelar" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition font-medium">
                            Cancelar
                        </button>
                        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-bold shadow-lg">
                            Guardar Pago
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../recursos/js/pagos.js?v=<?php echo time(); ?>"></script>
</body>
</html>