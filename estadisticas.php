<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'administrador') {
    header('Location: dashboard.php');
    exit;
}

$conn = getConnection();

// Obtener logs de actividad (crear tabla de logs si no existe)
$conn->query("CREATE TABLE IF NOT EXISTS logs_actividad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    tipo_usuario VARCHAR(50),
    accion VARCHAR(100),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios_sistema(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$logs = $conn->query("SELECT l.*, u.nombre, u.apellidos, u.tipo_usuario FROM logs_actividad l 
                      LEFT JOIN usuarios_sistema u ON l.usuario_id = u.id 
                      ORDER BY l.fecha DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC);
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
                                <th class="px-3 py-2 text-center">Eliminar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($logs) == 0): ?>
                            <tr>
                                <td colspan="5" class="px-3 py-4 text-center text-gray-500">No hay registros de actividad aún</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-3 py-2 text-xs"><?php echo date('d/m/Y H:i', strtotime($log['fecha'])); ?></td>
                                <td class="px-3 py-2"><?php echo htmlspecialchars($log['nombre'] . ' ' . $log['apellidos']); ?></td>
                                <td class="px-3 py-2">
                                    <span class="px-2 py-1 rounded text-xs font-semibold bg-navy text-white">
                                        <?php echo ucfirst($log['tipo_usuario']); ?>
                                    </span>
                                </td>
                                <td class="px-3 py-2"><?php echo htmlspecialchars($log['accion']); ?></td>
                                <td class="px-3 py-2 text-center">
                                    <button onclick="eliminarLog(<?php echo $log['id']; ?>)" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
        
        <?php include 'includes/footer.php'; ?>
    </div>

    <script>
        function eliminarLog(id) {
            mostrarConfirmacion('¿Eliminar este registro de actividad?', function(confirmado) {
                if (confirmado) {
                    window.location.href = 'actions/eliminar_log.php?id=' + id;
                }
            });
        }
    </script>

</body>
</html>