<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tipo = isset($_GET['tipo']) ? sanitize($_GET['tipo']) : '';

if ($id <= 0 || empty($tipo)) {
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
    exit;
}

$conn = getConnection();

if ($tipo == 'delegado') {
    $stmt = $conn->prepare("SELECT id, nombre, apellidos, pais, ciudad, telefono, centro_estudios, usuario FROM usuarios_sistema WHERE id = ? AND tipo_usuario = 'delegado'");
    $stmt->bind_param("i", $id);
} else {
    $stmt = $conn->prepare("SELECT id, nombre, apellidos, pais, ciudad, telefono, centro_estudios, notas FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $datos = $result->fetch_assoc();
    echo json_encode(['success' => true, 'datos' => $datos]);
} else {
    echo json_encode(['success' => false, 'message' => 'Registro no encontrado']);
}

$stmt->close();
$conn->close();
?>