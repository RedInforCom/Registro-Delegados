<?php
// actions/get_logs.php
// Devuelve HTML parcial de logs + indicador 'more' para infinite scroll.
// Requiere sesión de administrador.

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

// Verificar permiso: solo administradores
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$conn = getConnection();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

// Leer filtros
$q = trim($_GET['q'] ?? '');
$tipo = trim($_GET['tipo'] ?? '');
$usuarioFilter = trim($_GET['usuario'] ?? '');
$desde = trim($_GET['desde'] ?? '');
$hasta = trim($_GET['hasta'] ?? '');

// Paginación (batch)
$limit = isset($_GET['limit']) ? max(10, min(200, intval($_GET['limit']))) : 50;
$offset = isset($_GET['offset']) ? max(0, intval($_GET['offset'])) : 0;

// Construir WHERE dinámico
$where = [];
$params = [];
$types = '';

if ($q !== '') {
    $where[] = "(accion LIKE CONCAT('%', ?, '%') OR usuario_nombre LIKE CONCAT('%', ?, '%') OR ruta LIKE CONCAT('%', ?, '%'))";
    $params[] = $q; $params[] = $q; $params[] = $q;
    $types .= 'sss';
}
if ($tipo !== '') {
    $where[] = "tipo_usuario = ?";
    $params[] = $tipo;
    $types .= 's';
}
if ($usuarioFilter !== '') {
    // permitir búsqueda por nombre o por id numérica
    $where[] = "(usuario_nombre LIKE CONCAT('%', ?, '%') OR usuario_id = ?)";
    $params[] = $usuarioFilter;
    $params[] = ctype_digit($usuarioFilter) ? intval($usuarioFilter) : 0;
    $types .= 'si';
}
if ($desde !== '') {
    $where[] = "fecha >= ?";
    $params[] = $desde . ' 00:00:00';
    $types .= 's';
}
if ($hasta !== '') {
    $where[] = "fecha <= ?";
    $params[] = $hasta . ' 23:59:59';
    $types .= 's';
}

$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Query principal con LIMIT/OFFSET
$sql = "
    SELECT id, usuario_id, usuario_nombre, tipo_usuario, metodo, ruta, accion, detalle, ip, user_agent, fecha
    FROM logs_actividad
    $where_sql
    ORDER BY fecha DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error preparando la consulta']);
    $conn->close();
    exit;
}

// Bind dinámico de parámetros (params ... + limit + offset)
if (!empty($params)) {
    $bind_types = $types . 'ii';
    $bind_values = $params;
    $bind_values[] = $limit;
    $bind_values[] = $offset;

    // Crear array de referencias
    $refs = [];
    $refs[] = &$bind_types;
    for ($i = 0; $i < count($bind_values); $i++) {
        $refs[] = &$bind_values[$i];
    }
    call_user_func_array([$stmt, 'bind_param'], $refs);
} else {
    // Solo limit y offset
    $stmt->bind_param('ii', $limit, $offset);
}

$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = $r;
}

$stmt->close();
$conn->close();

// Construir HTML que el frontend insertará (puede ser tabla completa o sólo filas)
// Devolvemos un fragmento que contenga <table>..</table> para facilitar el append.
$html = '';
if (empty($rows)) {
    if ($offset === 0) {
        $html = '<div class="p-4 text-sm text-gray-600">No hay registros recientes</div>';
    } else {
        $html = ''; // nada para añadir
    }
} else {
    // Crear tabla con filas
    $html .= '<table class="w-full text-sm"><tbody>';
    foreach ($rows as $row) {
        $fecha = htmlspecialchars($row['fecha'] ?? '');
        // usuarioDisplay: preferir usuario_nombre, si no ID, sino 'Anónimo'
        $usuarioDisplay = 'Anónimo';
        if (!empty($row['usuario_nombre'])) $usuarioDisplay = htmlspecialchars($row['usuario_nombre']);
        elseif (!empty($row['usuario_id'])) $usuarioDisplay = 'ID:' . intval($row['usuario_id']);

        $tipoUsuario = htmlspecialchars($row['tipo_usuario'] ?? '');
        $accion = htmlspecialchars($row['accion'] ?? '');
        $detalleText = '';
        if (!empty($row['detalle'])) {
            $det = json_decode($row['detalle'], true);
            $detalleText = $det !== null ? json_encode($det, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : $row['detalle'];
        }
        $detalleEsc = htmlspecialchars($detalleText);

        $html .= '<tr class="border-b hover:bg-gray-50">';
        $html .= '<td class="px-3 py-2 align-top">' . $fecha . '</td>';
        $html .= '<td class="px-3 py-2 align-top">' . $usuarioDisplay . '</td>';
        $html .= '<td class="px-3 py-2 align-top">' . $tipoUsuario . '</td>';
        $html .= '<td class="px-3 py-2 align-top">' . $accion . '</td>';
        $html .= '<td class="px-3 py-2 align-top"><pre style="white-space:pre-wrap;word-break:break-word;max-width:36rem;">' . $detalleEsc . '</pre></td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';
}

$more = count($rows) === $limit;

echo json_encode([
    'success' => true,
    'html' => $html,
    'more' => $more,
    'count' => count($rows)
]);
exit;
?>