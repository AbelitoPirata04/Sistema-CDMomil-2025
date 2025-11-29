<?php
session_start();
include("../config/conexion.php"); // Tu archivo de conexión a la BD

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    // Validar campos vacíos
    if (empty($usuario) || empty($contrasena)) {
        $_SESSION['login_error'] = 'Por favor, completa todos los campos.';
        header('Location: ../views/index.php');
        exit();
    }

    // --- INTENTO 1: INICIAR SESIÓN COMO SUPER ADMINISTRADOR (Tabla administradores) ---
    $stmt_admin = $conn->prepare("SELECT id_admin, usuario, contrasena FROM administradores WHERE usuario = ? LIMIT 1");
    $stmt_admin->bind_param("s", $usuario);
    $stmt_admin->execute();
    $resultado_admin = $stmt_admin->get_result();
    $admin = $resultado_admin->fetch_assoc();
    $stmt_admin->close();

    if ($admin && password_verify($contrasena, $admin['contrasena'])) {
        // ¡ÉXITO! ES UN SUPER ADMIN
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id_admin'];
        $_SESSION['admin_usuario'] = $admin['usuario'];
        $_SESSION['rol'] = 'SuperAdmin'; // Opcional: para diferenciar

        header("Location: ../views/indexAdmin.php"); // Al Dashboard
        exit();
    } 

    // --- INTENTO 2: INICIAR SESIÓN COMO ENTRENADOR (Tabla entrenador) ---
    // (Esto es lo nuevo que faltaba)
    $stmt_entrenador = $conn->prepare("SELECT id_entrenador, usuario, contrasena, nombre, apellido FROM entrenador WHERE usuario = ? LIMIT 1");
    $stmt_entrenador->bind_param("s", $usuario);
    $stmt_entrenador->execute();
    $resultado_entrenador = $stmt_entrenador->get_result();
    $entrenador = $resultado_entrenador->fetch_assoc();
    $stmt_entrenador->close();

    if ($entrenador && password_verify($contrasena, $entrenador['contrasena'])) {
        // ¡ÉXITO! ES UN ENTRENADOR (Tiene permisos de Admin)
        $_SESSION['admin_logged_in'] = true; // Lo marcamos como logueado en admin
        $_SESSION['admin_id'] = $entrenador['id_entrenador'];
        $_SESSION['admin_usuario'] = $entrenador['usuario']; // O puedes poner $entrenador['nombre']
        $_SESSION['rol'] = 'Entrenador';

        header("Location: ../views/indexAdmin.php"); // Al mismo Dashboard de Admin
        exit();
    }
    
    // --- INTENTO 3: INICIAR SESIÓN COMO JUGADOR (Tabla jugador) ---
    $stmt_jugador = $conn->prepare("SELECT id_jugador, usuario, contrasena, nombre, apellido FROM jugador WHERE usuario = ? LIMIT 1");
    $stmt_jugador->bind_param("s", $usuario);
    $stmt_jugador->execute();
    $resultado_jugador = $stmt_jugador->get_result();
    $jugador = $resultado_jugador->fetch_assoc();
    $stmt_jugador->close();
    
    if ($jugador && password_verify($contrasena, $jugador['contrasena'])) {
        // ¡ÉXITO! ES UN JUGADOR
        $_SESSION['jugador_logged_in'] = true;
        $_SESSION['jugador_id'] = $jugador['id_jugador'];
        $_SESSION['jugador_usuario'] = $jugador['usuario'];
        $_SESSION['jugador_nombre'] = $jugador['nombre'] . ' ' . $jugador['apellido'];

        // Redirigir al perfil del jugador
        header("Location: ../views/indexJugadorPerfil.php"); 
        exit();
    }

    // --- FRACASO: NINGUNO DE LOS TRES ---
    $conn->close();
    $_SESSION['login_error'] = 'Usuario o contraseña incorrectos.';
    header('Location: ../views/index.php');
    exit();

} else {
    // Si alguien intenta acceder directamente
    header('Location: ../views/index.php');
    exit();
}
?>