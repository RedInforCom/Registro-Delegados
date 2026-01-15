<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = capitalizarNombre(sanitize($_POST['nombre']));
    $apellidos = capitalizarNombre(sanitize($_POST['apellidos']));
    $pais = sanitize($_POST['pais']);
    $ciudad = sanitize($_POST['ciudad']);
    $telefono = getPrefijoPais($pais) . sanitize($_POST['telefono']);
    $centro_estudios = capitalizarNombre(sanitize($_POST['centro_estudios']));
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
            window.location.href='../index.php';
        });
    </script>
</body>
</html>";
        exit;
    }
    
    $stmt->close();
    
    // Verificar si el teléfono ya existe
    $telefono_completo = getPrefijoPais($pais) . sanitize($_POST['telefono']);
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE telefono = ? UNION SELECT id FROM usuarios_sistema WHERE telefono = ?");
    $stmt->bind_param("ss", $telefono_completo, $telefono_completo);
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
            window.location.href='../index.php';
        });
    </script>
</body>
</html>";
        exit;
    }
    
    $stmt->close();
    
    // Insertar nuevo delegado
    $stmt = $conn->prepare("INSERT INTO usuarios_sistema (nombre, apellidos, pais, ciudad, telefono, centro_estudios, usuario, contrasena, tipo_usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'delegado')");
    $stmt->bind_param("ssssssss", $nombre, $apellidos, $pais, $ciudad, $telefono, $centro_estudios, $usuario, $contrasena);
    
    if ($stmt->execute()) {
        echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <script src='../assets/js/notifications.js'></script>
</head>
<body>
    <script>
        mostrarExito('¡Registro exitoso!<br><br><strong>Usuario:</strong> " . $usuario . "<br>Ya puede iniciar sesión con su contraseña.', function() {
            window.location.href='../index.php';
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
        mostrarError('Error al registrar. Intente nuevamente.', function() {
            window.location.href='../index.php';
        });
    </script>
</body>
</html>";
    }
    
    $stmt->close();
    $conn->close();
} else {
    header('Location: ../index.php');
    exit;
}
?>