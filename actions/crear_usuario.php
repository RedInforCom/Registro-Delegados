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
    
    // Determinar el delegado asignado
    $delegado_id = null;
    
    if ($_SESSION['usuario_tipo'] == 'delegado') {
        // Si es delegado, se asigna automáticamente a sí mismo
        $delegado_id = $_SESSION['usuario_id'];
    } else {
        // Si es admin o asesor, toman el valor del formulario
        if (isset($_POST['delegado_id']) && !empty($_POST['delegado_id'])) {
            $delegado_id = intval($_POST['delegado_id']);
        }
    }
    
    $conn = getConnection();
    
    // Insertar nuevo usuario
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellidos, pais, ciudad, telefono, centro_estudios, delegado_id, creado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssii", $nombre, $apellidos, $pais, $ciudad, $telefono, $centro_estudios, $delegado_id, $creado_por);
    
    if ($stmt->execute()) {
        echo "<script>alert('¡Usuario creado exitosamente!'); window.location.href='../dashboard.php';</script>";
    } else {
        echo "<script>alert('Error al crear usuario. Intente nuevamente.'); window.location.href='../dashboard.php';</script>";
    }
    
    $stmt->close();
    $conn->close();
} else {
    header('Location: ../dashboard.php');
    exit;
}
?>