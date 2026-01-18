<?php
// includes/auto_logger.php
// Registro automático de acciones "relevantes".
// Incluir en includes/header.php: require_once __DIR__ . '/auto_logger.php';

if (session_status() === PHP_SESSION_NONE) { @session_start(); }

// Evitar doble inclusión
if (defined('AUTO_LOGGER_INCLUDED')) return;
define('AUTO_LOGGER_INCLUDED', true);

// Ruta al helper
$logger_path = __DIR__ . '/logger.php';
if (!file_exists($logger_path)) return;

// Evitar assets estáticos
$uri_path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$lower_uri = strtolower($uri_path);
$static_exts = ['.css','.js','.png','.jpg','.jpeg','.gif','.svg','.ico','.woff','.woff2','.ttf','.eot','.map','.pdf','.zip'];
foreach ($static_exts as $ext) {
    if (str_ends_with($lower_uri, $ext)) return;
}

// Decidir registrar
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$register = false;
$force_tipo = null;

if ($method === 'POST') $register = true;
if ($method === 'GET') {
    if (!empty($_GET)) $register = true;
    $indicators = ['/actions/', '/ajax/', 'delete', 'eliminar', 'action=', 'accion='];
    foreach ($indicators as $i) {
        if (stripos($_SERVER['REQUEST_URI'] ?? '', $i) !== false) { $register = true; break; }
    }
}

// Detectar form públicos (crear_delegado/register) sin sesión
if (!$register && $method === 'POST') {
    $registerEndpoints = ['crear_delegado', '/auth/register', '/register', 'registro_delegado', 'crear_delegado.php', 'register.php'];
    foreach ($registerEndpoints as $pe) {
        if (stripos($lower_uri, $pe) !== false) { $register = true; $force_tipo = 'delegado'; break; }
    }
    if (!$force_tipo && (isset($_POST['tipo_usuario']) || isset($_POST['tipo']))) {
        $postedTipo = strtolower(trim((string)($_POST['tipo_usuario'] ?? $_POST['tipo'] ?? '')));
        if ($postedTipo === 'delegado') { $register = true; $force_tipo = 'delegado'; }
    }
}

if (!$register) return;

// No registrar si la sesión pertenece a administrador (por diseño)
$uid = $_SESSION['usuario_id'] ?? null;
$tipo_sesion = strtolower($_SESSION['usuario_tipo'] ?? '');
if ($uid && $tipo_sesion === 'administrador') return;

// Construir detalle seguro (excluir campos sensibles)
$skip = ['password','contrasena','pass','pwd','token','csrf','_csrf','archivo','file','clave'];
$source = ($method === 'POST') ? $_POST : $_GET;
$detalle = [];
foreach ($source as $k => $v) {
    if (in_array(strtolower($k), $skip, true)) continue;
    if (is_array($v)) $detalle[$k] = array_slice($v, 0, 30);
    else $detalle[$k] = mb_substr((string)$v, 0, 500);
}

// Mensaje breve (acción)
$summaryParts = [];
foreach ($detalle as $k => $v) {
    $summaryParts[] = $k . '=' . (is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v);
}
$summary = $summaryParts ? implode(', ', array_slice($summaryParts, 0, 12)) : 'sin_datos_detallados';
$accion = $method . ' ' . $uri_path . ' | ' . $summary;

// Tipo a guardar
$tipo = $tipo_sesion ?: ($force_tipo ?? 'anonimo');

// Registrar (no interrumpir flujo si falla)
require_once $logger_path;
@log_action($uid ?? null, $tipo, $accion, $detalle);