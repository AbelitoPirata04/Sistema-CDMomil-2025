<?php
// api/entrenadorAPI.php - CORREGIDO PARA EVITAR ERRORES DE FECHA
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/conexion.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['filtro']) && $_GET['filtro'] === 'especialidad') {
                obtenerEntrenadorPorEspecialidad($conn);
            } else {
                obtenerEntrenadores($conn);
            }
            break;
        case 'POST':
            crearEntrenador($conn, $input);
            break;
        case 'PUT':
            actualizarEntrenador($conn, $input);
            break;
        case 'DELETE':
            eliminarEntrenador($conn);
            break;
        case 'PATCH':
            asignarCategoria($conn, $input);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}

function obtenerEntrenadores($conn) {
    $result = $conn->query("CALL obtenerEntrenadores()");
    $entrenadores = [];
    if ($result && $result->num_rows > 0) {
        while ($fila = $result->fetch_assoc()) $entrenadores[] = $fila;
    }
    echo json_encode($entrenadores);
}

function obtenerEntrenadorPorEspecialidad($conn) {
    $esp = $_GET['especialidad'] ?? '';
    if (empty($esp)) { obtenerEntrenadores($conn); return; }
    
    $stmt = $conn->prepare("CALL obtenerEntrenadorPorEspecialidad(?)");
    $stmt->bind_param("s", $esp);
    $stmt->execute();
    $result = $stmt->get_result();
    $entrenadores = [];
    if ($result && $result->num_rows > 0) {
        while ($fila = $result->fetch_assoc()) $entrenadores[] = $fila;
    }
    echo json_encode($entrenadores);
}

// === CORRECCIÓN AQUÍ: Manejo seguro de datos vacíos ===
function crearEntrenador($conn, $data) {
    if (!$data || empty($data['cedula']) || empty($data['nombre']) || empty($data['usuario']) || empty($data['contrasena'])) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios']);
        return;
    }
    
    // 1. Encriptar contraseña
    $hash = password_hash($data['contrasena'], PASSWORD_DEFAULT);

    // 2. Limpiar Fecha (Si viene vacía "", la convertimos a NULL)
    $fecha_contratacion = !empty($data['fecha_contratacion']) ? $data['fecha_contratacion'] : null;
    
    // 3. Limpiar Teléfono y Correo (Evitar vacíos, mejor NULL)
    $telefono = !empty($data['telefono']) ? $data['telefono'] : null;
    $correo = !empty($data['correo']) ? $data['correo'] : null;
    $especialidad = !empty($data['especialidad']) ? $data['especialidad'] : null;
    $experiencia = !empty($data['experiencia_años']) ? intval($data['experiencia_años']) : 0;

    $stmt = $conn->prepare("CALL registrarEntrenadores(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @res, @msg, @id)");
    $stmt->bind_param("ssssssisss", 
        $data['cedula'], 
        $data['nombre'], 
        $data['apellido'], 
        $telefono, 
        $correo, 
        $especialidad, 
        $experiencia, 
        $fecha_contratacion, // Ahora sí envía NULL si está vacío
        $data['usuario'], 
        $hash
    );
    
    ejecutarSP($stmt, $conn);
}

function actualizarEntrenador($conn, $data) {
    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID requerido']);
        return;
    }

    $hash = !empty($data['contrasena']) ? password_hash($data['contrasena'], PASSWORD_DEFAULT) : null;

    // Limpieza de datos igual que en crear
    $fecha_contratacion = !empty($data['fecha_contratacion']) ? $data['fecha_contratacion'] : null;
    $telefono = !empty($data['telefono']) ? $data['telefono'] : null;
    $correo = !empty($data['correo']) ? $data['correo'] : null;
    $especialidad = !empty($data['especialidad']) ? $data['especialidad'] : null;
    $experiencia = !empty($data['experiencia_años']) ? intval($data['experiencia_años']) : 0;

    $stmt = $conn->prepare("CALL actualizarEntrenador(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @res, @msg)");
    $stmt->bind_param("ssssssisss", 
        $data['id'], 
        $data['nombre'], 
        $data['apellido'], 
        $telefono, 
        $correo, 
        $especialidad, 
        $experiencia, 
        $fecha_contratacion,
        $data['usuario'], 
        $hash
    );

    ejecutarSP($stmt, $conn);
}

function eliminarEntrenador($conn) {
    $id = $_GET['id'] ?? null;
    $stmt = $conn->prepare("CALL eliminarEntrenador(?, @res, @msg)");
    $stmt->bind_param("s", $id);
    ejecutarSP($stmt, $conn);
}

function asignarCategoria($conn, $data) {
    $stmt = $conn->prepare("CALL asignarCategoria(?, ?, @res, @msg)");
    $stmt->bind_param("ss", $data['id'], $data['categoria']);
    ejecutarSP($stmt, $conn);
}

function ejecutarSP($stmt, $conn) {
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Error ejecutando SP: ' . $stmt->error]);
        return;
    }
    
    $stmt->close();
    $res = $conn->query("SELECT @res as res, @msg as msg");
    $row = $res->fetch_assoc();
    
    echo json_encode([
        'success' => ($row['res'] == 1),
        'message' => $row['msg']
    ]);
}
?>