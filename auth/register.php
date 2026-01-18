<?php
session_start();
require_once '../config/database.php';

// incluir validadores centrales
require_once __DIR__ . '/../includes/validators.php';

header('Content-Type: application/json');

// Determinar la clave esperada
$env_key = getenv('REGISTRATION_KEY');
if ($env_key !== false && !empty(trim($env_key))) {
    $REGISTRATION_KEY = trim($env_key);
} elseif (defined('REGISTRATION_KEY')) {
    $REGISTRATION_KEY = REGISTRATION_KEY;
} else {
    $REGISTRATION_KEY = 'CAMBIA_ESTA_CLAVE_POR_DEFECTO';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Honeypot
if (!empty($_POST['website'])) {
    echo json_encode(['success' => false, 'message' => 'Registro no permitido.']);
    exit;
}

// Validar clave primero (para ahorrar trabajo)
$clave_enviada = isset($_POST['clave']) ? trim($_POST['clave']) : '';
if ($clave_enviada !== $REGISTRATION_KEY) {
    echo json_encode(['success' => false, 'message' => 'Clave de registro inválida']);
    exit;
}

// Validación y sanitización centralizada (requireClave=true para que valide si 'clave' está presente)
$v = validateDelegadoInput($_POST, true);
if (!$v['ok']) {
    echo json_encode(['success' => false, 'message' => $v['message']]);
    exit;
}
$data = $v['data'];

// Hash contraseña
$contrasena_hash = password_hash($data['contrasena'], PASSWORD_DEFAULT);

$conn = getConnection();

// Verificar usuario único
$stmt = $conn->prepare("SELECT id FROM usuarios_sistema WHERE usuario = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos (prepare usuario).']);
    $conn->close();
    exit;
}
$stmt->bind_param("s", $data['usuario']);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'El usuario ' . $data['usuario'] . ' ya existe']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Verificar teléfono
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE telefono = ? UNION SELECT id FROM usuarios_sistema WHERE telefono = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos (prepare teléfono).']);
    $conn->close();
    exit;
}
$stmt->bind_param("ss", $data['telefono'], $data['telefono']);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'El teléfono ' . $data['telefono'] . ' ya está registrado']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Insertar delegado (activo por defecto)
$stmt = $conn->prepare("INSERT INTO usuarios_sistema (nombre, apellidos, pais, ciudad, telefono, centro_estudios, usuario, contrasena, tipo_usuario, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'delegado', 1)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos (prepare insert).']);
    $conn->close();
    exit;
}
$stmt->bind_param("ssssssss", $data['nombre'], $data['apellidos'], $data['pais'], $data['ciudad'], $data['telefono'], $data['centro_estudios'], $data['usuario'], $contrasena_hash);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '¡Delegado creado exitosamente!', 'usuario' => $data['usuario'], 'contrasena' => $data['contrasena']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al crear delegado']);
}

$stmt->close();
$conn->close();
exit;
?>