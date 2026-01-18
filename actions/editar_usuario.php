<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../dashboard.php');
    exit;
}

$conn = getConnection();

// Si es POST, procesar actualización
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $nombre = capitalizarNombre(sanitize($_POST['nombre']));
    $apellidos = capitalizarNombre(sanitize($_POST['apellidos']));
    $pais = sanitize($_POST['pais']);
    $ciudad = sanitize($_POST['ciudad']);
    $telefono = getPrefijoPais($pais) . sanitize($_POST['telefono']);
    $centro_estudios = capitalizarNombre(sanitize($_POST['centro_estudios']));
    $notas = sanitize($_POST['notas']);
    
    // Verificar permisos
    if ($_SESSION['usuario_tipo'] == 'delegado') {
        // Verificar que el usuario pertenece a este delegado
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE id = ? AND delegado_id = ?");
        $stmt->bind_param("ii", $id, $_SESSION['usuario_id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows == 0) {
            echo "<script>alert('No tiene permisos para editar este usuario.'); window.location.href='../dashboard.php';</script>";
            exit;
        }
    }
    
    // Actualizar usuario
    $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, apellidos = ?, pais = ?, ciudad = ?, telefono = ?, centro_estudios = ?, notas = ? WHERE id = ?");
    $stmt->bind_param("sssssssi", $nombre, $apellidos, $pais, $ciudad, $telefono, $centro_estudios, $notas, $id);
    
    if ($stmt->execute()) {
        echo "<script>alert('¡Usuario actualizado exitosamente!'); window.location.href='../dashboard.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar usuario.'); window.location.href='../dashboard.php';</script>";
    }
    
    $stmt->close();
    $conn->close();
    exit;
}

// Si es GET, mostrar formulario
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Verificar permisos y obtener datos
    if ($_SESSION['usuario_tipo'] == 'delegado') {
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ? AND delegado_id = ?");
        $stmt->bind_param("ii", $id, $_SESSION['usuario_id']);
    } else {
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        echo "<script>alert('Usuario no encontrado o sin permisos.'); window.location.href='../dashboard.php';</script>";
        exit;
    }
    
    $usuario = $result->fetch_assoc();
    
    // Limpiar el prefijo del teléfono para mostrarlo
    $prefijo = getPrefijoPais($usuario['pais']);
    $telefono_sin_prefijo = str_replace($prefijo, '', $usuario['telefono']);
    
    $stmt->close();
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
    <title>Editar Usuario | Delegados</title>
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
                        <i class="fas fa-user-edit mr-2 text-blue-600"></i>
                        Editar Usuario
                    </h2>
                    <a href="../dashboard.php" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-arrow-left mr-2"></i>Volver
                    </a>
                </div>

                <form method="POST" action="" class="space-y-4">
                    <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Nombre *</label>
                            <input type="text" name="nombre" id="nombre" required 
                                value="<?php echo htmlspecialchars($usuario['nombre']); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Apellidos *</label>
                            <input type="text" name="apellidos" id="apellidos" required 
                                value="<?php echo htmlspecialchars($usuario['apellidos']); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">País *</label>
                            <select name="pais" id="pais" required onchange="cargarCiudadesEdit(this.value)"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                                <option value="">Seleccione un país</option>
                                <?php
                                $paises = ['Argentina', 'Bolivia', 'Brasil', 'Chile', 'Colombia', 'Costa Rica', 'Cuba', 'Ecuador', 'El Salvador', 'España', 'Estados Unidos', 'Guatemala', 'Honduras', 'México', 'Nicaragua', 'Panamá', 'Paraguay', 'Perú', 'Puerto Rico', 'República Dominicana', 'Uruguay', 'Venezuela'];
                                foreach ($paises as $p) {
                                    $selected = ($p == $usuario['pais']) ? 'selected' : '';
                                    echo "<option value='$p' $selected>$p</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Ciudad *</label>
                            <select name="ciudad" id="ciudad" required 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                                <option value="<?php echo htmlspecialchars($usuario['ciudad']); ?>" selected>
                                    <?php echo htmlspecialchars($usuario['ciudad']); ?>
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Teléfono / WhatsApp *</label>
                            <div class="flex">
                                <span id="prefijo" class="inline-flex items-center px-3 border border-r-0 border-gray-300 bg-gray-50 rounded-l-lg text-gray-600">
                                    <?php echo $prefijo; ?>
                                </span>
                                <input type="tel" name="telefono" id="telefono" required pattern="[0-9]+"
                                    value="<?php echo htmlspecialchars($telefono_sin_prefijo); ?>"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                            </div>
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Centro de Estudios *</label>
                            <input type="text" name="centro_estudios" id="centro_estudios" required 
                                value="<?php echo htmlspecialchars($usuario['centro_estudios']); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-gray-700 font-semibold mb-2">
                                <i class="fas fa-sticky-note mr-2"></i>Notas Adicionales
                            </label>
                            <textarea name="notas" rows="4" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                                placeholder="Agregue notas o comentarios sobre este usuario..."><?php echo htmlspecialchars($usuario['notas'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button type="submit" 
                            class="flex-1 bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">
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

    <script src="../assets/js/main.js"></script>
    <script>
        // Cargar ciudades al inicio
        document.addEventListener('DOMContentLoaded', function() {
            cargarCiudadesEdit('<?php echo $usuario['pais']; ?>');
        });

        function cargarCiudadesEdit(pais) {
            const selectCiudad = document.getElementById('ciudad');
            const prefijo = document.getElementById('prefijo');
            const ciudadActual = '<?php echo htmlspecialchars($usuario['ciudad']); ?>';
            
            selectCiudad.innerHTML = '<option value="">Seleccione una ciudad</option>';
            
            if (pais && ciudadesPorPais[pais]) {
                ciudadesPorPais[pais].forEach(ciudad => {
                    const option = document.createElement('option');
                    option.value = ciudad;
                    option.textContent = ciudad;
                    if (ciudad === ciudadActual) {
                        option.selected = true;
                    }
                    selectCiudad.appendChild(option);
                });
            }
            
            if (prefijo && prefijosPais[pais]) {
                prefijo.textContent = prefijosPais[pais];
            }
        }

        // Validaciones en tiempo real
        document.getElementById('nombre').addEventListener('input', function() {
            this.value = capitalizarNombre(this.value);
        });
        
        document.getElementById('apellidos').addEventListener('input', function() {
            this.value = capitalizarNombre(this.value);
        });
        
        document.getElementById('centro_estudios').addEventListener('input', function() {
            this.value = capitalizarNombre(this.value);
        });
        
        document.getElementById('telefono').addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>