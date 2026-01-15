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
        echo "<script>alert('El usuario ya existe. Por favor, elija otro.'); window.location.href='../dashboard.php';</script>";
        exit;
    }
    
    $stmt->close();
    
    // Insertar nuevo asesor
    $stmt = $conn->prepare("INSERT INTO usuarios_sistema (nombre, apellidos, telefono, email, usuario, contrasena, tipo_usuario) VALUES (?, ?, ?, ?, ?, ?, 'asesor')");
    $stmt->bind_param("ssssss", $nombre, $apellidos, $telefono, $email, $usuario, $contrasena);
    
    if ($stmt->execute()) {
        echo "<script>alert('¡Asesor creado exitosamente!'); window.location.href='../dashboard.php';</script>";
    } else {
        echo "<script>alert('Error al crear asesor. Intente nuevamente.'); window.location.href='../dashboard.php';</script>";
    }
    
    $stmt->close();
    $conn->close();
} else {
    header('Location: ../dashboard.php');
    exit;
}
?>