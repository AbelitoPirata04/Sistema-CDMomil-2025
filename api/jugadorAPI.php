<?php
// jugadorAPI.php - API para manejar jugadores
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/conexion.php';

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (isset($_GET['id_categoria'])) { 
                obtenerJugadoresPorCategoriaAPI($conn, $_GET['id_categoria']);
            
            } elseif (isset($_GET['id'])) { // <<< NUEVA LÓGICA PARA PERFIL
                obtenerPerfilJugador($conn); 
            
            } elseif (isset($_GET['filtrar'])) { 
                busquedafiltradaJugadores($conn);
            
            } else { 
                obtenerJugadores($conn);
            }
            break;
        case 'POST':
            registarJugador($conn); // Nombre de función corregido
            break;
        case 'PUT':
            actualizarJugador($conn);
            break;
        case 'DELETE':
            eliminarJugador($conn);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}

// ===============================================================
// FUNCIÓN NUEVA PARA OBTENER EL PERFIL COMPLETO
// ===============================================================
function obtenerPerfilJugador($conn) {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de jugador no proporcionado']);
        exit;
    }
    
    $id_jugador = intval($_GET['id']);

    try {
        $stmt = $conn->prepare("CALL obtenerPerfilCompletoJugador(?)"); 
        $stmt->bind_param("i", $id_jugador);
        $stmt->execute();
        
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $jugador = $resultado->fetch_assoc();
            
            // Convertir tipos de datos para JSON
            $jugador['id_jugador'] = intval($jugador['id_jugador']);
            $jugador['edad'] = intval($jugador['edad']);
            $jugador['altura'] = floatval($jugador['altura']);
            $jugador['peso'] = floatval($jugador['peso']);
            $jugador['dorsal'] = intval($jugador['dorsal']);
            $jugador['goles_totales'] = intval($jugador['goles_totales']);
            $jugador['tarjetas_amarillas'] = intval($jugador['tarjetas_amarillas']);
            $jugador['tarjetas_rojas'] = intval($jugador['tarjetas_rojas']);
            $jugador['partidos_jugados'] = intval($jugador['partidos_jugados']);
            $jugador['minutos_jugados'] = intval($jugador['minutos_jugados']);

            echo json_encode($jugador, JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Jugador no encontrado']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al obtener el perfil: ' . $e->getMessage()]);
    } finally {
        if (isset($stmt)) {
            $stmt->close(); 
        }
    }
}

// ===============================================================
// FUNCIONES GET EXISTENTES
// ===============================================================

function obtenerJugadoresPorCategoriaAPI($conn, $id_categoria) {
    try {
        $stmt = $conn->prepare("CALL obtenerJugadoresPorCategoria(?)"); 
        $stmt->bind_param("i", $id_categoria);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $jugadores = [];
        while ($fila = $resultado->fetch_assoc()) {
            $jugadores[] = $fila;
        }
        echo json_encode($jugadores, JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al obtener jugadores por categoría: ' . $e->getMessage()]);
    } finally {
        if (isset($stmt)) {
            $stmt->close(); 
        }
    }
}

function busquedafiltradaJugadores($conn) {
    try {
        $busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : null;
        $posicion = isset($_GET['posicion']) ? $_GET['posicion'] : null;
        $genero = isset($_GET['genero']) ? $_GET['genero'] : null;
        $categoria = isset($_GET['categoria']) ? $_GET['categoria'] : null;
        
        $stmt = $conn->prepare("CALL busquedafiltradaJugadores(?, ?, ?, ?)");
        $stmt->bind_param('ssss', $busqueda, $posicion, $genero, $categoria);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $jugadores = [];
        while ($fila = $resultado->fetch_assoc()) {
            if ($fila['altura']) { $fila['altura'] = floatval($fila['altura']); }
            if ($fila['peso']) { $fila['peso'] = floatval($fila['peso']); }
            if ($fila['dorsal']) { $fila['dorsal'] = intval($fila['dorsal']); }
            if ($fila['edad']) { $fila['edad'] = intval($fila['edad']); }
            $fila['usuario'] = $fila['usuario'] ?? null; // <<< MODIFICACIÓN: Enviar usuario a la lista
            $jugadores[] = $fila;
        }
        
        echo json_encode($jugadores, JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al filtrar jugadores: ' . $e->getMessage()]);
    } finally {
        if (isset($stmt)) {
            $stmt->close(); 
        }
    }
}

function obtenerJugadores($conn) {
    try {
        $stmt = $conn->prepare("CALL obtenerJugadores()");
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $jugadores = [];
        while ($fila = $resultado->fetch_assoc()) {
            if ($fila['altura']) { $fila['altura'] = floatval($fila['altura']); }
            if ($fila['peso']) { $fila['peso'] = floatval($fila['peso']); }
            if ($fila['dorsal']) { $fila['dorsal'] = intval($fila['dorsal']); }
            if ($fila['edad']) { $fila['edad'] = intval($fila['edad']); }
            $fila['usuario'] = $fila['usuario'] ?? null; // <<< MODIFICACIÓN: Enviar usuario a la lista
            $jugadores[] = $fila;
        }
        
        echo json_encode($jugadores, JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al obtener jugadores: ' . $e->getMessage()]);
    } finally {
        if (isset($stmt)) {
            $stmt->close(); 
        }
    }
}

// ===============================================================
// FUNCIONES POST, PUT, DELETE (MODIFICADAS)
// ===============================================================

// --- FUNCIÓN REGISTRAR JUGADOR MODIFICADA ---
function registarJugador($conn) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) { throw new Exception('No se recibieron datos válidos'); }
        if (empty($input['nombre']) || empty($input['apellido'])) { throw new Exception('El nombre y apellido son obligatorios'); }
        
        // Datos existentes
        $cedula = !empty($input['cedula']) ? $input['cedula'] : null;
        $nombre = trim($input['nombre']);
        $apellido = trim($input['apellido']);
        $fecha_nacimiento = !empty($input['fecha_nacimiento']) ? $input['fecha_nacimiento'] : null;
        $edad = !empty($input['edad']) ? intval($input['edad']) : null;
        $altura = !empty($input['altura']) ? floatval($input['altura']) : null;
        $peso = !empty($input['peso']) ? floatval($input['peso']) : null;
        $telefono = !empty($input['telefono']) ? $input['telefono'] : null;
        $posicion = !empty($input['posicion']) ? $input['posicion'] : null;
        $genero = !empty($input['genero']) ? $input['genero'] : null;
        $dorsal = !empty($input['dorsal']) ? intval($input['dorsal']) : null;
        $fecha_ingreso = !empty($input['fecha_ingreso']) ? $input['fecha_ingreso'] : null;
        $id_categoria = !empty($input['id_categoria']) ? intval($input['id_categoria']) : null;
        
        // Nuevos campos de credenciales
        $usuario = !empty($input['usuario']) ? trim($input['usuario']) : null;
        $contrasena = !empty($input['contrasena']) ? trim($input['contrasena']) : null;
        $contrasena_hash = null;

        // Lógica de validación y encriptación
        if ($usuario) {
            if (empty($contrasena)) {
                throw new Exception('Si proporciona un usuario, la contraseña es obligatoria');
            }
            if (strlen($contrasena) < 6) {
                throw new Exception('La contraseña debe tener al menos 6 caracteres');
            }
            // ¡ENCRIPTAR!
            $contrasena_hash = password_hash($contrasena, PASSWORD_BCRYPT);
        
        } elseif ($contrasena) {
            throw new Exception('No se puede asignar una contraseña sin un nombre de usuario');
        }
        
        $stmt = $conn->prepare("CALL registrarJugador (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @resultado, @mensaje, @id_nuevo_jugador)");
        $stmt->bind_param(
            'ssssidddisisi' . 'ss', // 13 params + 2 nuevos
            $cedula, $nombre, $apellido, $fecha_nacimiento, $edad, $altura, $peso,
            $telefono, $posicion, $genero, $dorsal, $fecha_ingreso, $id_categoria,
            $usuario, $contrasena_hash // Nuevas variables
        );
        
        if ($stmt->execute()) {
            $stmt->close();
            $result = $conn->query("SELECT @resultado AS resultado, @mensaje AS mensaje, @id_nuevo_jugador AS id_nuevo_jugador");
            $output = $result->fetch_assoc();
            
            if ($output['resultado'] == 1) {
                echo json_encode([
                    'success' => true,
                    'message' => $output['mensaje'],
                    'id_jugador' => intval($output['id_nuevo_jugador'])
                ], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception($output['mensaje']);
            }
        } else {
            throw new Exception('Error al ejecutar el procedimiento almacenado');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

// --- FUNCIÓN ACTUALIZAR JUGADOR MODIFICADA ---
function actualizarJugador($conn) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['id_jugador'])) { throw new Exception('ID del jugador es requerido'); }
        
        $id_jugador = intval($input['id_jugador']);
        if (empty($input['nombre']) || empty($input['apellido'])) { throw new Exception('El nombre y apellido son obligatorios'); }
        
        $cedula = !empty($input['cedula']) ? $input['cedula'] : null;
        $nombre = trim($input['nombre']);
        $apellido = trim($input['apellido']);
        $fecha_nacimiento = !empty($input['fecha_nacimiento']) ? $input['fecha_nacimiento'] : null;
        $edad = !empty($input['edad']) ? intval($input['edad']) : null;
        $altura = !empty($input['altura']) ? floatval($input['altura']) : null;
        $peso = !empty($input['peso']) ? floatval($input['peso']) : null;
        $telefono = !empty($input['telefono']) ? $input['telefono'] : null;
        $posicion = !empty($input['posicion']) ? $input['posicion'] : null;
        $genero = !empty($input['genero']) ? $input['genero'] : null;
        $dorsal = !empty($input['dorsal']) ? intval($input['dorsal']) : null;
        $fecha_ingreso = !empty($input['fecha_ingreso']) ? $input['fecha_ingreso'] : null;
        $id_categoria = !empty($input['id_categoria']) ? intval($input['id_categoria']) : null;
        
        // Nuevos campos de credenciales
        $usuario = !empty($input['usuario']) ? trim($input['usuario']) : null;
        $contrasena = !empty($input['contrasena']) ? trim($input['contrasena']) : null;
        $contrasena_hash = null;

        // Lógica de validación y encriptación (SOLO SI SE PROVEE NUEVA CONTRASEÑA)
        if ($contrasena) {
            if (strlen($contrasena) < 6) {
                throw new Exception('La nueva contraseña debe tener al menos 6 caracteres');
            }
            $contrasena_hash = password_hash($contrasena, PASSWORD_BCRYPT);
        
        } elseif ($usuario && !isset($input['id_jugador'])) { 
            // (Esta lógica está más segura en la función de registrar)
        }
        
        $stmt = $conn->prepare("CALL actualizarJugador(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @resultado, @mensaje)");
        $stmt->bind_param(
            'issssisssssisi' . 'ss', // 14 params + 2 nuevos
            $id_jugador, $cedula, $nombre, $apellido, $fecha_nacimiento, $edad, $altura, $peso,
            $telefono, $posicion, $genero, $dorsal, $fecha_ingreso, $id_categoria,
            $usuario, $contrasena_hash // Nuevas variables
        );
        
        if ($stmt->execute()) {
            $stmt->close();
            $result = $conn->query("SELECT @resultado AS resultado, @mensaje AS mensaje");
            $output = $result->fetch_assoc();
            
            if ($output['resultado'] == 1) {
                echo json_encode([
                    'success' => true,
                    'message' => $output['mensaje']
                ], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception($output['mensaje']);
            }
        } else {
            throw new Exception('Error al ejecutar el procedimiento almacenado');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

function eliminarJugador($conn) {
    try {
        if (!isset($_GET['id']) || empty($_GET['id'])) { throw new Exception('ID del jugador es requerido'); }
        
        $id_jugador = intval($_GET['id']);
        
        $stmt = $conn->prepare("CALL eliminarJugador(?, @resultado, @mensaje)");
        $stmt->bind_param('i', $id_jugador);
        
        if ($stmt->execute()) {
            $stmt->close();
            $result = $conn->query("SELECT @resultado AS resultado, @mensaje AS mensaje");
            $output = $result->fetch_assoc();
            
            if ($output['resultado'] == 1) {
                echo json_encode([
                    'success' => true,
                    'message' => $output['mensaje']
                ], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception($output['mensaje']);
            }
        } else {
            throw new Exception('Error al ejecutar el procedimiento almacenado');
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}
?>