<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// ======= CONFIG / ADVERTENCIA =======
// Este script permite registros públicos CONTROLADOS mediante una clave (REGISTRATION_KEY).
// Por seguridad: cambia la clave por defecto lo antes posible.
// Opciones para establecer la clave:
// 1) Variable de entorno: REGISTRATION_KEY
// 2) Constante en config/database.php: define('REGISTRATION_KEY', 'tu_clave');
// Si no hay ninguna, la clave por defecto será 'CAMBIA_ESTA_CLAVE_POR_DEFECTO' (NO segura).
// =====================================

// Determinar la clave esperada
$env_key = getenv('REGISTRATION_KEY');
if ($env_key !== false && !empty(trim($env_key))) {
    $REGISTRATION_KEY = trim($env_key);
} elseif (defined('REGISTRATION_KEY')) {
    $REGISTRATION_KEY = REGISTRATION_KEY;
} else {
    // Valor por defecto: cambiar obligatoriamente en producción
    $REGISTRATION_KEY = 'CAMBIA_ESTA_CLAVE_POR_DEFECTO';
}

// Aceptar solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Honeypot: campo oculto para detectar bots
if (!empty($_POST['website'])) {
    echo json_encode(['success' => false, 'message' => 'Registro no permitido.']);
    exit;
}

// Campos requeridos
$required = ['nombre','apellidos','pais','ciudad','telefono','centro_estudios','usuario','contrasena','clave'];
foreach ($required as $r) {
    if (!isset($_POST[$r]) || trim($_POST[$r]) === '') {
        echo json_encode(['success' => false, 'message' => 'Faltan campos requeridos: ' . $r]);
        exit;
    }
}

// Validar clave de registro
$clave_enviada = trim($_POST['clave']);
if ($clave_enviada !== $REGISTRATION_KEY) {
    echo json_encode(['success' => false, 'message' => 'Clave de registro inválida']);
    exit;
}

// Sanitizar y preparar datos (usa funciones existentes en config/database.php)
$nombre = function_exists('capitalizarNombre') ? capitalizarNombre(sanitize($_POST['nombre'])) : sanitize($_POST['nombre']);
$apellidos = function_exists('capitalizarNombre') ? capitalizarNombre(sanitize($_POST['apellidos'])) : sanitize($_POST['apellidos']);
$pais = sanitize($_POST['pais']);
$ciudad = sanitize($_POST['ciudad']);
$telefono_sin_prefijo = sanitize($_POST['telefono']);
$telefono = function_exists('getPrefijoPais') ? getPrefijoPais($pais) . $telefono_sin_prefijo : $telefono_sin_prefijo;
$centro_estudios = function_exists('capitalizarNombre') ? capitalizarNombre(sanitize($_POST['centro_estudios'])) : sanitize($_POST['centro_estudios']);
$usuario = sanitize($_POST['usuario']);
$contrasena_plain = $_POST['contrasena'];

// Validaciones extra
if (strlen($usuario) < 4) {
    echo json_encode(['success' => false, 'message' => 'El usuario debe tener al menos 4 caracteres']);
    exit;
}
if (strlen($contrasena_plain) < 6) {
    echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
    exit;
}

// Hash de contraseña
$contrasena = password_hash($contrasena_plain, PASSWORD_DEFAULT);

// Conexión a BD y comprobaciones
$conn = getConnection();

// Verificar usuario único
$stmt = $conn->prepare("SELECT id FROM usuarios_sistema WHERE usuario = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos (prepare).']);
    $conn->close();
    exit;
}
$stmt->bind_param("s", $usuario);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'El usuario ' . $usuario . ' ya existe']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Verificar teléfono en tablas usuarios y usuarios_sistema
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE telefono = ? UNION SELECT id FROM usuarios_sistema WHERE telefono = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos (prepare).']);
    $conn->close();
    exit;
}
$stmt->bind_param("ss", $telefono, $telefono);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'El teléfono ' . $telefono . ' ya está registrado']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Insertar nuevo delegado (activo por defecto)
$stmt = $conn->prepare("INSERT INTO usuarios_sistema (nombre, apellidos, pais, ciudad, telefono, centro_estudios, usuario, contrasena, tipo_usuario, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'delegado', 1)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos (prepare).']);
    $conn->close();
    exit;
}
$stmt->bind_param("ssssssss", $nombre, $apellidos, $pais, $ciudad, $telefono, $centro_estudios, $usuario, $contrasena);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '¡Delegado creado exitosamente!', 'usuario' => $usuario, 'contrasena' => $contrasena_plain]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al crear delegado']);
}

$stmt->close();
$conn->close();
exit;
?>