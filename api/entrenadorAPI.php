<?php
// entrenadorAPI.php
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
    $especialidad = $_GET['especialidad'] ?? '';
    if (empty($especialidad)) { obtenerEntrenadores($conn); return; }
    
    $stmt = $conn->prepare("CALL obtenerEntrenadorPorEspecialidad(?)");
    $stmt->bind_param("s", $especialidad);
    $stmt->execute();
    $result = $stmt->get_result();
    $entrenadores = [];
    if ($result && $result->num_rows > 0) {
        while ($fila = $result->fetch_assoc()) $entrenadores[] = $fila;
    }
    echo json_encode($entrenadores);
}

function crearEntrenador($conn, $data) {
    if (!$data || empty($data['cedula']) || empty($data['nombre']) || empty($data['usuario']) || empty($data['contrasena'])) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios']);
        return;
    }
    
    // Encriptar contraseña
    $hash = password_hash($data['contrasena'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("CALL registrarEntrenadores(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @res, @msg, @id)");
    $stmt->bind_param("ssssssisss", 
        $data['cedula'], $data['nombre'], $data['apellido'], 
        $data['telefono'], $data['correo'], $data['especialidad'], 
        $data['experiencia_años'], $data['fecha_contratacion'],
        $data['usuario'], $hash
    );
    $stmt->execute();
    
    $result = $conn->query("SELECT @res as res, @msg as msg, @id as id");
    $row = $result->fetch_assoc();
    
    echo json_encode(['success' => ($row['res'] == 1), 'message' => $row['msg'], 'id' => $row['id']]);
}

function actualizarEntrenador($conn, $data) {
    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID requerido']);
        return;
    }

    // Hash solo si hay contraseña nueva
    $hash = !empty($data['contrasena']) ? password_hash($data['contrasena'], PASSWORD_DEFAULT) : null;

    $stmt = $conn->prepare("CALL actualizarEntrenador(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @res, @msg)");
    $stmt->bind_param("ssssssisss", 
        $data['id'], $data['nombre'], $data['apellido'], 
        $data['telefono'], $data['correo'], $data['especialidad'], 
        $data['experiencia_años'], $data['fecha_contratacion'],
        $data['usuario'], $hash
    );
    $stmt->execute();
    
    $result = $conn->query("SELECT @res as res, @msg as msg");
    $row = $result->fetch_assoc();
    
    echo json_encode(['success' => ($row['res'] == 1), 'message' => $row['msg']]);
}

function eliminarEntrenador($conn) {
    $id = $_GET['id'] ?? null;
    $stmt = $conn->prepare("CALL eliminarEntrenador(?, @res, @msg)");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    
    $result = $conn->query("SELECT @res as res, @msg as msg");
    $row = $result->fetch_assoc();
    
    echo json_encode(['success' => ($row['res'] == 1), 'message' => $row['msg']]);
}

function asignarCategoria($conn, $data) {
    $stmt = $conn->prepare("CALL asignarCategoria(?, ?, @res, @msg)");
    $stmt->bind_param("ss", $data['id'], $data['categoria']);
    $stmt->execute();
    
    $result = $conn->query("SELECT @res as res, @msg as msg");
    $row = $result->fetch_assoc();
    
    echo json_encode(['success' => ($row['res'] == 1), 'message' => $row['msg']]);
}
?>