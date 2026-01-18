<?php
// includes/logger.php
// Helper para insertar logs en logs_actividad.
// Requiere que config/database.php provea getConnection(): mysqli

require_once __DIR__ . '/../config/database.php';

/**
 * Inserta un log en logs_actividad.
 *
 * @param int|null $usuarioId
 * @param string|null $tipoUsuario
 * @param string $accion
 * @param array|null $detalleArray  (se guardará como JSON; NO incluya passwords)
 * @param string|null $usuarioNombre (opcional: nombre para guardar en el momento)
 * @param mysqli|null $conn
 * @return bool
 */
function log_action($usuarioId, $tipoUsuario, $accion, $detalleArray = null, $usuarioNombre = null, $conn = null) {
    $closeConn = false;
    if ($conn === null) {
        $conn = getConnection();
        $closeConn = true;
        if (!$conn) return false;
    }

    $uid = ($usuarioId === null) ? null : intval($usuarioId);
    $tipo = $tipoUsuario ? substr((string)$tipoUsuario, 0, 50) : null;
    $accionTxt = substr((string)$accion, 0, 255);
    $detalleJson = null;
    if ($detalleArray !== null) {
        $json = json_encode($detalleArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $detalleJson = ($json === false) ? json_encode(['raw' => (string)$detalleArray]) : $json;
    }

    // IP y user agent
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
    if ($ip && strpos($ip, ',') !== false) { $ip = trim(explode(',', $ip)[0]); }
    $ip = $ip ? substr((string)$ip, 0, 45) : null;
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? substr((string)$_SERVER['HTTP_USER_AGENT'], 0, 255) : null;

    // Si no se envió usuarioNombre y hay uid, intentar obtener nombre (silencioso)
    if ($usuarioNombre === null && $uid !== null) {
        $stmtN = $conn->prepare("SELECT COALESCE(nombre, usuario, '') AS nombre FROM usuarios_sistema WHERE id = ? LIMIT 1");
        if ($stmtN) {
            $stmtN->bind_param('i', $uid);
            if ($stmtN->execute()) {
                $res = $stmtN->get_result();
                if ($row = $res->fetch_assoc()) {
                    $usuarioNombre = $row['nombre'] ?: null;
                }
            }
            $stmtN->close();
        }
    }
    $usuarioNombre = $usuarioNombre ? substr((string)$usuarioNombre, 0, 150) : null;

    // metodo y ruta
    $metodo = $_SERVER['REQUEST_METHOD'] ?? null;
    $ruta = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? null;
    $metodo = $metodo ? substr($metodo, 0, 10) : null;
    $ruta = $ruta ? substr($ruta, 0, 255) : null;

    $sql = "INSERT INTO logs_actividad (usuario_id, usuario_nombre, tipo_usuario, metodo, ruta, accion, detalle, ip, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        if ($closeConn) $conn->close();
        return false;
    }

    // bind as strings; usuario_id may be NULL (bind_param requires variable)
    $uidParam = $uid === null ? null : $uid;
    $detalleParam = $detalleJson;
    $stmt->bind_param(
        'issssssss',
        $uidParam,
        $usuarioNombre,
        $tipo,
        $metodo,
        $ruta,
        $accionTxt,
        $detalleParam,
        $ip,
        $ua
    );

    $ok = $stmt->execute();
    $stmt->close();
    if ($closeConn) $conn->close();
    return (bool)$ok;
}