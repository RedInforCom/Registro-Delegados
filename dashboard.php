<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$conn = getConnection();

// Obtener todos los registros según el tipo de usuario
if ($_SESSION['usuario_tipo'] == 'delegado') {
    // Solo sus usuarios
    $query = "SELECT 'usuario' as tipo, u.id, u.nombre, u.apellidos, u.pais, u.ciudad, u.telefono, u.centro_estudios as centro, u.notas, u.fecha_creacion, us.nombre as delegado_nombre, us.apellidos as delegado_apellidos 
              FROM usuarios u 
              LEFT JOIN usuarios_sistema us ON u.delegado_id = us.id 
              WHERE u.delegado_id = {$_SESSION['usuario_id']}
              ORDER BY u.fecha_creacion DESC";
} else {
    // Admin y Asesor ven todo
    $query = "SELECT 'delegado' as tipo, id, nombre, apellidos, pais, ciudad, telefono, centro_estudios as centro, '' as notas, fecha_creacion, '' as delegado_nombre, '' as delegado_apellidos, usuario 
              FROM usuarios_sistema 
              WHERE tipo_usuario = 'delegado'
              UNION ALL
              SELECT 'usuario' as tipo, u.id, u.nombre, u.apellidos, u.pais, u.ciudad, u.telefono, u.centro_estudios as centro, u.notas, u.fecha_creacion, us.nombre as delegado_nombre, us.apellidos as delegado_apellidos, '' as usuario
              FROM usuarios u 
              LEFT JOIN usuarios_sistema us ON u.delegado_id = us.id
              ORDER BY fecha_creacion DESC";
}

$result = $conn->query($query);
$registros = $result->fetch_all(MYSQLI_ASSOC);

// Obtener países únicos para filtro
$paises = array_unique(array_column($registros, 'pais'));
sort($paises);

// Obtener delegados para asignar (solo admin y asesor)
$delegados_lista = [];
if ($_SESSION['usuario_tipo'] != 'delegado') {
    $delegados_result = $conn->query("SELECT id, nombre, apellidos FROM usuarios_sistema WHERE tipo_usuario = 'delegado' ORDER BY nombre");
    $delegados_lista = $delegados_result->fetch_all(MYSQLI_ASSOC);
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
            
            <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
            <!-- Estadísticas Compactas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-xs">Delegados</p>
                            <h3 class="text-2xl font-bold text-navy"><?php echo $conn->query("SELECT COUNT(*) as t FROM usuarios_sistema WHERE tipo_usuario = 'delegado'")->fetch_assoc()['t']; ?></h3>
                        </div>
                        <i class="fas fa-users text-navy text-2xl"></i>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-xs">Usuarios</p>
                            <h3 class="text-2xl font-bold text-blue-600"><?php echo $conn->query("SELECT COUNT(*) as t FROM usuarios")->fetch_assoc()['t']; ?></h3>
                        </div>
                        <i class="fas fa-user-friends text-blue-600 text-2xl"></i>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-xs">Asesores</p>
                            <h3 class="text-2xl font-bold text-orange-600"><?php echo $conn->query("SELECT COUNT(*) as t FROM usuarios_sistema WHERE tipo_usuario = 'asesor'")->fetch_assoc()['t']; ?></h3>
                        </div>
                        <i class="fas fa-user-tie text-orange-600 text-2xl"></i>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Lista Completa con Filtros -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800">
                        <i class="fas fa-list mr-2"></i>
                        <?php echo $_SESSION['usuario_tipo'] == 'delegado' ? 'Mis Usuarios' : 'Delegados y Usuarios'; ?>
                    </h2>
                    <span class="text-gray-600 text-sm">Total: <strong><?php echo count($registros); ?></strong></span>
                </div>

                <!-- Filtros -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                    <input type="text" id="searchInput" placeholder="Buscar..." 
                        class="px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-navy">

                    <select id="filtroPais" class="px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-navy">
                        <option value="">Todos los países</option>
                        <?php foreach ($paises as $pais): ?>
                        <option value="<?php echo htmlspecialchars($pais); ?>"><?php echo htmlspecialchars($pais); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                    <select id="filtroTipo" class="px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-navy">
                        <option value="">Todos</option>
                        <option value="delegado">Delegados</option>
                        <option value="usuario">Usuarios</option>
                    </select>
                    <?php endif; ?>

                    <select id="ordenFecha" class="px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-navy">
                        <option value="desc">Más reciente</option>
                        <option value="asc">Más antiguo</option>
                    </select>
                </div>

                <!-- Tabla -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-navy text-white">
                            <tr>
                                <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                                <th class="px-3 py-2 text-left">Tipo</th>
                                <?php endif; ?>
                                <th class="px-3 py-2 text-left">Nombre</th>
                                <th class="px-3 py-2 text-left">País</th>
                                <th class="px-3 py-2 text-left">Ciudad</th>
                                <th class="px-3 py-2 text-left">Teléfono</th>
                                <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                                <th class="px-3 py-2 text-left">Delegado</th>
                                <?php endif; ?>
                                <th class="px-3 py-2 text-left">Fecha</th>
                                <th class="px-3 py-2 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaRegistros">
                            <?php foreach ($registros as $reg): ?>
                            <tr class="border-b hover:bg-gray-50" data-tipo="<?php echo $reg['tipo']; ?>" data-pais="<?php echo htmlspecialchars($reg['pais']); ?>" data-fecha="<?php echo strtotime($reg['fecha_creacion']); ?>">
                                <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                                <td class="px-3 py-2">
                                    <span class="px-2 py-1 rounded text-xs font-semibold <?php echo $reg['tipo'] == 'delegado' ? 'bg-navy text-white' : 'bg-lightblue text-navy'; ?>">
                                        <?php echo ucfirst($reg['tipo']); ?>
                                    </span>
                                </td>
                                <?php endif; ?>
                                <td class="px-3 py-2 font-semibold"><?php echo htmlspecialchars($reg['nombre'] . ' ' . $reg['apellidos']); ?></td>
                                <td class="px-3 py-2"><?php echo htmlspecialchars($reg['pais']); ?></td>
                                <td class="px-3 py-2"><?php echo htmlspecialchars($reg['ciudad']); ?></td>
                                <td class="px-3 py-2"><?php echo htmlspecialchars($reg['telefono']); ?></td>
                                <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                                <td class="px-3 py-2 text-xs">
                                    <?php if ($reg['tipo'] == 'usuario'): ?>
                                        <?php echo $reg['delegado_nombre'] ? htmlspecialchars($reg['delegado_nombre'] . ' ' . $reg['delegado_apellidos']) : '<span class="text-gray-400">Sin asignar</span>'; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                                <td class="px-3 py-2 text-xs text-gray-500"><?php echo date('d/m/Y', strtotime($reg['fecha_creacion'])); ?></td>
                                <td class="px-3 py-2 text-center">
                                    <button onclick="editarRegistro(<?php echo $reg['id']; ?>, '<?php echo $reg['tipo']; ?>')" class="text-blue-600 hover:text-blue-800 mx-1">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                                    <button onclick="eliminarRegistro(<?php echo $reg['id']; ?>, '<?php echo $reg['tipo']; ?>')" class="text-red-600 hover:text-red-800 mx-1">
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