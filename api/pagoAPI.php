<?php
// api/pagoAPI.php - API COMPLETA Y CORREGIDA
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/conexion.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            obtenerPagos($conn);
            break;
        case 'POST':
            procesarPago($conn, false); // Guardar
            break;
        case 'PUT':
            procesarPago($conn, true); // Editar
            break;
        case 'DELETE':
            eliminarPagoAPI($conn); // Eliminar
            break;
        default:
            throw new Exception('Método no permitido');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

function obtenerPagos($conn) {
    $stmt = $conn->prepare("CALL obtenerHistorialPagos()");
    $stmt->execute();
    $res = $stmt->get_result();
    $data = [];
    while ($row = $res->fetch_assoc()) {
        // Convertir números para que el JS no falle
        $row['idPago'] = (int)$row['idPago'];
        $row['monto'] = (float)$row['monto'];
        $data[] = $row;
    }
    echo json_encode($data);
    $stmt->close();
}

function procesarPago($conn, $esEdicion) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) throw new Exception('Datos inválidos');

    $sp = $esEdicion ? "actualizarPago" : "registrarPago";
    
    $idJugador = $input['idJugador'];
    $fecha = $input['fechaPago'];
    $monto = $input['monto'];
    $mes = $input['mesPagado'];
    $estado = $input['estado'];

    if ($esEdicion) {
        $idPago = $input['idPago'];
        $stmt = $conn->prepare("CALL $sp(?, ?, ?, ?, ?, ?, @res, @msg)");
        $stmt->bind_param('iisdss', $idPago, $idJugador, $fecha, $monto, $mes, $estado);
    } else {
        $stmt = $conn->prepare("CALL $sp(?, ?, ?, ?, ?, @res, @msg)");
        $stmt->bind_param('isdss', $idJugador, $fecha, $monto, $mes, $estado);
    }

    ejecutarSP($stmt, $conn);
}

function eliminarPagoAPI($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['idPago'])) throw new Exception('Falta el ID del pago');

    $idPago = $input['idPago'];
    $stmt = $conn->prepare("CALL eliminarPago(?, @res, @msg)");
    $stmt->bind_param('i', $idPago);

    ejecutarSP($stmt, $conn);
}

function ejecutarSP($stmt, $conn) {
    if ($stmt->execute()) {
        $stmt->close();
        $res = $conn->query("SELECT @res as res, @msg as msg")->fetch_assoc();
        if ($res['res'] == 1) {
            echo json_encode(['success' => true, 'message' => $res['msg']]);
        } else {
            throw new Exception($res['msg']);
        }
    } else {
        throw new Exception("Error SQL ejecutando SP");
    }
}
?>