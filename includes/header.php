<header class="bg-white shadow-lg h-16 flex items-center justify-between px-6 fixed top-0 right-0 left-64 z-10">
    <h1 class="text-xl font-bold text-gray-800">
        <i class="fas fa-graduation-cap text-navy mr-2"></i>
        Escuela Internacional de Psicología - Delegados
    </h1>
    
    <div class="flex items-center gap-4">
        <span class="text-gray-600">
            <i class="fas fa-user-circle mr-2"></i>
            <?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
            <span class="text-xs bg-navy text-white px-2 py-1 rounded-full ml-2">
                <?php echo htmlspecialchars(ucfirst($_SESSION['usuario_tipo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
            </span>
        </span>
        
        <button id="btnEditarPerfil" onclick="editarPerfil()" class="text-blue-600 hover:text-blue-800" title="Editar perfil">
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
            <div id="errorPerfil" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded text-sm" role="alert" aria-live="assertive"></div>
            
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
async function editarPerfil() {
    const errorEl = document.getElementById('errorPerfil');
    if (errorEl) { errorEl.classList.add('hidden'); errorEl.textContent = ''; }

    // Mostrar modal inmediatamente (evita que nada impida abrirlo)
    const modal = document.getElementById('modalEditarPerfil');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    try {
        const resp = await fetch('actions/obtener_perfil.php', {
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        // Si el servidor redirige a login, salimos y redirigimos
        if (resp.redirected) {
            window.location.href = resp.url;
            return;
        }

        const data = await resp.json();

        if (!data || !data.success) {
            // Mostrar mensaje en el modal pero dejar el modal abierto para que el usuario vea el formulario vacío o edite manualmente
            if (errorEl) {
                errorEl.textContent = (data && data.message) ? data.message : 'No se pudo cargar el perfil.';
                errorEl.classList.remove('hidden');
            } else {
                alert((data && data.message) ? data.message : 'No se pudo cargar el perfil.');
            }
            return;
        }

        // Rellenar campos con los datos recibidos
        const d = data.datos || {};
        document.getElementById('nombrePerfil').value = d.nombre || '';
        document.getElementById('apellidosPerfil').value = d.apellidos || '';
        document.getElementById('telefonoPerfil').value = d.telefono || '';
        document.getElementById('usuarioPerfil').value = d.usuario || '';
        document.getElementById('emailPerfil').value = d.email || '';
        if (document.getElementById('paisPerfil') && d.pais) document.getElementById('paisPerfil').value = d.pais;
        if (document.getElementById('ciudadPerfil') && d.ciudad) document.getElementById('ciudadPerfil').value = d.ciudad;

        // Foco en el primer campo
        try { document.getElementById('nombrePerfil').focus(); } catch (e) {}
    } catch (err) {
        console.error('editarPerfil error:', err);
        if (errorEl) {
            errorEl.textContent = 'Error de red al cargar el perfil.';
            errorEl.classList.remove('hidden');
        } else {
            alert('Error de red al cargar el perfil.');
        }
    }
}

function cerrarModalPerfil() {
    const modal = document.getElementById('modalEditarPerfil');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    const f = document.getElementById('formEditarPerfil');
    if (f) f.reset();
    const er = document.getElementById('errorPerfil');
    if (er) { er.classList.add('hidden'); er.textContent = ''; }
}

document.getElementById('formEditarPerfil')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const errorEl = document.getElementById('errorPerfil');
    if (errorEl) errorEl.classList.add('hidden');

    fetch('actions/actualizar_perfil.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
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
            const el = document.getElementById('errorPerfil');
            if (el) {
                el.textContent = data.message || 'Error al actualizar perfil.';
                el.classList.remove('hidden');
            } else {
                alert(data.message || 'Error al actualizar perfil.');
            }
        }
    })
    .catch(err => {
        console.error('actualizar_perfil error:', err);
        const el = document.getElementById('errorPerfil');
        if (el) {
            el.textContent = 'Error de red al actualizar.';
            el.classList.remove('hidden');
        } else {
            alert('Error de red al actualizar.');
        }
    });
});
</script>