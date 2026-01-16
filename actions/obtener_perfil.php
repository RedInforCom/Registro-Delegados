<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$conn = getConnection();
$stmt = $conn->prepare("SELECT nombre, apellidos, telefono, email, usuario, pais, ciudad FROM usuarios_sistema WHERE id = ?");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => true, 'datos' => $result->fetch_assoc()]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se encontraron datos']);
}

$stmt->close();
$conn->close();
?>