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
        echo "<script>alert('El usuario ya existe. Por favor, elija otro.'); window.location.href='../index.php';</script>";
        exit;
    }
    
    $stmt->close();
    
    // Insertar nuevo delegado
    $stmt = $conn->prepare("INSERT INTO usuarios_sistema (nombre, apellidos, pais, ciudad, telefono, centro_estudios, usuario, contrasena, tipo_usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'delegado')");
    $stmt->bind_param("ssssssss", $nombre, $apellidos, $pais, $ciudad, $telefono, $centro_estudios, $usuario, $contrasena);
    
    if ($stmt->execute()) {
        echo "<script>
            alert('¡Registro exitoso!\\n\\nUsuario: " . $usuario . "\\nContraseña: (la que ingresó)\\n\\nYa puede iniciar sesión.');
            window.location.href='../index.php';
        </script>";
    } else {
        echo "<script>alert('Error al registrar. Intente nuevamente.'); window.location.href='../index.php';</script>";
    }
    
    $stmt->close();
    $conn->close();
} else {
    header('Location: ../index.php');
    exit;
}
?>