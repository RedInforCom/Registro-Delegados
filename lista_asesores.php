<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'administrador') {
    header('Location: dashboard.php');
    exit;
}

$conn = getConnection();
$result = $conn->query("SELECT * FROM usuarios_sistema WHERE tipo_usuario = 'asesor' ORDER BY fecha_creacion DESC");
$asesores = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Asesores | Delegados</title>
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
                        <i class="fas fa-user-tie mr-2"></i>Lista de Asesores
                    </h2>
                    <div class="text-gray-600">
                        Total: <span class="font-bold"><?php echo count($asesores); ?></span> asesores
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-green-700 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left">Nombre Completo</th>
                                <th class="px-4 py-3 text-left">País</th>
                                <th class="px-4 py-3 text-left">Ciudad</th>
                                <th class="px-4 py-3 text-left">Teléfono</th>
                                <th class="px-4 py-3 text-left">Email</th>
                                <th class="px-4 py-3 text-left">Usuario</th>
                                <th class="px-4 py-3 text-left">Fecha Registro</th>
                                <th class="px-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($asesores as $asesor): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3 font-semibold"><?php echo htmlspecialchars($asesor['nombre'] . ' ' . $asesor['apellidos']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($asesor['pais']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($asesor['ciudad']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($asesor['telefono']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($asesor['email']); ?></td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($asesor['usuario']); ?></td>
                                <td class="px-4 py-3 text-xs text-gray-500"><?php echo date('d/m/Y H:i', strtotime($asesor['fecha_creacion'])); ?></td>
                                <td class="px-4 py-3 text-center">
                                    <button onclick="editarAsesor(<?php echo $asesor['id']; ?>)" class="text-blue-600 hover:text-blue-800 mr-2" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="eliminarAsesor(<?php echo $asesor['id']; ?>)" class="text-red-600 hover:text-red-800" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
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
        function editarAsesor(id) {
            mostrarInfo('Función de edición en desarrollo.<br>Asesor ID: ' + id);
        }

        function eliminarAsesor(id) {
            mostrarConfirmacion('¿Está seguro de eliminar este asesor?<br><br>Esta acción no se puede deshacer.', function(confirmado) {
                if (confirmado) {
                    window.location.href = 'actions/eliminar_usuario.php?id=' + id + '&tipo=asesor';
                }
            });
        }
    </script>

</body>
</html>