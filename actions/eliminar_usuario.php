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
            echo "<script>alert('Este delegado tiene " . $row['total'] . " usuario(s) asignado(s). Por favor, reasigne o elimine los usuarios primero.'); window.location.href='../dashboard.php';</script>";
            exit;
        }
        
        $stmt->close();
        
        // Eliminar delegado
        $stmt = $conn->prepare("DELETE FROM usuarios_sistema WHERE id = ? AND tipo_usuario = 'delegado'");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo "<script>alert('Delegado eliminado exitosamente.'); window.location.href='../dashboard.php';</script>";
        } else {
            echo "<script>alert('Error al eliminar delegado.'); window.location.href='../dashboard.php';</script>";
        }
        
    } else {
        // Eliminar usuario
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo "<script>alert('Usuario eliminado exitosamente.'); window.location.href='../dashboard.php';</script>";
        } else {
            echo "<script>alert('Error al eliminar usuario.'); window.location.href='../dashboard.php';</script>";
        }
    }
    
    $stmt->close();
    $conn->close();
    
} else {
    header('Location: ../dashboard.php');
    exit;
}
?>