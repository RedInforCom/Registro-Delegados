<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'administrador') {
    header('Location: ../dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = capitalizarNombre(sanitize($_POST['nombre']));
    $apellidos = capitalizarNombre(sanitize($_POST['apellidos']));
    $telefono = sanitize($_POST['telefono']);
    $email = sanitize($_POST['email']);
    $usuario = sanitize($_POST['usuario']);
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    
    $conn = getConnection();
    
    // Verificar si el usuario ya existe
    $stmt = $conn->prepare("SELECT id FROM usuarios_sistema WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <script src='../assets/js/notifications.js'></script>
</head>
<body>
    <script>
        mostrarError('El usuario ya existe. Por favor, elija otro.', function() {
            window.location.href='../dashboard.php';
        });
    </script>
</body>
</html>";
        exit;
    }
    
    $stmt->close();
    
    // Verificar si el teléfono ya existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE telefono = ? UNION SELECT id FROM usuarios_sistema WHERE telefono = ?");
    $stmt->bind_param("ss", $telefono, $telefono);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <script src='../assets/js/notifications.js'></script>
</head>
<body>
    <script>
        mostrarError('El teléfono ya está registrado en el sistema.', function() {
            window.location.href='../dashboard.php';
        });
    </script>
</body>
</html>";
        exit;
    }
    
    $stmt->close();
    
    // Insertar nuevo asesor
    $stmt = $conn->prepare("INSERT INTO usuarios_sistema (nombre, apellidos, telefono, email, usuario, contrasena, tipo_usuario) VALUES (?, ?, ?, ?, ?, ?, 'asesor')");
    $stmt->bind_param("ssssss", $nombre, $apellidos, $telefono, $email, $usuario, $contrasena);
    
    if ($stmt->execute()) {
        echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <script src='../assets/js/notifications.js'></script>
</head>
<body>
    <script>
        mostrarExito('¡Asesor creado exitosamente!', function() {
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
        mostrarError('Error al crear asesor. Intente nuevamente.', function() {
            window.location.href='../dashboard.php';
        });
    </script>
</body>
</html>";
    }
    
    $stmt->close();
    $conn->close();
} else {
    header('Location: ../dashboard.php');
    exit;
}
?>