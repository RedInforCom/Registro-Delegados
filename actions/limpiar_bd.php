<?php
// actions/limpiar_bd.php
// Versión mínima: exige POST con confirm="ELIMINAR" y sesión admin.
// Borra registros como pediste y devuelve JSON.

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../config/database.php';

function respond($code, $payload) {
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

try {
    // Autorización
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'administrador') {
        respond(403, ['success' => false, 'message' => 'No autorizado.']);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respond(405, ['success' => false, 'message' => 'Método no permitido. Use POST.']);
    }

    $confirm = isset($_POST['confirm']) ? trim($_POST['confirm']) : '';
    if ($confirm !== 'ELIMINAR') {
        respond(400, ['success' => false, 'message' => 'Confirmación inválida. Escribe ELIMINAR para confirmar.']);
    }

    $conn = getConnection();
    if (!$conn) {
        respond(500, ['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    }

    // Desactivar FK temporalmente
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");

    // Ejecutar las mismas operaciones que tu script original
    if (!$conn->query("DELETE FROM `usuarios`")) {
        $err = $conn->error;
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        respond(500, ['success' => false, 'message' => "Error al eliminar registros de usuarios: $err"]);
    }

    if (!$conn->query("DELETE FROM `usuarios_sistema` WHERE tipo_usuario != 'administrador'")) {
        $err = $conn->error;
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        respond(500, ['success' => false, 'message' => "Error al limpiar usuarios_sistema: $err"]);
    }

    // Determinar siguiente AUTO_INCREMENT para usuarios_sistema (max(id)+1)
    $res = $conn->query("SELECT COALESCE(MAX(id),0) AS m FROM `usuarios_sistema`");
    $mrow = $res ? $res->fetch_assoc() : null;
    $nextUsuariosSistema = ($mrow ? intval($mrow['m']) : 0) + 1;
    if ($nextUsuariosSistema < 2) $nextUsuariosSistema = 2;

    // Resetear AUTO_INCREMENT
    $conn->query("ALTER TABLE `usuarios` AUTO_INCREMENT = 1");
    $conn->query("ALTER TABLE `usuarios_sistema` AUTO_INCREMENT = " . $nextUsuariosSistema);

    // Reactivar FK
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");

    // Log de la acción (opcional)
    $adminId = intval($_SESSION['usuario_id']);
    $logDir = __DIR__ . '/../storage';
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
    @file_put_contents($logDir . '/limpiar_bd.log', date('c') . " - Limpiar BD ejecutado por admin_id={$adminId}\n", FILE_APPEND | LOCK_EX);

    respond(200, ['success' => true, 'message' => '¡Base de datos limpiada exitosamente! Todos los registros han sido eliminados excepto los administradores.']);
} catch (Exception $ex) {
    respond(500, ['success' => false, 'message' => 'Excepción: ' . $ex->getMessage()]);
}