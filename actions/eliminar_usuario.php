<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] == 'delegado') {
    header('Location: ../dashboard.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'usuario';
    
    $conn = getConnection();
    
    if ($tipo == 'delegado') {
        // Verificar si el delegado tiene usuarios asignados
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM usuarios WHERE delegado_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['total'] > 0) {
            echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <script src='../assets/js/notifications.js'></script>
</head>
<body>
    <script>
        mostrarAdvertencia('Este delegado tiene " . $row['total'] . " usuario(s) asignado(s). No se puede eliminar hasta que reasigne o elimine los usuarios primero.', function() {
            window.location.href='../dashboard.php';
        });
    </script>
</body>
</html>";
            exit;
        }
        
        $stmt->close();
        
        // Desactivar delegado en lugar de eliminarlo
        $stmt = $conn->prepare("UPDATE usuarios_sistema SET activo = 0 WHERE id = ? AND tipo_usuario = 'delegado'");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <script src='../assets/js/notifications.js'></script>
</head>
<body>
    <script>
        mostrarExito('Delegado desactivado exitosamente. Ya no tiene acceso al sistema.', function() {
            window.location.href='../dashboard.php';
        });
    </script>
</body>
</html>";
        } else {
            echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <script src='../assets/js/notifications.js'></script>
</head>
<body>
    <script>
        mostrarError('Error al desactivar delegado.', function() {
            window.location.href='../dashboard.php';
        });
    </script>
</body>
</html>";
        }
        
    } else {
        // Eliminar usuario
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <script src='../assets/js/notifications.js'></script>
</head>
<body>
    <script>
        mostrarExito('Usuario eliminado exitosamente.', function() {
            window.location.href='../dashboard.php';
        });
    </script>
</body>
</html>";
        } else {
            echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <script src='../assets/js/notifications.js'></script>
</head>
<body>
    <script>
        mostrarError('Error al eliminar usuario.', function() {
            window.location.href='../dashboard.php';
        });
    </script>
</body>
</html>";
        }
    }
    
    $stmt->close();
    $conn->close();
    
} else {
    header('Location: ../dashboard.php');
    exit;
}
?>