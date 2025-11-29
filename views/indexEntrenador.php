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
  <title>Entrenadores - C.D. Momil</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../recursos/css/style.css">
</head>
<body class="bg-gray-100 font-sans">
  
  <header class="bg-green-900 text-white shadow-lg relative z-30">
    <div class="flex items-center justify-between px-6 py-4">
      <div class="flex items-center space-x-4">
        <button id="menuToggle" class="p-2 rounded-md hover:bg-green-800 transition focus:outline-none">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
        <img src="../recursos/img/logoCD.png" alt="Logo CD Momil" class="w-12 h-12" />
        <h1 class="text-xl font-bold">Panel de Administración</h1>
      </div>
      
      <div class="flex items-center space-x-4 relative">
        <span class="text-base hidden md:block">Hola, <?php echo htmlspecialchars($admin_usuario); ?></span> 
        <div class="relative">
            <button id="profileToggle" class="w-10 h-10 bg-yellow-600 rounded-full flex items-center justify-center hover:bg-yellow-500 transition focus:outline-none">
                <span class="text-sm font-bold">A</span>
            </button>
            
            <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-50">
                <div class="py-1">
                    <a href="../authe/logout.php" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 w-full text-left">
                        <svg class="w-5 h-5 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
      </div>
    </div>
  </header>

  <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity duration-300"></div>

  <nav id="sidebar" class="fixed left-0 top-0 h-full w-72 bg-green-800 shadow-2xl z-50 pt-20 transform -translate-x-full transition-transform duration-300 ease-in-out">
    <div class="py-4">
        <a href="../views/indexAdmin.php" class="flex items-center px-8 py-4 hover:bg-green-700 transition border-b border-green-700">
            <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center mr-4 text-white font-bold">I</div>
            <span class="text-white text-lg">Inicio</span>
        </a>
        <a href="../views/indexUsuarios.php" class="flex items-center px-8 py-4 hover:bg-green-700 transition border-b border-green-700">
            <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center mr-4 text-white font-bold">U</div>
      <span class="text-white font-semibold text-lg">Usuarios Asistentes</span>
        </a>
        <a href="indexEntrenador.php" class="flex items-center px-8 py-4 bg-green-700 border-l-4 border-yellow-500 transition border-b border-green-700">
            <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center mr-4 text-white font-bold">E</div>
            <span class="text-white text-lg">Entrenadores</span>
        </a>
        <a href="indexJugador.php" class="flex items-center px-8 py-4 hover:bg-green-700 transition border-b border-green-700">
            <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center mr-4 text-white font-bold">J</div>
            <span class="text-white text-lg">Jugadores</span>
        </a>
        <a href="indexPartidos.php" class="flex items-center px-8 py-4 hover:bg-green-700 transition border-b border-green-700">
            <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center mr-4 text-white font-bold">P</div>
            <span class="text-white text-lg">Partidos</span>
        </a>
        <a href="indexEstadisticas.php" class="flex items-center px-8 py-4 hover:bg-green-700 transition border-b border-green-700">
            <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center mr-4 text-white font-bold">PL</div>
            <span class="text-white text-lg">Plantilla</span>
        </a>
        <a href="indexPagos.php" class="flex items-center px-8 py-4 hover:bg-green-700 transition border-b border-green-700">
            <div class="w-8 h-8 bg-yellow-600 rounded-lg flex items-center justify-center mr-4 text-white font-bold">$</div>
            <span class="text-white text-lg">Pagos</span>
        </a>
    </div>
  </nav>

  <main class="p-4 md:p-8">
    <div class="bg-white rounded-lg shadow-md min-h-[500px]">
      <div class="px-6 py-6 border-b border-gray-200 flex flex-col md:flex-row justify-between items-center gap-4">
          <div>
            <h2 class="text-3xl font-bold text-gray-900">Gestión de Entrenadores</h2>
            <p class="text-gray-600 mt-1">Administra los entrenadores del club.</p>
          </div>
          <button id="btnNuevoEntrenador" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition flex items-center shadow-md">
              <span class="text-2xl mr-2 font-bold">+</span> Nuevo Entrenador
          </button>
      </div>

      <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex flex-wrap gap-4 items-center">
          <input type="text" id="searchInput" placeholder="Buscar..." class="pl-4 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 w-full md:w-64">
          <select id="filterEspecialidad" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 w-full md:w-auto">
              <option value="">Todas las especialidades</option>
              <option value="Fútbol Profesional">Fútbol Profesional</option>
              <option value="Fútbol Semi Profesional">Fútbol Semi Profesional</option>
              <option value="Fútbol Amateur">Fútbol Amateur</option>
          </select>
          <div class="text-sm text-gray-500 ml-auto">Total: <span id="totalEntrenadores" class="font-bold text-green-700">0</span></div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b-2 border-gray-200">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Entrenador</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Usuario</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Contacto</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Esp.</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Exp.</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Inicio</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Cat.</th>
              <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody id="tablaEntrenadores" class="bg-white divide-y divide-gray-200">
             <tr><td colspan="8" class="p-8 text-center text-gray-500">Cargando...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <div id="modalEntrenador" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] flex flex-col">
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50 rounded-t-lg">
        <h3 id="modalTitle" class="text-xl font-bold text-gray-900">Nuevo Entrenador</h3>
        <button id="btnCerrarModal" class="text-gray-400 hover:text-red-500 text-3xl font-bold leading-none">&times;</button>
      </div>
      
      <div class="overflow-y-auto p-6">
        <form id="formEntrenador">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cédula <span class="text-red-500">*</span></label>
                <input type="text" id="cedula" name="cedula" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                <input type="text" id="nombre" name="nombre" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Apellido <span class="text-red-500">*</span></label>
                <input type="text" id="apellido" name="apellido" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                <input type="tel" id="telefono" name="telefono" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Correo</label>
                <input type="email" id="correo" name="correo" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
            </div>

            <div class="md:col-span-2 bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                <h4 class="text-sm font-bold text-yellow-800 mb-3 uppercase">Acceso al Sistema</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Usuario <span class="text-red-500">*</span></label>
                        <input type="text" id="usuario" name="usuario" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Contraseña</label>
                        <input type="password" id="contrasena" name="contrasena" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white" placeholder="••••••">
                        <p id="msgPass" class="text-xs text-gray-500 mt-1">Obligatorio al crear</p>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Especialidad</label>
                <select id="especialidad" name="especialidad" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="">Seleccionar...</option>
                    <option value="Fútbol Profesional">Fútbol Profesional</option>
                    <option value="Fútbol Semi Profesional">Fútbol Semi Profesional</option>
                    <option value="Fútbol Amateur">Fútbol Amateur</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Años Exp.</label>
                <input type="number" id="experiencia_años" name="experiencia_años" min="0" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Contratación</label>
                <input type="date" id="fecha_contratacion" name="fecha_contratacion" class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
            </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
            <button type="button" id="btnCancelar" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">Cancelar</button>
            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-bold shadow-md">Guardar</button>
            </div>
        </form>
      </div>
    </div>
  </div>

  <div id="modalCategoria" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50 rounded-t-lg">
        <h3 class="text-xl font-bold text-gray-900">Asignar Categoría</h3>
        <button id="btnCerrarCat" class="text-gray-400 hover:text-red-500 text-2xl font-bold">&times;</button>
      </div>
      <form id="formCategoria" class="p-6">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Entrenador</label>
          <p id="entrenadorSeleccionado" class="text-lg text-green-700 font-bold bg-green-50 p-2 rounded"></p>
        </div>
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">Categoría *</label>
          <select id="categoria" name="categoria" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-green-500">
            <option value="">Seleccionar...</option>
          </select>
        </div>
        <div class="flex justify-end space-x-3 pt-4 border-t">
          <button type="button" id="btnCancelarCategoria" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Cancelar</button>
          <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-bold">Asignar</button>
        </div>
      </form>
    </div>
  </div>

  <script src="../recursos/js/entrenadorFrm.js?v=<?php echo time(); ?>"></script>
</body>
</html>