<header class="bg-white shadow-lg h-16 flex items-center justify-between px-6 fixed top-0 right-0 left-64 z-10">
    <h1 class="text-xl font-bold text-gray-800">
        <i class="fas fa-graduation-cap text-navy mr-2"></i>
        Escuela Internacional de Psicología - Delegados
    </h1>
    
    <div class="flex items-center gap-4">
        <span class="text-gray-600">
            <i class="fas fa-user-circle mr-2"></i>
            <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
            <span class="text-xs bg-navy text-white px-2 py-1 rounded-full ml-2">
                <?php echo ucfirst($_SESSION['usuario_tipo']); ?>
            </span>
        </span>
        
        <button onclick="editarPerfil()" class="text-blue-600 hover:text-blue-800" title="Editar perfil">
            <i class="fas fa-user-edit text-xl"></i>
        </button>
        
        <a href="auth/logout.php" class="text-red-600 hover:text-red-800" title="Cerrar sesión">
            <i class="fas fa-sign-out-alt text-xl"></i>
        </a>
    </div>
</header>

<!-- Modal Editar Perfil -->
<div id="modalEditarPerfil" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="bg-navy text-white p-6 rounded-t-lg">
            <h2 class="text-2xl font-bold">
                <i class="fas fa-user-edit mr-2"></i>Editar Mi Perfil
            </h2>
        </div>

        <form id="formEditarPerfil" class="p-6 space-y-4">
            <div id="errorPerfil" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded text-sm"></div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Nombre *</label>
                    <input type="text" name="nombre" id="nombrePerfil" required 
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-navy">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Apellidos *</label>
                    <input type="text" name="apellidos" id="apellidosPerfil" required 
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-navy">
                </div>

                <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">País *</label>
                    <select name="pais" id="paisPerfil" required 
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-navy">
                        <option value="">Seleccione un país</option>
                        <option value="Perú">Perú</option>
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Ciudad *</label>
                    <input type="text" name="ciudad" id="ciudadPerfil" required 
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-navy">
                </div>
                <?php endif; ?>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Teléfono *</label>
                    <input type="tel" name="telefono" id="telefonoPerfil" required 
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-navy">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Email</label>
                    <input type="email" name="email" id="emailPerfil" 
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-navy">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Usuario *</label>
                    <input type="text" name="usuario" id="usuarioPerfil" required 
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-navy">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Nueva Contraseña</label>
                    <input type="password" name="contrasena" id="contrasenaPerfil" 
                        placeholder="Dejar vacío para no cambiar"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-navy">
                </div>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" 
                    class="flex-1 bg-navy text-white py-3 rounded-lg font-semibold hover:opacity-90 transition">
                    <i class="fas fa-save mr-2"></i>Guardar Cambios
                </button>
                <button type="button" onclick="cerrarModalPerfil()" 
                    class="flex-1 bg-gray-500 text-white py-3 rounded-lg font-semibold hover:bg-gray-600 transition">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/notifications.js"></script>
<script>
function editarPerfil() {
    // Cargar datos actuales
    fetch('actions/obtener_perfil.php')
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('nombrePerfil').value = data.datos.nombre;
            document.getElementById('apellidosPerfil').value = data.datos.apellidos;
            document.getElementById('telefonoPerfil').value = data.datos.telefono;
            document.getElementById('usuarioPerfil').value = data.datos.usuario;
            if (data.datos.email) document.getElementById('emailPerfil').value = data.datos.email;
            if (data.datos.pais) document.getElementById('paisPerfil').value = data.datos.pais;
            if (data.datos.ciudad) document.getElementById('ciudadPerfil').value = data.datos.ciudad;
            
            document.getElementById('modalEditarPerfil').classList.remove('hidden');
            document.getElementById('modalEditarPerfil').classList.add('flex');
        }
    });
}

function cerrarModalPerfil() {
    document.getElementById('modalEditarPerfil').classList.add('hidden');
    document.getElementById('modalEditarPerfil').classList.remove('flex');
}

document.getElementById('formEditarPerfil').addEventListener('submit', function(e) {
    e.preventDefault();
    document.getElementById('errorPerfil').classList.add('hidden');
    
    fetch('actions/actualizar_perfil.php', {
        method: 'POST',
        body: new FormData(this)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            cerrarModalPerfil();
            mostrarExito('Perfil actualizado exitosamente', function() {
                location.reload();
            });
        } else {
            document.getElementById('errorPerfil').textContent = data.message;
            document.getElementById('errorPerfil').classList.remove('hidden');
        }
    });
});
</script>