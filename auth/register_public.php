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

// Detectar si la petición la hace un usuario interno (Administrador o Asesor)
// Si es interna, NO requeriremos 'clave' en el formulario.
$is_internal = false;
if (isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_tipo'])) {
    $tipo = $_SESSION['usuario_tipo'];
    if ($tipo === 'administrador' || $tipo === 'asesor') {
        $is_internal = true;
    }
}

// Campos requeridos
if ($is_internal) {
    // Para usuarios internos no se exige la clave
    $required = ['nombre','apellidos','pais','ciudad','telefono','centro_estudios','usuario','contrasena'];
} else {
    // Para público se exige la clave
    $required = ['nombre','apellidos','pais','ciudad','telefono','centro_estudios','usuario','contrasena','clave'];
}

foreach ($required as $r) {
    if (!isset($_POST[$r]) || trim($_POST[$r]) === '') {
        echo json_encode(['success' => false, 'message' => 'Faltan campos requeridos: ' . $r]);
        exit;
    }
}

// Validar clave de registro solo si no es petición interna
if (!$is_internal) {
    $clave_enviada = trim($_POST['clave']);
    if ($clave_enviada !== $REGISTRATION_KEY) {

        // --- LOG: intento con clave inválida (solo para no-internal) ---
        if (file_exists(__DIR__ . '/../includes/logger.php')) {
            require_once __DIR__ . '/../includes/logger.php';

            // actor: si hay sesión, se guardará el id/tipo (por ejemplo, si es un asesor logueado)
            $actorId = $_SESSION['usuario_id'] ?? null;
            $actorTipo = $_SESSION['usuario_tipo'] ?? 'anonimo';

            // preparar detalle seguro: clonar $_POST y quitar campos sensibles/honeypot
            $detalle = $_POST;
            unset($detalle['contrasena'], $detalle['password'], $detalle['clave'], $detalle['token'], $detalle['_csrf'], $detalle['website']);

            // registrar (no interrumpe el flujo si falla)
            @log_action(
                $actorId,
                $actorTipo,
                'CLAVE INVALIDA - Intento de registro',
                $detalle,
                ($detalle['nombre'] ?? null)
            );
        }
        // --- fin LOG ---

        echo json_encode(['success' => false, 'message' => 'Clave de registro inválida']);
        exit;
    }
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
// (los placeholders coinciden con los parámetros que vamos a bindear)
$stmt = $conn->prepare("INSERT INTO usuarios_sistema (nombre, apellidos, pais, ciudad, telefono, centro_estudios, usuario, contrasena, tipo_usuario, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'delegado', 1)");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos (prepare).']);
    $conn->close();
    exit;
}
$stmt->bind_param("ssssssss", $nombre, $apellidos, $pais, $ciudad, $telefono, $centro_estudios, $usuario, $contrasena);

if ($stmt->execute()) {
    // --- LOG: registro exitoso ---
    $newId = $conn->insert_id;
    // Preparar detalle seguro: clonar $_POST y eliminar campos sensibles
    $detalle = $_POST;
    unset($detalle['contrasena'], $detalle['password'], $detalle['clave'], $detalle['token'], $detalle['_csrf'], $detalle['website']);

    // Intentar registrar en logs_actividad (no interrumpir flujo si falla)
    if (file_exists(__DIR__ . '/../includes/logger.php')) {
        require_once __DIR__ . '/../includes/logger.php';
        $actorId = $_SESSION['usuario_id'] ?? null;
        $actorTipo = $_SESSION['usuario_tipo'] ?? 'anonimo';
        $usuarioNombre = trim($nombre . ' ' . $apellidos);
        $accionText = 'Registro público de delegado' . ($newId ? ' (id=' . intval($newId) . ')' : '');
        @log_action($actorId, $actorTipo, $accionText, $detalle, $usuarioNombre, $conn);
    }
    // --- fin LOG ---

    echo json_encode(['success' => true, 'message' => '¡Delegado creado exitosamente!', 'usuario' => $usuario, 'contrasena' => $contrasena_plain]);
} else {
    // --- LOG: fallo en INSERT ---
    $usuarioNombre = trim($nombre . ' ' . $apellidos);
    if (file_exists(__DIR__ . '/../includes/logger.php')) {
        require_once __DIR__ . '/../includes/logger.php';
        $actorId = $_SESSION['usuario_id'] ?? null;
        $actorTipo = $_SESSION['usuario_tipo'] ?? 'anonimo';
        $accionText = 'Error al crear delegado: ' . ($stmt->error ?? 'desconocido');
        $detalleError = ['usuario' => $usuario, 'error' => $stmt->error ?? ''];
        @log_action($actorId, $actorTipo, $accionText, $detalleError, $usuarioNombre, $conn);
    }
    // --- fin LOG ---

    echo json_encode(['success' => false, 'message' => 'Error al crear delegado']);
}

$stmt->close();
$conn->close();
exit;
?>