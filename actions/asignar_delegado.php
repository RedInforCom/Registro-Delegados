<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] == 'delegado') {
    header('Location: ../dashboard.php');
    exit;
}

$conn = getConnection();

// Si es POST, procesar asignación
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario_id = intval($_POST['usuario_id']);
    $delegado_id = intval($_POST['delegado_id']);
    
    // Actualizar asignación
    $stmt = $conn->prepare("UPDATE usuarios SET delegado_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $delegado_id, $usuario_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('¡Delegado asignado exitosamente!'); window.location.href='../dashboard.php';</script>";
    } else {
        echo "<script>alert('Error al asignar delegado.'); window.location.href='../dashboard.php';</script>";
    }
    
    $stmt->close();
    $conn->close();
    exit;
}

// Si es GET, mostrar formulario
if (isset($_GET['id'])) {
    $usuario_id = intval($_GET['id']);
    
    // Obtener datos del usuario
    $stmt = $conn->prepare("SELECT u.*, us.nombre as delegado_nombre, us.apellidos as delegado_apellidos 
                            FROM usuarios u 
                            LEFT JOIN usuarios_sistema us ON u.delegado_id = us.id 
                            WHERE u.id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo "<script>alert('Usuario no encontrado.'); window.location.href='../dashboard.php';</script>";
        exit;
    }
    
    $usuario = $result->fetch_assoc();
    $stmt->close();
    
    // Obtener lista de delegados
    $delegados = $conn->query("SELECT id, nombre, apellidos FROM usuarios_sistema WHERE tipo_usuario = 'delegado' ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);
    
} else {
    header('Location: ../dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Delegado | Delegados</title>
    <link rel="icon" type="image/webp" href="../assets/images/favicon.webp">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="ml-64">
        <?php include '../includes/header.php'; ?>
        
        <main class="p-6 pt-24 pb-24 min-h-screen">
            
            <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-user-plus mr-2 text-green-600"></i>
                        Asignar Delegado
                    </h2>
                    <a href="../dashboard.php" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-arrow-left mr-2"></i>Volver
                    </a>
                </div>

                <!-- Información del Usuario -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h3 class="font-bold text-blue-800 mb-2">
                        <i class="fas fa-user mr-2"></i>Usuario:
                    </h3>
                    <p class="text-gray-700">
                        <strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']); ?><br>
                        <strong>País:</strong> <?php echo htmlspecialchars($usuario['pais']); ?><br>
                        <strong>Ciudad:</strong> <?php echo htmlspecialchars($usuario['ciudad']); ?><br>
                        <strong>Centro de Estudios:</strong> <?php echo htmlspecialchars($usuario['centro_estudios']); ?>
                    </p>
                    
                    <?php if ($usuario['delegado_nombre']): ?>
                    <div class="mt-3 pt-3 border-t border-blue-200">
                        <p class="text-sm text-blue-700">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Delegado actual:</strong> <?php echo htmlspecialchars($usuario['delegado_nombre'] . ' ' . $usuario['delegado_apellidos']); ?>
                        </p>
                    </div>
                    <?php else: ?>
                    <div class="mt-3 pt-3 border-t border-blue-200">
                        <p class="text-sm text-orange-700">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Este usuario aún no tiene delegado asignado
                        </p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Formulario de Asignación -->
                <form method="POST" action="" class="space-y-6">
                    <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                    
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            <i class="fas fa-user-tie mr-2"></i>Seleccione el Delegado *
                        </label>
                        <select name="delegado_id" required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600">
                            <option value="">-- Seleccione un delegado --</option>
                            <?php foreach ($delegados as $delegado): ?>
                                <option value="<?php echo $delegado['id']; ?>" 
                                    <?php echo ($usuario['delegado_id'] == $delegado['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($delegado['nombre'] . ' ' . $delegado['apellidos']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-sm text-gray-500 mt-2">
                            <i class="fas fa-lightbulb mr-1"></i>
                            El usuario será asignado al delegado seleccionado
                        </p>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" 
                            class="flex-1 bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition duration-300">
                            <i class="fas fa-check mr-2"></i>Asignar Delegado
                        </button>
                        <a href="../dashboard.php" 
                            class="flex-1 bg-gray-500 text-white py-3 rounded-lg font-semibold hover:bg-gray-600 transition duration-300 text-center">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>

        </main>
        
        <?php include '../includes/footer.php'; ?>
    </div>

</body>
</html>
<?php $conn->close(); ?>