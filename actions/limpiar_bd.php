<?php
session_start();
require_once '../config/database.php';

// Solo el administrador puede limpiar la BD
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'administrador') {
    header('Location: ../dashboard.php');
    exit;
}

$conn = getConnection();

// Eliminar todos los usuarios
$conn->query("DELETE FROM usuarios");

// Eliminar todos excepto el administrador
$conn->query("DELETE FROM usuarios_sistema WHERE tipo_usuario != 'administrador'");

// Resetear auto_increment
$conn->query("ALTER TABLE usuarios AUTO_INCREMENT = 1");
$conn->query("ALTER TABLE usuarios_sistema AUTO_INCREMENT = 2");

$conn->close();

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <script src='../assets/js/notifications.js'></script>
</head>
<body>
    <script>
        mostrarExito('¡Base de datos limpiada exitosamente!<br>Todos los registros han sido eliminados excepto el administrador.', function() {
            window.location.href='../dashboard.php';
        });
    </script>
</body>
</html>";
?>