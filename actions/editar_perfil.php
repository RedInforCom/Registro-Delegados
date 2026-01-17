<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] == 'delegado') {
    header('Location: ../dashboard.php');
    exit;
}

$conn = getConnection();

// Si es POST, procesar actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_SESSION['usuario_id'];
    $nombre = capitalizarNombre(sanitize($_POST['nombre']));
    $apellidos = capitalizarNombre(sanitize($_POST['apellidos']));
    $telefono = sanitize($_POST['telefono']);
    $email = sanitize($_POST['email']);
    $usuario = sanitize($_POST['usuario']);
    
    // Si hay nueva contraseña
    $update_password = false;
    if (!empty($_POST['nueva_contrasena'])) {
        $contrasena_actual = $_POST['contrasena_actual'];
        
        // Verificar contraseña actual
        $stmt = $conn->prepare("SELECT contrasena FROM usuarios_sistema WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (!password_verify($contrasena_actual, $user['contrasena'])) {
            echo "<script>alert('La contraseña actual es incorrecta.'); window.history.back();</script>";
            exit;
        }
        
        $nueva_contrasena = password_hash($_POST['nueva_contrasena'], PASSWORD_DEFAULT);
        $update_password = true;
    }
    
    // Verificar si el usuario ya existe (excepto el actual)
    $stmt = $conn->prepare("SELECT id FROM usuarios_sistema WHERE usuario = ? AND id != ?");
    $stmt->bind_param("si", $usuario, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<script>alert('El usuario ya existe. Por favor, elija otro.'); window.history.back();</script>";
        exit;
    }
    
    $stmt->close();
    
    // Actualizar perfil
    if ($update_password) {
        $stmt = $conn->prepare("UPDATE usuarios_sistema SET nombre = ?, apellidos = ?, telefono = ?, email = ?, usuario = ?, contrasena = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $nombre, $apellidos, $telefono, $email, $usuario, $nueva_contrasena, $id);
    } else {
        $stmt = $conn->prepare("UPDATE usuarios_sistema SET nombre = ?, apellidos = ?, telefono = ?, email = ?, usuario = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $nombre, $apellidos, $telefono, $email, $usuario, $id);
    }
    
    if ($stmt->execute()) {
        // Actualizar sesión
        $_SESSION['usuario_nombre'] = $nombre . ' ' . $apellidos;
        $_SESSION['usuario_user'] = $usuario;
        
        echo "<script>alert('¡Perfil actualizado exitosamente!'); window.location.href='../dashboard.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar perfil.'); window.location.href='../dashboard.php';</script>";
    }
    
    $stmt->close();
    $conn->close();
    exit;
}

// Si es GET, mostrar formulario
$id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT * FROM usuarios_sistema WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$perfil = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil | Delegados</title>
    <link rel="icon" type="image/webp" href="../assets/images/favicon.webp">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="ml-64">
        <?php include '../includes/header.php'; ?>
        
        <main class="p-6 pt-24 pb-24 min-h-screen">
            
            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-user-cog mr-2 text-purple-600"></i>
                        Editar Mi Perfil
                    </h2>
                    <a href="../dashboard.php" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-arrow-left mr-2"></i>Volver
                    </a>
                </div>

                <form method="POST" action="" class="space-y-6">
                    
                    <!-- Información Personal -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">
                            <i class="fas fa-user mr-2"></i>Información Personal
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Nombre *</label>
                                <input type="text" name="nombre" id="nombre" required 
                                    value="<?php echo htmlspecialchars($perfil['nombre']); ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            </div>

                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Apellidos *</label>
                                <input type="text" name="apellidos" id="apellidos" required 
                                    value="<?php echo htmlspecialchars($perfil['apellidos']); ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            </div>

                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Teléfono *</label>
                                <input type="tel" name="telefono" required 
                                    value="<?php echo htmlspecialchars($perfil['telefono']); ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            </div>

                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Email *</label>
                                <input type="email" name="email" required 
                                    value="<?php echo htmlspecialchars($perfil['email']); ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            </div>
                        </div>
                    </div>

                    <!-- Credenciales -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">
                            <i class="fas fa-key mr-2"></i>Credenciales de Acceso
                        </h3>
                        
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Usuario *</label>
                                <input type="text" name="usuario" required 
                                    value="<?php echo htmlspecialchars($perfil['usuario']); ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            </div>
                        </div>
                    </div>

                    <!-- Cambiar Contraseña -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 mb-4 pb-2 border-b">
                            <i class="fas fa-lock mr-2"></i>Cambiar Contraseña (Opcional)
                        </h3>
                        
                        <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-3 mb-4">
                            <p class="text-sm text-yellow-800">
                                <i class="fas fa-info-circle mr-2"></i>
                                Solo complete estos campos si desea cambiar su contraseña
                            </p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Contraseña Actual</label>
                                <input type="password" name="contrasena_actual" id="contrasena_actual"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            </div>

                            <div>
                                <label class="block text-gray-700 font-semibold mb-2">Nueva Contraseña</label>
                                <input type="password" name="nueva_contrasena" id="nueva_contrasena"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="submit" 
                            class="flex-1 bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 transition duration-300">
                            <i class="fas fa-save mr-2"></i>Guardar Cambios
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

    <script>
        // Validaciones
        document.getElementById('nombre').addEventListener('input', function() {
            this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1);
        });
        
        document.getElementById('apellidos').addEventListener('input', function() {
            this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1);
        });

        // Validar que si ingresa nueva contraseña, también ingrese la actual
        document.querySelector('form').addEventListener('submit', function(e) {
            const nuevaPass = document.getElementById('nueva_contrasena').value;
            const actualPass = document.getElementById('contrasena_actual').value;
            
            if (nuevaPass && !actualPass) {
                e.preventDefault();
                alert('Debe ingresar su contraseña actual para cambiarla.');
                document.getElementById('contrasena_actual').focus();
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>