<?php
session_start();
require_once 'config/database.php';

// Verificación de permisos: solo administradores
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'administrador') {
    header('Location: dashboard.php');
    exit;
}

$conn = getConnection();

// Asegurarnos de que la tabla exista (no modifica si ya está)
// Es la versión "mejorada" que guarda detalle JSON, usuario_nombre, metodo y ruta.
$conn->query("CREATE TABLE IF NOT EXISTS logs_actividad (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT DEFAULT NULL,
    usuario_nombre VARCHAR(150) DEFAULT NULL,
    tipo_usuario VARCHAR(50) DEFAULT NULL,
    metodo VARCHAR(10) DEFAULT NULL,
    ruta VARCHAR(255) DEFAULT NULL,
    accion VARCHAR(255) DEFAULT NULL,
    detalle JSON DEFAULT NULL,
    ip VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (usuario_id),
    INDEX (tipo_usuario),
    INDEX (fecha),
    CONSTRAINT IF NOT EXISTS logs_actividad_ibfk_user FOREIGN KEY (usuario_id) REFERENCES usuarios_sistema(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

// Obtener últimos 200 logs (ajusta límite si hace falta)
$limit = 200;
$stmt = $conn->prepare("
    SELECT l.id, l.usuario_id, l.usuario_nombre, l.tipo_usuario, l.metodo, l.ruta, l.accion, l.detalle, l.ip, l.user_agent, l.fecha,
           u.nombre AS usuario_nombre_sistema, u.apellidos AS usuario_apellidos_sistema, u.tipo_usuario AS usuario_tipo_sistema
    FROM logs_actividad l
    LEFT JOIN usuarios_sistema u ON l.usuario_id = u.id
    ORDER BY l.fecha DESC
    LIMIT ?
");
if ($stmt) {
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $res = $stmt->get_result();
    $logs = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    // Si prepare falla, intentar query directa (fallback)
    $res = $conn->query("SELECT * FROM logs_actividad ORDER BY fecha DESC LIMIT " . intval($limit));
    $logs = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas | Delegados</title>
    <link rel="icon" type="image/webp" href="assets/images/favicon.webp">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="assets/js/notifications.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'navy': '#001f3f',
                        'lightblue': '#7FDBFF',
                    }
                }
            }
        }
    </script>
    <style>
      pre.detalle { white-space: pre-wrap; word-break: break-word; max-width: 36rem; font-size: 0.85rem; }
    </style>
</head>
<body class="bg-gray-100">
    
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="ml-64">
        <?php include 'includes/header.php'; ?>
        
        <main class="p-6 pt-24 min-h-screen pb-20">
            
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-chart-line mr-2"></i>Estadísticas y Logs de Actividad
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-navy text-white">
                            <tr>
                                <th class="px-3 py-2 text-left">Fecha</th>
                                <th class="px-3 py-2 text-left">Usuario</th>
                                <th class="px-3 py-2 text-left">Tipo</th>
                                <th class="px-3 py-2 text-left">Acción</th>
                                <th class="px-3 py-2 text-left">Detalle</th>
                                <th class="px-3 py-2 text-left">IP</th>
                                <th class="px-3 py-2 text-center">Eliminar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="7" class="px-3 py-4 text-center text-gray-500">No hay registros de actividad aún</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                    <tr class="border-b hover:bg-gray-50 align-top">
                                        <td class="px-3 py-3 align-top"><?= htmlspecialchars($log['fecha'] ?? '') ?></td>

                                        <?php
                                          // Preferir nombre guardado en log, sino el nombre del sistema, sino ID o 'Anónimo'
                                          $usuarioDisplay = 'Anónimo';
                                          if (!empty($log['usuario_nombre'])) {
                                              $usuarioDisplay = trim($log['usuario_nombre']);
                                          } elseif (!empty($log['usuario_nombre_sistema']) || !empty($log['usuario_apellidos_sistema'])) {
                                              $usuarioDisplay = trim(($log['usuario_nombre_sistema'] ?? '') . ' ' . ($log['usuario_apellidos_sistema'] ?? ''));
                                          } elseif (!empty($log['usuario_id'])) {
                                              $usuarioDisplay = 'ID:' . intval($log['usuario_id']);
                                          }
                                          $tipoMostrar = $log['usuario_tipo_sistema'] ?? $log['tipo_usuario'] ?? '—';
                                          // Acción y detalle
                                          $accion = $log['accion'] ?? '';
                                          $detalleHtml = '';
                                          if (!empty($log['detalle'])) {
                                              // Si es string JSON, decodificar; si no, dejar como está
                                              $det = null;
                                              if (is_string($log['detalle'])) {
                                                  $det = json_decode($log['detalle'], true);
                                              } else {
                                                  $det = $log['detalle'];
                                              }
                                              if ($det !== null) {
                                                  $detalleText = json_encode($det, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
                                              } else {
                                                  $detalleText = (string)$log['detalle'];
                                              }
                                              $detalleHtml = '<pre class="detalle">' . htmlspecialchars($detalleText) . '</pre>';
                                          } else {
                                              // intentar mostrar método/ruta si existen
                                              $mr = [];
                                              if (!empty($log['metodo'])) $mr[] = htmlspecialchars($log['metodo']);
                                              if (!empty($log['ruta'])) $mr[] = htmlspecialchars($log['ruta']);
                                              $detalleHtml = $mr ? '<div class="text-xs text-gray-600">' . implode(' / ', $mr) . '</div>' : '<div class="text-xs text-gray-500">-</div>';
                                          }
                                        ?>
                                        <td class="px-3 py-3 align-top"><?= htmlspecialchars($usuarioDisplay) ?></td>
                                        <td class="px-3 py-3 align-top"><?= htmlspecialchars($tipoMostrar) ?></td>
                                        <td class="px-3 py-3 align-top"><?= htmlspecialchars($accion) ?></td>
                                        <td class="px-3 py-3 align-top"><?= $detalleHtml ?></td>
                                        <td class="px-3 py-3 align-top"><?= htmlspecialchars($log['ip'] ?? '') ?></td>
                                        <td class="px-3 py-3 text-center align-top">
                                            <a href="actions/eliminar_log.php?id=<?= intval($log['id']) ?>" class="text-red-600 hover:underline" title="Eliminar registro" onclick="return confirm('¿Eliminar este registro?');">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>

</body>
</html>