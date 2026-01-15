<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$conn = getConnection();

// Obtener estadísticas
$stats = [];
if ($_SESSION['usuario_tipo'] == 'administrador' || $_SESSION['usuario_tipo'] == 'asesor') {
    $total_delegados = $conn->query("SELECT COUNT(*) as total FROM usuarios_sistema WHERE tipo_usuario = 'delegado'")->fetch_assoc()['total'];
    $total_usuarios = $conn->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'];
    $total_asesores = $conn->query("SELECT COUNT(*) as total FROM usuarios_sistema WHERE tipo_usuario = 'asesor'")->fetch_assoc()['total'];
    
    $stats = [
        'delegados' => $total_delegados,
        'usuarios' => $total_usuarios,
        'asesores' => $total_asesores
    ];
}

// Obtener lista de usuarios
$usuarios_lista = [];
if ($_SESSION['usuario_tipo'] == 'delegado') {
    $stmt = $conn->prepare("SELECT u.*, us.nombre as creador_nombre, us.apellidos as creador_apellidos, DATE_FORMAT(u.fecha_creacion, '%d/%m/%Y %H:%i') as fecha_formateada FROM usuarios u LEFT JOIN usuarios_sistema us ON u.creado_por = us.id WHERE u.delegado_id = ? ORDER BY u.fecha_creacion DESC");
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $usuarios_lista = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $usuarios_lista = $conn->query("SELECT u.*, us.nombre as delegado_nombre, us.apellidos as delegado_apellidos, DATE_FORMAT(u.fecha_creacion, '%d/%m/%Y %H:%i') as fecha_formateada FROM usuarios u LEFT JOIN usuarios_sistema us ON u.delegado_id = us.id ORDER BY u.fecha_creacion DESC")->fetch_all(MYSQLI_ASSOC);
}

// Obtener delegados para admin y asesor
$delegados_lista = [];
if ($_SESSION['usuario_tipo'] != 'delegado') {
    $delegados_lista = $conn->query("SELECT *, DATE_FORMAT(fecha_creacion, '%d/%m/%Y %H:%i') as fecha_formateada FROM usuarios_sistema WHERE tipo_usuario = 'delegado' ORDER BY fecha_creacion DESC")->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Delegados</title>
    <link rel="icon" type="image/webp" href="assets/images/favicon.webp">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/notifications.js"></script>
    <style>
        /* Auto-refresh cada 30 segundos para ver cambios en tiempo real */
        .auto-refresh-indicator {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body class="bg-gray-100">
    
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="ml-64">
        <?php include 'includes/header.php'; ?>
        
        <<main class="p-6 pt-20 pb-24 min-h-screen">
            
            <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
            <!-- Estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Total Delegados</p>
                            <h3 class="text-3xl font-bold text-purple-600"><?php echo $stats['delegados']; ?></h3>
                        </div>
                        <div class="bg-purple-100 p-4 rounded-lg">
                            <i class="fas fa-users text-purple-600 text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Total Usuarios</p>
                            <h3 class="text-3xl font-bold text-blue-600"><?php echo $stats['usuarios']; ?></h3>
                        </div>
                        <div class="bg-blue-100 p-4 rounded-lg">
                            <i class="fas fa-user-friends text-blue-600 text-2xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Total Asesores</p>
                            <h3 class="text-3xl font-bold text-green-600"><?php echo $stats['asesores']; ?></h3>
                        </div>
                        <div class="bg-green-100 p-4 rounded-lg">
                            <i class="fas fa-user-tie text-green-600 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h3 class="text-xl font-bold mb-4"><i class="fas fa-chart-bar mr-2"></i>Estadísticas Generales</h3>
                <canvas id="statsChart" style="max-height: 300px;"></canvas>
            </div>
            <?php endif; ?>

            <!-- Lista de Delegados (solo admin y asesor) -->
            <?php if ($_SESSION['usuario_tipo'] != 'delegado' && count($delegados_lista) > 0): ?>
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold"><i class="fas fa-users mr-2"></i>Lista de Delegados</h3>
                    <input type="text" id="buscarDelegado" placeholder="Buscar delegado..." 
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-purple-600 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left">Nombre</th>
                                <th class="px-4 py-3 text-left">País</th>
                                <th class="px-4 py-3 text-left">Ciudad</th>
                                <th class="px-4 py-3 text-left">Teléfono</th>
                                <th class="px-4 py-3 text-left">Usuario</th>
                                <th class="px-4 py-3 text-left">Fecha Registro</th>
                                <th class="px-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaDelegados">
                            <?php foreach ($delegados_lista as $delegado): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3"><?php echo htmlspecialchars($delegado['nombre'] . ' ' . $delegado['apellidos']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($delegado['pais']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($delegado['ciudad']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($delegado['telefono']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($delegado['usuario']); ?></td>
                                <td class="px-4 py-3 text-xs text-gray-500"><?php echo $delegado['fecha_formateada']; ?></td>
                                <td class="px-4 py-3 text-center">
                                    <button onclick="editarDelegado(<?php echo $delegado['id']; ?>)" class="text-blue-600 hover:text-blue-800 mr-2">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($_SESSION['usuario_tipo'] == 'administrador'): ?>
                                    <button onclick="eliminarDelegado(<?php echo $delegado['id']; ?>)" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Lista de Usuarios -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold"><i class="fas fa-user-friends mr-2"></i>Lista de Usuarios</h3>
                    <input type="text" id="buscarUsuario" placeholder="Buscar usuario..." 
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-blue-600 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left">Nombre</th>
                                <th class="px-4 py-3 text-left">País</th>
                                <th class="px-4 py-3 text-left">Ciudad</th>
                                <th class="px-4 py-3 text-left">Teléfono</th>
                                <th class="px-4 py-3 text-left">Centro de Estudios</th>
                                <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                                <th class="px-4 py-3 text-left">Delegado Asignado</th>
                                <?php endif; ?>
                                <th class="px-4 py-3 text-left">Fecha Registro</th>
                                <th class="px-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaUsuarios">
                            <?php foreach ($usuarios_lista as $usuario): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3"><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($usuario['pais']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($usuario['ciudad']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($usuario['telefono']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($usuario['centro_estudios']); ?></td>
                                <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                                <td class="px-4 py-3">
                                    <?php 
                                    if ($usuario['delegado_nombre']) {
                                        echo htmlspecialchars($usuario['delegado_nombre'] . ' ' . $usuario['delegado_apellidos']);
                                    } else {
                                        echo '<span class="text-gray-400">Sin asignar</span>';
                                    }
                                    ?>
                                </td>
                                <?php endif; ?>
                                <td class="px-4 py-3 text-xs text-gray-500"><?php echo $usuario['fecha_formateada']; ?></td>
                                <td class="px-4 py-3 text-center">
                                    <button onclick="editarUsuario(<?php echo $usuario['id']; ?>)" class="text-blue-600 hover:text-blue-800 mr-2">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                                    <button onclick="asignarDelegado(<?php echo $usuario['id']; ?>)" class="text-green-600 hover:text-green-800 mr-2">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                    <button onclick="eliminarUsuario(<?php echo $usuario['id']; ?>)" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
        
        <?php include 'includes/footer.php'; ?>
    </div>

    <script>
        <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
        // Crear gráfico
        const ctx = document.getElementById('statsChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Delegados', 'Usuarios', 'Asesores'],
                datasets: [{
                    label: 'Cantidad',
                    data: [<?php echo $stats['delegados']; ?>, <?php echo $stats['usuarios']; ?>, <?php echo $stats['asesores']; ?>],
                    backgroundColor: [
                        'rgba(124, 58, 237, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(34, 197, 94, 0.8)'
                    ],
                    borderColor: [
                        'rgba(124, 58, 237, 1)',
                        'rgba(59, 130, 246, 1)',
                        'rgba(34, 197, 94, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        <?php endif; ?>

        // Auto-refresh cada 30 segundos para ver cambios en tiempo real
        let autoRefreshTimer;
        function iniciarAutoRefresh() {
            autoRefreshTimer = setInterval(function() {
                // Recargar la página silenciosamente
                location.reload();
            }, 30000); // 30 segundos
        }
        
        // Iniciar auto-refresh
        iniciarAutoRefresh();
        
        // Pausar auto-refresh si hay un modal abierto
        document.addEventListener('DOMContentLoaded', function() {
            const modals = document.querySelectorAll('[id^="modal"]');
            modals.forEach(modal => {
                modal.addEventListener('click', function() {
                    clearInterval(autoRefreshTimer);
                });
            });
        });

        // Búsqueda en tiempo real
        document.getElementById('buscarUsuario')?.addEventListener('input', function(e) {
            const search = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#tablaUsuarios tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(search) ? '' : 'none';
            });
        });

        document.getElementById('buscarDelegado')?.addEventListener('input', function(e) {
            const search = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#tablaDelegados tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(search) ? '' : 'none';
            });
        });

        // Funciones placeholder (implementar después)
        function editarUsuario(id) {
            alert('Función en desarrollo: Editar usuario ' + id);
        }

        function editarDelegado(id) {
            alert('Función en desarrollo: Editar delegado ' + id);
        }

        function eliminarUsuario(id) {
            mostrarConfirmacion('¿Está seguro de eliminar este usuario?', function(confirmado) {
                if (confirmado) {
                    window.location.href = 'actions/eliminar_usuario.php?id=' + id;
                }
            });
        }

        function eliminarDelegado(id) {
            mostrarConfirmacion('¿Está seguro de desactivar este delegado? El delegado perderá acceso al sistema.', function(confirmado) {
                if (confirmado) {
                    window.location.href = 'actions/eliminar_usuario.php?id=' + id + '&tipo=delegado';
                }
            });
        }

        function asignarDelegado(id) {
            alert('Función en desarrollo: Asignar delegado al usuario ' + id);
        }
    </script>
</body>
</html>