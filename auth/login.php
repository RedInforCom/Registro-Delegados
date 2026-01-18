<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = sanitize($_POST['usuario']);
    $contrasena = $_POST['contrasena'];
    
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT id, nombre, apellidos, usuario, contrasena, tipo_usuario FROM usuarios_sistema WHERE usuario = ? AND activo = 1");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($contrasena, $user['contrasena'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nombre'] = $user['nombre'] . ' ' . $user['apellidos'];
            $_SESSION['usuario_tipo'] = $user['tipo_usuario'];
            $_SESSION['usuario_user'] = $user['usuario'];

            // Registrar LOGIN en logs_actividad
            require_once __DIR__ . '/../includes/logger.php';
            @log_action(
                $_SESSION['usuario_id'],
                $_SESSION['usuario_tipo'],
                'LOGIN - Inicio de sesión',
                null,
                $_SESSION['usuario_nombre']
            );

            header('Location: ../dashboard.php');
            exit;
        } else {
            header('Location: ../index.php?error=credenciales');
            exit;
        }
    } else {
        header('Location: ../index.php?error=credenciales');
        exit;
    }
    
    $stmt->close();
    $conn->close();
} else {
    header('Location: ../index.php');
    exit;
}
?>