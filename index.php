<?php
session_start();

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delegados | Escuela Internacional de Psicología</title>
    <link rel="icon" type="image/webp" href="assets/images/favicon.webp">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md p-8">
        <div class="text-center mb-8">
            <img src="assets/images/logo.webp" alt="Logo" class="mx-auto h-20 mb-4" onerror="this.style.display='none'">
            <h1 class="text-2xl font-bold text-gray-800">Escuela Internacional de Psicología</h1>
            <p class="text-gray-600 mt-2">Sistema de Delegados</p>
        </div>

        <form id="loginForm" method="POST" action="auth/login.php" class="space-y-4">
            <div>
                <label class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-user mr-2"></i>Usuario
                </label>
                <input type="text" name="usuario" required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-lock mr-2"></i>Contraseña
                </label>
                <input type="password" name="contrasena" required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
            </div>

            <button type="submit" 
                class="w-full bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 transition duration-300">
                <i class="fas fa-sign-in-alt mr-2"></i>Ingresar
            </button>
        </form>

        <div class="mt-6 text-center">
            <button onclick="openRegistroModal()" 
                class="text-purple-600 hover:text-purple-800 font-semibold">
                <i class="fas fa-user-plus mr-2"></i>Registro de Delegados
            </button>
        </div>
    </div>

    <!-- Modal de Registro -->
    <div id="registroModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="bg-purple-600 text-white p-6 rounded-t-lg">
                <h2 class="text-2xl font-bold">
                    <i class="fas fa-user-plus mr-2"></i>Registro de Delegado
                </h2>
            </div>

            <form id="registroForm" method="POST" action="auth/register.php" class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Nombre *</label>
                        <input type="text" name="nombre" id="nombre" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Apellidos *</label>
                        <input type="text" name="apellidos" id="apellidos" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">País *</label>
                        <select name="pais" id="pais" required onchange="cargarCiudades(this.value)"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            <option value="">Seleccione un país</option>
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
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Ciudad *</label>
                        <select name="ciudad" id="ciudad" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                            <option value="">Seleccione primero un país</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Teléfono / WhatsApp *</label>
                        <div class="flex">
                            <span id="prefijo" class="inline-flex items-center px-3 border border-r-0 border-gray-300 bg-gray-50 rounded-l-lg text-gray-600"></span>
                            <input type="tel" name="telefono" id="telefono" required 
                                pattern="[0-9]+" 
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Centro de Estudios *</label>
                        <input type="text" name="centro_estudios" id="centro_estudios" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Usuario *</label>
                        <input type="text" name="usuario" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Contraseña *</label>
                        <input type="password" name="contrasena" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                    </div>
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="submit" 
                        class="flex-1 bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 transition duration-300">
                        <i class="fas fa-save mr-2"></i>Registrar
                    </button>
                    <button type="button" onclick="closeRegistroModal()" 
                        class="flex-1 bg-gray-500 text-white py-3 rounded-lg font-semibold hover:bg-gray-600 transition duration-300">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        function openRegistroModal() {
            document.getElementById('registroModal').classList.remove('hidden');
            document.getElementById('registroModal').classList.add('flex');
        }

        function closeRegistroModal() {
            document.getElementById('registroModal').classList.add('hidden');
            document.getElementById('registroModal').classList.remove('flex');
        }
    </script>

</body>
</html>