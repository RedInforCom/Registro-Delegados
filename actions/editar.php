<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $tipo = sanitize($_POST['tipo']);
    $nombre = capitalizarNombre(sanitize($_POST['nombre']));
    $apellidos = capitalizarNombre(sanitize($_POST['apellidos']));
    $pais = sanitize($_POST['pais']);
    $ciudad = sanitize($_POST['ciudad']);
    $telefono = getPrefijoPais($pais) . sanitize($_POST['telefono']);
    $centro_estudios = isset($_POST['centro_estudios']) ? capitalizarNombre(sanitize($_POST['centro_estudios'])) : '';
    $notas = isset($_POST['notas']) ? sanitize($_POST['notas']) : '';
    
    $conn = getConnection();
    
    // Verificar teléfono duplicado
    if ($tipo == 'delegado') {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE telefono = ? UNION SELECT id FROM usuarios_sistema WHERE telefono = ? AND id != ?");
        $stmt->bind_param("ssi", $telefono, $telefono, $id);
    } else {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE telefono = ? AND id != ? UNION SELECT id FROM usuarios_sistema WHERE telefono = ?");
        $stmt->bind_param("sis", $telefono, $id, $telefono);
    }
    
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El teléfono ya está registrado en el sistema.']);
        exit;
    }
    $stmt->close();
    
    // Actualizar según tipo
    if ($tipo == 'delegado') {
        $usuario = sanitize($_POST['usuario']);
        
        // Verificar usuario duplicado
        $stmt = $conn->prepare("SELECT id FROM usuarios_sistema WHERE usuario = ? AND id != ?");
        $stmt->bind_param("si", $usuario, $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'El usuario ya existe.']);
            exit;
        }
        $stmt->close();
        
        // Actualizar delegado
        $stmt = $conn->prepare("UPDATE usuarios_sistema SET nombre=?, apellidos=?, pais=?, ciudad=?, telefono=?, centro_estudios=?, usuario=? WHERE id=? AND tipo_usuario='delegado'");
        $stmt->bind_param("sssssssi", $nombre, $apellidos, $pais, $ciudad, $telefono, $centro_estudios, $usuario, $id);
    } else {
        // Actualizar usuario
        $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, apellidos=?, pais=?, ciudad=?, telefono=?, centro_estudios=?, notas=? WHERE id=?");
        $stmt->bind_param("sssssssi", $nombre, $apellidos, $pais, $ciudad, $telefono, $centro_estudios, $notas, $id);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Actualizado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
    }
    
    $stmt->close();
    $conn->close();
}
?>