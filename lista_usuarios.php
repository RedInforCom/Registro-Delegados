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
$delegados = [];
if ($_SESSION['usuario_tipo'] != 'delegado') {
    $delegados_result = $conn->query("SELECT id, nombre, apellidos FROM usuarios_sistema WHERE tipo_usuario = 'delegado' ORDER BY nombre");
    $delegados = $delegados_result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuarios | Delegados</title>
    <link rel="icon" type="image/webp" href="assets/images/favicon.webp">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="assets/js/notifications.js"></script>
</head>
<body class="bg-gray-100">
    
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="ml-64">
        <?php include 'includes/header.php'; ?>
        
        <main class="p-6 pt-24 min-h-screen pb-20">
            
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-list mr-2"></i>
                        <?php echo $_SESSION['usuario_tipo'] == 'delegado' ? 'Mis Usuarios' : 'Delegados y Usuarios'; ?>
                    </h2>
                    <div class="text-gray-600">
                        Total: <span class="font-bold"><?php echo count($registros); ?></span> registros
                    </div>
                </div>

                <!-- Filtros -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Buscar</label>
                        <input type="text" id="searchInput" placeholder="Nombre, teléfono, ciudad..." 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">País</label>
                        <select id="filtroPais" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <option value="">Todos los países</option>
                            <?php foreach ($paises as $pais): ?>
                            <option value="<?php echo htmlspecialchars($pais); ?>"><?php echo htmlspecialchars($pais); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo</label>
                        <select id="filtroTipo" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <option value="">Todos</option>
                            <option value="delegado">Delegados</option>
                            <option value="usuario">Usuarios</option>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Ordenar por Fecha</label>
                        <select id="ordenFecha" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <option value="desc">Más reciente primero</option>
                            <option value="asc">Más antiguo primero</option>
                        </select>
                    </div>
                </div>

                <!-- Tabla -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-blue-900 text-white">
                            <tr>
                                <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                                <th class="px-4 py-3 text-left">Tipo</th>
                                <?php endif; ?>
                                <th class="px-4 py-3 text-left">Nombre Completo</th>
                                <th class="px-4 py-3 text-left">País</th>
                                <th class="px-4 py-3 text-left">Ciudad</th>
                                <th class="px-4 py-3 text-left">Teléfono</th>
                                <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                                <th class="px-4 py-3 text-left">Delegado Asignado</th>
                                <?php endif; ?>
                                <th class="px-4 py-3 text-left">Fecha Registro</th>
                                <th class="px-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaRegistros">
                            <?php foreach ($registros as $reg): ?>
                            <tr class="border-b hover:bg-gray-50" data-tipo="<?php echo $reg['tipo']; ?>" data-pais="<?php echo htmlspecialchars($reg['pais']); ?>" data-fecha="<?php echo strtotime($reg['fecha_creacion']); ?>">
                                <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded text-xs font-semibold <?php echo $reg['tipo'] == 'delegado' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'; ?>">
                                        <?php echo ucfirst($reg['tipo']); ?>
                                    </span>
                                </td>
                                <?php endif; ?>
                                <td class="px-4 py-3 font-semibold"><?php echo htmlspecialchars($reg['nombre'] . ' ' . $reg['apellidos']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($reg['pais']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($reg['ciudad']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($reg['telefono']); ?></td>
                                <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                                <td class="px-4 py-3">
                                    <?php if ($reg['tipo'] == 'usuario'): ?>
                                        <?php if ($reg['delegado_nombre']): ?>
                                            <?php echo htmlspecialchars($reg['delegado_nombre'] . ' ' . $reg['delegado_apellidos']); ?>
                                        <?php else: ?>
                                            <span class="text-gray-400">Sin asignar</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                                <td class="px-4 py-3 text-xs text-gray-500"><?php echo date('d/m/Y H:i', strtotime($reg['fecha_creacion'])); ?></td>
                                <td class="px-4 py-3 text-center">
                                    <button onclick="editarRegistro(<?php echo $reg['id']; ?>, '<?php echo $reg['tipo']; ?>')" class="text-blue-600 hover:text-blue-800 mr-2" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($_SESSION['usuario_tipo'] != 'delegado' && $reg['tipo'] == 'usuario'): ?>
                                    <button onclick="asignarDelegado(<?php echo $reg['id']; ?>)" class="text-green-600 hover:text-green-800 mr-2" title="Asignar Delegado">
                                        <i class="fas fa-user-plus"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                                    <button onclick="eliminarRegistro(<?php echo $reg['id']; ?>, '<?php echo $reg['tipo']; ?>')" class="text-red-600 hover:text-red-800" title="Eliminar">
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
        // Búsqueda en tiempo real
        document.getElementById('searchInput').addEventListener('input', filtrarTabla);
        document.getElementById('filtroPais').addEventListener('change', filtrarTabla);
        <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
        document.getElementById('filtroTipo').addEventListener('change', filtrarTabla);
        <?php endif; ?>
        document.getElementById('ordenFecha').addEventListener('change', ordenarTabla);

        function filtrarTabla() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const pais = document.getElementById('filtroPais').value.toLowerCase();
            <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
            const tipo = document.getElementById('filtroTipo').value.toLowerCase();
            <?php endif; ?>
            
            const filas = document.querySelectorAll('#tablaRegistros tr');
            
            filas.forEach(fila => {
                const texto = fila.textContent.toLowerCase();
                const filaPais = fila.dataset.pais.toLowerCase();
                <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                const filaTipo = fila.dataset.tipo.toLowerCase();
                <?php endif; ?>
                
                let mostrar = true;
                
                if (search && !texto.includes(search)) mostrar = false;
                if (pais && filaPais !== pais) mostrar = false;
                <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                if (tipo && filaTipo !== tipo) mostrar = false;
                <?php endif; ?>
                
                fila.style.display = mostrar ? '' : 'none';
            });
        }

        function ordenarTabla() {
            const orden = document.getElementById('ordenFecha').value;
            const tbody = document.getElementById('tablaRegistros');
            const filas = Array.from(tbody.querySelectorAll('tr'));
            
            filas.sort((a, b) => {
                const fechaA = parseInt(a.dataset.fecha);
                const fechaB = parseInt(b.dataset.fecha);
                return orden === 'asc' ? fechaA - fechaB : fechaB - fechaA;
            });
            
            filas.forEach(fila => tbody.appendChild(fila));
        }

        function editarRegistro(id, tipo) {
            mostrarInfo('Función de edición en desarrollo.<br>ID: ' + id + '<br>Tipo: ' + tipo);
        }

        function asignarDelegado(id) {
            mostrarInfo('Función de asignación en desarrollo.<br>Usuario ID: ' + id);
        }

        function eliminarRegistro(id, tipo) {
            const mensaje = tipo === 'delegado' 
                ? '¿Está seguro de desactivar este delegado?<br><br>El delegado perderá acceso al sistema.'
                : '¿Está seguro de eliminar este usuario?<br><br>Esta acción no se puede deshacer.';
                
            mostrarConfirmacion(mensaje, function(confirmado) {
                if (confirmado) {
                    window.location.href = 'actions/eliminar_usuario.php?id=' + id + '&tipo=' + tipo;
                }
            });
        }
    </script>

</body>
</html>