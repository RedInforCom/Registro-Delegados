<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = capitalizarNombre(sanitize($_POST['nombre']));
    $apellidos = capitalizarNombre(sanitize($_POST['apellidos']));
    $telefono = sanitize($_POST['telefono']);
    $email = sanitize($_POST['email']);
    $usuario = sanitize($_POST['usuario']);
    $pais = isset($_POST['pais']) ? sanitize($_POST['pais']) : null;
    $ciudad = isset($_POST['ciudad']) ? sanitize($_POST['ciudad']) : null;
    
    $conn = getConnection();
    
    // Verificar usuario duplicado
    $stmt = $conn->prepare("SELECT id FROM usuarios_sistema WHERE usuario = ? AND id != ?");
    $stmt->bind_param("si", $usuario, $_SESSION['usuario_id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El usuario ' . $usuario . ' ya existe']);
        exit;
    }
    $stmt->close();
    
    // Verificar teléfono duplicado
    $stmt = $conn->prepare("SELECT id FROM usuarios_sistema WHERE telefono = ? AND id != ?");
    $stmt->bind_param("si", $telefono, $_SESSION['usuario_id']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El teléfono ' . $telefono . ' ya está registrado']);
        exit;
    }
    $stmt->close();
    
    // Actualizar
    if (!empty($_POST['contrasena'])) {
        $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios_sistema SET nombre=?, apellidos=?, telefono=?, email=?, usuario=?, contrasena=?, pais=?, ciudad=? WHERE id=?");
        $stmt->bind_param("ssssssssi", $nombre, $apellidos, $telefono, $email, $usuario, $contrasena, $pais, $ciudad, $_SESSION['usuario_id']);
    } else {
        $stmt = $conn->prepare("UPDATE usuarios_sistema SET nombre=?, apellidos=?, telefono=?, email=?, usuario=?, pais=?, ciudad=? WHERE id=?");
        $stmt->bind_param("sssssssi", $nombre, $apellidos, $telefono, $email, $usuario, $pais, $ciudad, $_SESSION['usuario_id']);
    }
    
    if ($stmt->execute()) {
        $_SESSION['usuario_nombre'] = $nombre . ' ' . $apellidos;
        $_SESSION['usuario_user'] = $usuario;
        echo json_encode(['success' => true, 'message' => 'Perfil actualizado']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
    }
    
    $stmt->close();
    $conn->close();
}
?>