<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = capitalizarNombre(sanitize($_POST['nombre']));
    $apellidos = capitalizarNombre(sanitize($_POST['apellidos']));
    $pais = sanitize($_POST['pais']);
    $ciudad = sanitize($_POST['ciudad']);
    $telefono = getPrefijoPais($pais) . sanitize($_POST['telefono']);
    $centro_estudios = capitalizarNombre(sanitize($_POST['centro_estudios']));
    $creado_por = $_SESSION['usuario_id'];
    
    // Si es delegado, asignar automáticamente
    $delegado_id = null;
    if ($_SESSION['usuario_tipo'] == 'delegado') {
        $delegado_id = $_SESSION['usuario_id'];
    }
    
    $conn = getConnection();
    
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
            window.location.href='../dashboard.php';
        });
    </script>
</body>
</html>";
        exit;
    }
    
    $stmt->close();
    
    // Insertar nuevo usuario
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellidos, pais, ciudad, telefono, centro_estudios, delegado_id, creado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssii", $nombre, $apellidos, $pais, $ciudad, $telefono, $centro_estudios, $delegado_id, $creado_por);
    
    if ($stmt->execute()) {
        echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <script src='../assets/js/notifications.js'></script>
</head>
<body>
    <script>
        mostrarExito('¡Usuario creado exitosamente!', function() {
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
        mostrarError('Error al crear usuario. Intente nuevamente.', function() {
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