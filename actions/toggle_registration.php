<?php
// actions/toggle_registration.php
// Endpoint admin-only que activa/desactiva el botón "Registro de Delegados".
// Guarda la clave 'registro_delegados' en la tabla `settings` (crea la tabla si no existe).
// Espera POST { enable: '1'|'0' } y devuelve JSON.

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../config/database.php';

function respond($code, $payload) {
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

try {
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'administrador') {
        respond(403, ['success' => false, 'message' => 'No autorizado.']);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respond(405, ['success' => false, 'message' => 'Método no permitido. Use POST.']);
    }

    $enable = isset($_POST['enable']) ? ($_POST['enable'] === '1' ? '1' : '0') : '0';

    $conn = getConnection();
    if (!$conn) respond(500, ['success' => false, 'message' => 'Error de conexión a la base de datos.']);

    // Crear tabla settings si no existe
    $create = "CREATE TABLE IF NOT EXISTS `settings` (
        `k` VARCHAR(100) NOT NULL PRIMARY KEY,
        `v` VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $conn->query($create);

    // Upsert value
    $stmt = $conn->prepare("INSERT INTO `settings` (`k`, `v`) VALUES ('registro_delegados', ?) ON DUPLICATE KEY UPDATE `v` = VALUES(`v`)");
    if (!$stmt) {
        respond(500, ['success' => false, 'message' => 'Error al preparar la consulta.']);
    }
    $stmt->bind_param('s', $enable);
    if (!$stmt->execute()) {
        $stmt->close();
        respond(500, ['success' => false, 'message' => 'Error al guardar la configuración.']);
    }
    $stmt->close();

    // Log
    $adminId = intval($_SESSION['usuario_id']);
    $logDir = __DIR__ . '/../storage';
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
    @file_put_contents($logDir . '/settings.log', date('c') . " - admin_id={$adminId} set registro_delegados={$enable}\n", FILE_APPEND | LOCK_EX);

    respond(200, ['success' => true, 'message' => ($enable === '1' ? 'Registro público habilitado.' : 'Registro público deshabilitado.'), 'value' => $enable]);
} catch (Exception $ex) {
    respond(500, ['success' => false, 'message' => 'Excepción: ' . $ex->getMessage()]);
}