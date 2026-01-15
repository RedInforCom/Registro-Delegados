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

// Obtener lista combinada de delegados y usuarios
$lista_completa = [];

if ($_SESSION['usuario_tipo'] == 'delegado') {
    // El delegado solo ve sus usuarios
    $stmt = $conn->prepare("SELECT u.*, 'usuario' as tipo_registro, us.nombre as delegado_nombre, us.apellidos as delegado_apellidos 
                            FROM usuarios u 
                            LEFT JOIN usuarios_sistema us ON u.delegado_id = us.id 
                            WHERE u.delegado_id = ? 
                            ORDER BY u.fecha_creacion DESC");
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $lista_completa = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    // Admin y Asesor ven todo: delegados y usuarios mezclados
    
    // Obtener delegados
    $delegados = $conn->query("SELECT id, nombre, apellidos, pais, ciudad, telefono, centro_estudios, usuario, 'delegado' as tipo_registro, fecha_creacion, NULL as delegado_nombre, NULL as delegado_apellidos, NULL as notas 
                               FROM usuarios_sistema 
                               WHERE tipo_usuario = 'delegado' 
                               ORDER BY fecha_creacion DESC")->fetch_all(MYSQLI_ASSOC);
    
    // Obtener usuarios
    $usuarios = $conn->query("SELECT u.*, 'usuario' as tipo_registro, us.nombre as delegado_nombre, us.apellidos as delegado_apellidos 
                              FROM usuarios u 
                              LEFT JOIN usuarios_sistema us ON u.delegado_id = us.id 
                              ORDER BY u.fecha_creacion DESC")->fetch_all(MYSQLI_ASSOC);
    
    // Combinar ambas listas
    $lista_completa = array_merge($delegados, $usuarios);
    
    // Ordenar por fecha
    usort($lista_completa, function($a, $b) {
        return strtotime($b['fecha_creacion']) - strtotime($a['fecha_creacion']);
    });
}

// Obtener lista de delegados para el selector
$delegados_selector = [];
if ($_SESSION['usuario_tipo'] != 'delegado') {
    $delegados_selector = $conn->query("SELECT id, nombre, apellidos FROM usuarios_sistema WHERE tipo_usuario = 'delegado' ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);
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
</head>
<body class="bg-gray-100">
    
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="ml-64">
        <?php include 'includes/header.php'; ?>
        
        <main class="p-6 pt-24 pb-24 min-h-screen">
            
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

            <!-- Lista Unificada de Delegados y Usuarios -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="mb-4">
                    <h3 class="text-xl font-bold mb-4">
                        <i class="fas fa-list mr-2"></i>
                        <?php echo $_SESSION['usuario_tipo'] == 'delegado' ? 'Mis Usuarios' : 'Lista de Delegados y Usuarios'; ?>
                    </h3>
                    
                    <!-- Filtros y Buscador -->
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                        <input type="text" id="buscarGeneral" placeholder="Buscar por nombre..." 
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                        
                        <select id="filtroTipo" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            <option value="">Todos los tipos</option>
                            <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                            <option value="delegado">Solo Delegados</option>
                            <?php endif; ?>
                            <option value="usuario">Solo Usuarios</option>
                        </select>
                        
                        <select id="filtroPais" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            <option value="">Todos los países</option>
                            <option value="Argentina">Argentina</option>
                            <option value="Bolivia">Bolivia</option>
                            <option value="Brasil">Brasil</option>
                            <option value="Chile">Chile</option>
                            <option value="Colombia">Colombia</option>
                            <option value="Costa Rica">Costa Rica</option>
                            <option value="Cuba">Cuba</option>
                            <option value="Ecuador">Ecuador</option>
                            <option value="El Salvador">El Salvador</option>
                            <option value="España">España</option>
                            <option value="Estados Unidos">Estados Unidos</option>
                            <option value="Guatemala">Guatemala</option>
                            <option value="Honduras">Honduras</option>
                            <option value="México">México</option>
                            <option value="Nicaragua">Nicaragua</option>
                            <option value="Panamá">Panamá</option>
                            <option value="Paraguay">Paraguay</option>
                            <option value="Perú">Perú</option>
                            <option value="Puerto Rico">Puerto Rico</option>
                            <option value="República Dominicana">República Dominicana</option>
                            <option value="Uruguay">Uruguay</option>
                            <option value="Venezuela">Venezuela</option>
                        </select>
                        
                        <input type="text" id="filtroCiudad" placeholder="Filtrar por ciudad..." 
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                        
                        <button onclick="limpiarFiltros()" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                            <i class="fas fa-redo mr-2"></i>Limpiar
                        </button>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gradient-to-r from-purple-600 to-blue-600 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left">Tipo</th>
                                <th class="px-4 py-3 text-left">Nombre</th>
                                <th class="px-4 py-3 text-left">País</th>
                                <th class="px-4 py-3 text-left">Ciudad</th>
                                <th class="px-4 py-3 text-left">Teléfono</th>
                                <th class="px-4 py-3 text-left">Centro de Estudios</th>
                                <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                                <th class="px-4 py-3 text-left">Delegado Asignado</th>
                                <?php endif; ?>
                                <th class="px-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaCompleta">
                            <?php foreach ($lista_completa as $item): ?>
                            <tr class="border-b hover:bg-gray-50" 
                                data-tipo="<?php echo $item['tipo_registro']; ?>"
                                data-pais="<?php echo htmlspecialchars($item['pais']); ?>"
                                data-ciudad="<?php echo htmlspecialchars($item['ciudad']); ?>"
                                data-nombre="<?php echo htmlspecialchars($item['nombre'] . ' ' . $item['apellidos']); ?>">
                                
                                <td class="px-4 py-3">
                                    <?php if ($item['tipo_registro'] == 'delegado'): ?>
                                        <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs font-semibold">
                                            <i class="fas fa-user-tie mr-1"></i>Delegado
                                        </span>
                                    <?php else: ?>
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-semibold">
                                            <i class="fas fa-user mr-1"></i>Usuario
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="px-4 py-3 font-semibold"><?php echo htmlspecialchars($item['nombre'] . ' ' . $item['apellidos']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($item['pais']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($item['ciudad']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($item['telefono']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($item['centro_estudios'] ?? 'N/A'); ?></td>
                                
                                <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                                <td class="px-4 py-3">
                                    <?php 
                                    if ($item['tipo_registro'] == 'usuario') {
                                        if ($item['delegado_nombre']) {
                                            echo '<span class="text-sm">' . htmlspecialchars($item['delegado_nombre'] . ' ' . $item['delegado_apellidos']) . '</span>';
                                        } else {
                                            echo '<span class="text-gray-400 text-sm">Sin asignar</span>';
                                        }
                                    } else {
                                        echo '<span class="text-gray-400 text-sm">-</span>';
                                    }
                                    ?>
                                </td>
                                <?php endif; ?>
                                
                                <td class="px-4 py-3 text-center">
                                    <button onclick="editar<?php echo ucfirst($item['tipo_registro']); ?>(<?php echo $item['id']; ?>)" 
                                        class="text-blue-600 hover:text-blue-800 mr-2" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                                        <?php if ($item['tipo_registro'] == 'usuario'): ?>
                                        <button onclick="asignarDelegado(<?php echo $item['id']; ?>)" 
                                            class="text-green-600 hover:text-green-800 mr-2" title="Asignar Delegado">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                        <?php endif; ?>
                                        
                                        <button onclick="eliminar<?php echo ucfirst($item['tipo_registro']); ?>(<?php echo $item['id']; ?>)" 
                                            class="text-red-600 hover:text-red-800" title="Eliminar">
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

        // Filtros en tiempo real
        function aplicarFiltros() {
            const buscar = document.getElementById('buscarGeneral').value.toLowerCase();
            const tipo = document.getElementById('filtroTipo').value;
            const pais = document.getElementById('filtroPais').value;
            const ciudad = document.getElementById('filtroCiudad').value.toLowerCase();
            
            const rows = document.querySelectorAll('#tablaCompleta tr');
            
            rows.forEach(row => {
                const nombre = row.dataset.nombre?.toLowerCase() || '';
                const rowTipo = row.dataset.tipo || '';
                const rowPais = row.dataset.pais || '';
                const rowCiudad = row.dataset.ciudad?.toLowerCase() || '';
                
                const matchBuscar = nombre.includes(buscar);
                const matchTipo = !tipo || rowTipo === tipo;
                const matchPais = !pais || rowPais === pais;
                const matchCiudad = !ciudad || rowCiudad.includes(ciudad);
                
                row.style.display = (matchBuscar && matchTipo && matchPais && matchCiudad) ? '' : 'none';
            });
        }

        document.getElementById('buscarGeneral').addEventListener('input', aplicarFiltros);
        document.getElementById('filtroTipo').addEventListener('change', aplicarFiltros);
        document.getElementById('filtroPais').addEventListener('change', aplicarFiltros);
        document.getElementById('filtroCiudad').addEventListener('input', aplicarFiltros);

        function limpiarFiltros() {
            document.getElementById('buscarGeneral').value = '';
            document.getElementById('filtroTipo').value = '';
            document.getElementById('filtroPais').value = '';
            document.getElementById('filtroCiudad').value = '';
            aplicarFiltros();
        }

        // Funciones de acciones
        function editarUsuario(id) {
            window.location.href = 'actions/editar_usuario.php?id=' + id;
        }

        function editarDelegado(id) {
            window.location.href = 'actions/editar_delegado.php?id=' + id;
        }

        function eliminarUsuario(id) {
            if (confirm('¿Está seguro de eliminar este usuario?')) {
                window.location.href = 'actions/eliminar_usuario.php?id=' + id;
            }
        }

        function eliminarDelegado(id) {
            if (confirm('¿Está seguro de eliminar este delegado?')) {
                window.location.href = 'actions/eliminar_usuario.php?id=' + id + '&tipo=delegado';
            }
        }

        function asignarDelegado(id) {
            window.location.href = 'actions/asignar_delegado.php?id=' + id;
        }
    </script>
</body>
</html>