<aside class="bg-gradient-to-b from-purple-700 to-purple-900 w-64 min-h-screen fixed left-0 top-0 z-20 shadow-2xl">
    <div class="p-6">
        <img src="assets/images/logo.webp" alt="Logo" class="w-32 mx-auto mb-4" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Ctext y=%22.9em%22 font-size=%2290%22%3E🎓%3C/text%3E%3C/svg%3E'">
    </div>
    
    <nav class="px-4">
        <a href="dashboard.php" class="flex items-center px-4 py-3 text-white hover:bg-purple-600 rounded-lg mb-2 transition duration-300">
            <i class="fas fa-home mr-3"></i>
            Dashboard
        </a>
        
        <?php if ($_SESSION['usuario_tipo'] == 'administrador'): ?>
        
        <button onclick="abrirModal('modalCrearAsesor')" class="w-full flex items-center px-4 py-3 text-white hover:bg-purple-600 rounded-lg mb-2 transition duration-300">
            <i class="fas fa-user-tie mr-3"></i>
            Crear Asesor
        </button>
        
        <button onclick="abrirModal('modalCrearDelegado')" class="w-full flex items-center px-4 py-3 text-white hover:bg-purple-600 rounded-lg mb-2 transition duration-300">
            <i class="fas fa-users mr-3"></i>
            Crear Delegado
        </button>
        
        <button onclick="abrirModal('modalCrearUsuario')" class="w-full flex items-center px-4 py-3 text-white hover:bg-purple-600 rounded-lg mb-2 transition duration-300">
            <i class="fas fa-user-plus mr-3"></i>
            Crear Usuario
        </button>
        
        <?php elseif ($_SESSION['usuario_tipo'] == 'asesor'): ?>
        
        <button onclick="abrirModal('modalCrearDelegado')" class="w-full flex items-center px-4 py-3 text-white hover:bg-purple-600 rounded-lg mb-2 transition duration-300">
            <i class="fas fa-users mr-3"></i>
            Crear Delegado
        </button>
        
        <button onclick="abrirModal('modalCrearUsuario')" class="w-full flex items-center px-4 py-3 text-white hover:bg-purple-600 rounded-lg mb-2 transition duration-300">
            <i class="fas fa-user-plus mr-3"></i>
            Crear Usuario
        </button>
        
        <?php elseif ($_SESSION['usuario_tipo'] == 'delegado'): ?>
        
        <button onclick="abrirModal('modalCrearUsuario')" class="w-full flex items-center px-4 py-3 text-white hover:bg-purple-600 rounded-lg mb-2 transition duration-300">
            <i class="fas fa-user-plus mr-3"></i>
            Crear Usuario
        </button>
        
        <?php endif; ?>
    </nav>
</aside>

<!-- Modales -->

<!-- Modal Crear Asesor -->
<?php if ($_SESSION['usuario_tipo'] == 'administrador'): ?>
<div id="modalCrearAsesor" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="bg-green-600 text-white p-6 rounded-t-lg">
            <h2 class="text-2xl font-bold">
                <i class="fas fa-user-tie mr-2"></i>Crear Asesor
            </h2>
        </div>

        <form method="POST" action="actions/crear_asesor.php" class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Nombre *</label>
                    <input type="text" name="nombre" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Apellidos *</label>
                    <input type="text" name="apellidos" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Teléfono *</label>
                    <input type="tel" name="telefono" required pattern="[0-9+]+"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Email *</label>
                    <input type="email" name="email" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Usuario *</label>
                    <input type="text" name="usuario" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Contraseña *</label>
                    <input type="password" name="contrasena" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-600">
                </div>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" 
                    class="flex-1 bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition duration-300">
                    <i class="fas fa-save mr-2"></i>Crear Asesor
                </button>
                <button type="button" onclick="cerrarModal('modalCrearAsesor')" 
                    class="flex-1 bg-gray-500 text-white py-3 rounded-lg font-semibold hover:bg-gray-600 transition duration-300">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Modal Crear Delegado -->
<?php if ($_SESSION['usuario_tipo'] == 'administrador' || $_SESSION['usuario_tipo'] == 'asesor'): ?>
<div id="modalCrearDelegado" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="bg-purple-600 text-white p-6 rounded-t-lg">
            <h2 class="text-2xl font-bold">
                <i class="fas fa-users mr-2"></i>Crear Delegado
            </h2>
        </div>

        <form method="POST" action="actions/crear_delegado.php" class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Nombre *</label>
                    <input type="text" name="nombre" id="nombreDelegado" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Apellidos *</label>
                    <input type="text" name="apellidos" id="apellidosDelegado" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">País *</label>
                    <select name="pais" id="paisDelegado" required onchange="cargarCiudadesDelegado(this.value)"
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
                    <select name="ciudad" id="ciudadDelegado" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                        <option value="">Seleccione primero un país</option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Teléfono / WhatsApp *</label>
                    <div class="flex">
                        <span id="prefijoDelegado" class="inline-flex items-center px-3 border border-r-0 border-gray-300 bg-gray-50 rounded-l-lg text-gray-600"></span>
                        <input type="tel" name="telefono" id="telefonoDelegado" required pattern="[0-9]+"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Centro de Estudios *</label>
                    <input type="text" name="centro_estudios" id="centroDelegado" required 
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
                    <i class="fas fa-save mr-2"></i>Crear Delegado
                </button>
                <button type="button" onclick="cerrarModal('modalCrearDelegado')" 
                    class="flex-1 bg-gray-500 text-white py-3 rounded-lg font-semibold hover:bg-gray-600 transition duration-300">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Modal Crear Usuario -->
<div id="modalCrearUsuario" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="bg-blue-600 text-white p-6 rounded-t-lg">
            <h2 class="text-2xl font-bold">
                <i class="fas fa-user-plus mr-2"></i>Crear Usuario
            </h2>
        </div>

        <form method="POST" action="actions/crear_usuario.php" class="p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-semibold mb-2">
                        <i class="fas fa-user-tie mr-2"></i>Asignar a Delegado *
                    </label>
                    <select name="delegado_id" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="">Seleccione un delegado</option>
                        <?php 
                        global $conn;
                        $delegados_lista = $conn->query("SELECT id, nombre, apellidos FROM usuarios_sistema WHERE tipo_usuario = 'delegado' ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);
                        foreach ($delegados_lista as $del): 
                        ?>
                            <option value="<?php echo $del['id']; ?>">
                                <?php echo htmlspecialchars($del['nombre'] . ' ' . $del['apellidos']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Nombre *</label>
                    <input type="text" name="nombre" id="nombreUsuario" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Apellidos *</label>
                    <input type="text" name="apellidos" id="apellidosUsuario" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">País *</label>
                    <select name="pais" id="paisUsuario" required onchange="cargarCiudadesUsuario(this.value)"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
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
                    <select name="ciudad" id="ciudadUsuario" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="">Seleccione primero un país</option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Teléfono / WhatsApp *</label>
                    <div class="flex">
                        <span id="prefijoUsuario" class="inline-flex items-center px-3 border border-r-0 border-gray-300 bg-gray-50 rounded-l-lg text-gray-600"></span>
                        <input type="tel" name="telefono" id="telefonoUsuario" required pattern="[0-9]+"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Centro de Estudios *</label>
                    <input type="text" name="centro_estudios" id="centroUsuario" required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" 
                    class="flex-1 bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">
                    <i class="fas fa-save mr-2"></i>Crear Usuario
                </button>
                <button type="button" onclick="cerrarModal('modalCrearUsuario')" 
                    class="flex-1 bg-gray-500 text-white py-3 rounded-lg font-semibold hover:bg-gray-600 transition duration-300">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/main.js"></script>
<script>
function abrirModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    document.getElementById(modalId).classList.add('flex');
}

function cerrarModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.getElementById(modalId).classList.remove('flex');
}

// Funciones específicas para cada modal
function cargarCiudadesDelegado(pais) {
    cargarCiudadesGenerico(pais, 'ciudadDelegado', 'prefijoDelegado');
}

function cargarCiudadesUsuario(pais) {
    cargarCiudadesGenerico(pais, 'ciudadUsuario', 'prefijoUsuario');
}

function cargarCiudadesGenerico(pais, selectId, prefijoId) {
    const selectCiudad = document.getElementById(selectId);
    const prefijo = document.getElementById(prefijoId);
    
    selectCiudad.innerHTML = '<option value="">Seleccione una ciudad</option>';
    
    if (pais && ciudadesPorPais[pais]) {
        ciudadesPorPais[pais].forEach(ciudad => {
            const option = document.createElement('option');
            option.value = ciudad;
            option.textContent = ciudad;
            selectCiudad.appendChild(option);
        });
    }
    
    if (prefijo && prefijosPais[pais]) {
        prefijo.textContent = prefijosPais[pais];
    }
}

// Validaciones en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    // Para delegados
    const nombreDelegado = document.getElementById('nombreDelegado');
    const apellidosDelegado = document.getElementById('apellidosDelegado');
    const centroDelegado = document.getElementById('centroDelegado');
    const telefonoDelegado = document.getElementById('telefonoDelegado');

    if (nombreDelegado) nombreDelegado.addEventListener('input', function() { this.value = capitalizarNombre(this.value); });
    if (apellidosDelegado) apellidosDelegado.addEventListener('input', function() { this.value = capitalizarNombre(this.value); });
    if (centroDelegado) centroDelegado.addEventListener('input', function() { this.value = capitalizarNombre(this.value); });
    if (telefonoDelegado) telefonoDelegado.addEventListener('input', function() { this.value = this.value.replace(/\D/g, ''); });

    // Para usuarios
    const nombreUsuario = document.getElementById('nombreUsuario');
    const apellidosUsuario = document.getElementById('apellidosUsuario');
    const centroUsuario = document.getElementById('centroUsuario');
    const telefonoUsuario = document.getElementById('telefonoUsuario');

    if (nombreUsuario) nombreUsuario.addEventListener('input', function() { this.value = capitalizarNombre(this.value); });
    if (apellidosUsuario) apellidosUsuario.addEventListener('input', function() { this.value = capitalizarNombre(this.value); });
    if (centroUsuario) centroUsuario.addEventListener('input', function() { this.value = capitalizarNombre(this.value); });
    if (telefonoUsuario) telefonoUsuario.addEventListener('input', function() { this.value = this.value.replace(/\D/g, ''); });
});
</script>