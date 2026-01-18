<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'administrador') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = capitalizarNombre(sanitize($_POST['nombre']));
    $apellidos = capitalizarNombre(sanitize($_POST['apellidos']));
    $pais = sanitize($_POST['pais']);
    $ciudad = sanitize($_POST['ciudad']);
    $telefono_sin_prefijo = sanitize($_POST['telefono']);
    $telefono = getPrefijoPais($pais) . $telefono_sin_prefijo;
    $email = sanitize($_POST['email']);
    $usuario = sanitize($_POST['usuario']);
    $contrasena_plain = $_POST['contrasena'];
    $contrasena = password_hash($contrasena_plain, PASSWORD_DEFAULT);
    
    $conn = getConnection();
    
    // Verificar usuario
    $stmt = $conn->prepare("SELECT id FROM usuarios_sistema WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El usuario ' . $usuario . ' ya existe']);
        exit;
    }
    $stmt->close();
    
    // Verificar teléfono
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE telefono = ? UNION SELECT id FROM usuarios_sistema WHERE telefono = ?");
    $stmt->bind_param("ss", $telefono, $telefono);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El teléfono ' . $telefono . ' ya está registrado']);
        exit;
    }
    $stmt->close();
    
    // Verificar email
    $stmt = $conn->prepare("SELECT id FROM usuarios_sistema WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El email ' . $email . ' ya está registrado']);
        exit;
    }
    $stmt->close();
    
    // Insertar
    $stmt = $conn->prepare("INSERT INTO usuarios_sistema (nombre, apellidos, pais, ciudad, telefono, email, usuario, contrasena, tipo_usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'asesor')");
    $stmt->bind_param("ssssssss", $nombre, $apellidos, $pais, $ciudad, $telefono, $email, $usuario, $contrasena);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '¡Asesor creado exitosamente!', 'usuario' => $usuario, 'contrasena' => $contrasena_plain]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear asesor']);
    }
    
    $stmt->close();
    $conn->close();
}
?>