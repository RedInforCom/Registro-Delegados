<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$conn = getConnection();

if ($_SESSION['usuario_tipo'] == 'delegado') {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios WHERE delegado_id = ?");
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
} else {
    $delegados = $conn->query("SELECT COUNT(*) as t FROM usuarios_sistema WHERE tipo_usuario = 'delegado'")->fetch_assoc()['t'];
    $usuarios = $conn->query("SELECT COUNT(*) as t FROM usuarios")->fetch_assoc()['t'];
    $total = $delegados + $usuarios;
}

echo json_encode(['success' => true, 'total' => $total]);
$conn->close();
?>