<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
$valor = isset($_POST['valor']) ? $_POST['valor'] : '';
$id_actual = isset($_POST['id_actual']) ? intval($_POST['id_actual']) : 0;

$conn = getConnection();
$respuesta = ['valido' => true, 'mensaje' => ''];

switch ($tipo) {
    case 'usuario':
        if ($id_actual > 0) {
            $stmt = $conn->prepare("SELECT id FROM usuarios_sistema WHERE usuario = ? AND id != ?");
            $stmt->bind_param("si", $valor, $id_actual);
        } else {
            $stmt = $conn->prepare("SELECT id FROM usuarios_sistema WHERE usuario = ?");
            $stmt->bind_param("s", $valor);
        }
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $respuesta['valido'] = false;
            $respuesta['mensaje'] = 'El usuario ya existe en el sistema';
        }
        $stmt->close();
        break;
        
    case 'telefono':
        if ($id_actual > 0) {
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE telefono = ? AND id != ? UNION SELECT id FROM usuarios_sistema WHERE telefono = ? AND id != ?");
            $stmt->bind_param("sisi", $valor, $id_actual, $valor, $id_actual);
        } else {
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE telefono = ? UNION SELECT id FROM usuarios_sistema WHERE telefono = ?");
            $stmt->bind_param("ss", $valor, $valor);
        }
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $respuesta['valido'] = false;
            $respuesta['mensaje'] = 'El teléfono ya está registrado en el sistema';
        }
        $stmt->close();
        break;
        
    case 'email':
        if ($id_actual > 0) {
            $stmt = $conn->prepare("SELECT id FROM usuarios_sistema WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $valor, $id_actual);
        } else {
            $stmt = $conn->prepare("SELECT id FROM usuarios_sistema WHERE email = ?");
            $stmt->bind_param("s", $valor);
        }
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $respuesta['valido'] = false;
            $respuesta['mensaje'] = 'El email ya está registrado en el sistema';
        }
        $stmt->close();
        break;
}

$conn->close();
echo json_encode($respuesta);
?>