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
    $pais = sanitize($_POST['pais']);
    $ciudad = sanitize($_POST['ciudad']);
    $telefono_sin_prefijo = sanitize($_POST['telefono']);
    $telefono = getPrefijoPais($pais) . $telefono_sin_prefijo;
    $centro_estudios = capitalizarNombre(sanitize($_POST['centro_estudios']));
    $creado_por = $_SESSION['usuario_id'];
    
    // Delegado
    if ($_SESSION['usuario_tipo'] == 'delegado') {
        $delegado_id = $_SESSION['usuario_id'];
    } else {
        $delegado_id = isset($_POST['delegado_id']) ? intval($_POST['delegado_id']) : null;
        if (!$delegado_id) {
            echo json_encode(['success' => false, 'message' => 'Debe asignar un delegado']);
            exit;
        }
    }
    
    $conn = getConnection();
    
    // Verificar teléfono
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE telefono = ? UNION SELECT id FROM usuarios_sistema WHERE telefono = ?");
    $stmt->bind_param("ss", $telefono, $telefono);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El teléfono ' . $telefono . ' ya está registrado']);
        exit;
    }
    $stmt->close();
    
    // Insertar
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellidos, pais, ciudad, telefono, centro_estudios, delegado_id, creado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssii", $nombre, $apellidos, $pais, $ciudad, $telefono, $centro_estudios, $delegado_id, $creado_por);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '¡Usuario creado exitosamente!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear usuario']);
    }
    
    $stmt->close();
    $conn->close();
}
?>