<?php
session_start();

// Registrar LOGOUT en logs_actividad antes de destruir la sesión
if (isset($_SESSION['usuario_id'])) {
    require_once __DIR__ . '/../includes/logger.php';
    @log_action(
        $_SESSION['usuario_id'] ?? null,
        $_SESSION['usuario_tipo'] ?? 'usuario',
        'LOGOUT - Cierre de sesión',
        null,
        $_SESSION['usuario_nombre'] ?? null
    );
}

session_destroy();
header('Location: ../index.php');
exit;
?>