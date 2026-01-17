<?php
// config/update_registration_key.php
session_start();
require_once __DIR__ . '/database.php';

header('Content-Type: application/json; charset=utf-8');

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar administrador
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$new_key = trim($_POST['new_key'] ?? '');
$confirm = trim($_POST['confirm_key'] ?? '');

// Validaciones básicas
if ($new_key === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'La nueva clave no puede estar vacía.']);
    exit;
}
if ($new_key !== $confirm) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Las claves no coinciden.']);
    exit;
}
if (mb_strlen($new_key) > 200) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'La clave es demasiado larga (max 200 caracteres).']);
    exit;
}

// Archivo objetivo
$reg_file = __DIR__ . '/registration_key.php';
$content = "<?php\n// Archivo generado por config/update_registration_key.php\nreturn " . var_export($new_key, true) . ";\n";

// Escribir de forma segura: temp + rename
$tmp = $reg_file . '.tmp';
$ok = false;
if (@file_put_contents($tmp, $content, LOCK_EX) !== false) {
    @chmod($tmp, 0644);
    if (@rename($tmp, $reg_file)) {
        $ok = true;
    } else {
        if (@copy($tmp, $reg_file)) {
            @unlink($tmp);
            $ok = true;
        }
    }
}

if (!$ok) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'No se pudo escribir el archivo. Verifica permisos.']);
    exit;
}

// Éxito
echo json_encode(['success' => true, 'message' => 'Clave actualizada correctamente.']);
exit;