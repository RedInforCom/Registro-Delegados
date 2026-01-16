<aside class="bg-gradient-to-b from-navy to-gray-900 w-64 min-h-screen fixed left-0 top-0 z-20 shadow-2xl">
    <div class="p-6">
        <div class="bg-white rounded-lg p-3 mb-4">
            <img src="assets/images/logo.webp" alt="Logo" class="w-full h-auto max-h-16 object-contain" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%22 0 0 100 100 %22%3E%3C/svg%3E'">
        </div>
    </div>
    
    <nav class="px-4">
        <a href="dashboard.php" class="flex items-center px-4 py-3 text-white hover:bg-blue-600 rounded-lg mb-2 transition duration-300">
            <i class="fas fa-home mr-3"></i>
            Dashboard
        </a>
        
        <?php if ($_SESSION['usuario_tipo'] == 'administrador'): ?>
        
        <button onclick="abrirModal('modalCrearAsesor')" class="w-full flex items-center px-4 py-3 text-white hover:bg-blue-600 rounded-lg mb-2 transition duration-300">
            <i class="fas fa-user-tie mr-3"></i>
            Crear Asesor
        </button>
        
        <button onclick="abrirModal('modalCrearDelegado')" class="w-full flex items-center px-4 py-3 text-white hover:bg-blue-600 rounded-lg mb-2 transition duration-300">
            <i class="fas fa-users mr-3"></i>
            Crear Delegado
        </button>
        
        <button onclick="abrirModal('modalCrearUsuario')" class="w-full flex items-center px-4 py-3 text-white hover:bg-blue-600 rounded-lg mb-2 transition duration-300">
            <i class="fas fa-user-plus mr-3"></i>
            Crear Usuario
        </button>

        <a href="estadisticas.php" class="flex items-center px-4 py-3 text-white hover:bg-blue-600 rounded-lg mb-2 transition duration-300">
            <i class="fas fa-chart-line mr-3"></i>
            Estadísticas
        </a>
        
        <?php elseif ($_SESSION['usuario_tipo'] == 'asesor'): ?>
        
        <button onclick="abrirModal('modalCrearDelegado')" class="w-full flex items-center px-4 py-3 text-white hover:bg-blue-600 rounded-lg mb-2 transition duration-300">
            <i class="fas fa-users mr-3"></i>
            Crear Delegado
        </button>
        
        <button onclick="abrirModal('modalCrearUsuario')" class="w-full flex items-center px-4 py-3 text-white hover:bg-blue-600 rounded-lg mb-2 transition duration-300">
            <i class="fas fa-user-plus mr-3"></i>
            Crear Usuario
        </button>
        
        <?php elseif ($_SESSION['usuario_tipo'] == 'delegado'): ?>
        
        <button onclick="abrirModal('modalCrearUsuario')" class="w-full flex items-center px-4 py-3 text-white hover:bg-blue-600 rounded-lg mb-2 transition duration-300">
            <i class="fas fa-user-plus mr-3"></i>
            Crear Usuario
        </button>
        
        <?php endif; ?>
    </nav>
</aside>

<!-- Modales -->

<!-- Modal Crear Asesor (sin cambios) -->
<?php if ($_SESSION['usuario_tipo'] == 'administrador'): ?>
<div id="modalCrearAsesor" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="bg-green-600 text-white p-6 rounded-t-lg">
            <h2 class="text-2xl font-bold">
                <i class="fas fa-user-tie mr-2"></i>Crear Asesor
            </h2>
        </div>

        <form id="formCrearAsesor" class="p-6 space-y-4">
            <div id="errorAsesor" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded text-sm"></div>
            <!-- ... campos Asesor ... -->
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Modal Crear Delegado -->
<?php if ($_SESSION['usuario_tipo'] == 'administrador' || $_SESSION['usuario_tipo'] == 'asesor'): ?>
<div id="modalCrearDelegado" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="bg-navy text-white p-6 rounded-t-lg">
            <h2 class="text-2xl font-bold">
                <i class="fas fa-users mr-2"></i>Crear Delegado
            </h2>
        </div>

        <!-- Formulario: mismo que index.php salvo que NO incluye 'clave' -->
        <form id="formCrearDelegado" class="p-6 space-y-4" novalidate>
            <div id="errorDelegado" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded text-sm"></div>
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
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy">
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
                    <input type="text" name="usuario" id="usuarioDelegado" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Contraseña *</label>
                    <input type="password" name="contrasena" id="contrasenaDelegado" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                </div>

                <!-- Honeypot (oculto) -->
                <div style="display:none;">
                    <label>Website</label>
                    <input type="text" name="website" id="websiteDelegado" autocomplete="off" value="">
                </div>

            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit"
                    class="flex-1 bg-navy text-white py-3 rounded-lg font-semibold hover:opacity-90 transition duration-300">
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

<script src="assets/js/main.js"></script>

<script>
function confirmarLimpiarBD() {
    mostrarConfirmacion('⚠️ ADVERTENCIA: Esta acción eliminará TODOS los registros de usuarios, delegados y asesores (excepto el administrador).<br><br>¿Está seguro de que desea continuar?', function(confirmado) {
        if (confirmado) {
            window.location.href = 'actions/limpiar_bd.php';
        }
    });
}
</script>
<script>
function abrirModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    document.getElementById(modalId).classList.add('flex');
}

function cerrarModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.getElementById(modalId).classList.remove('flex');
}
</script>

<script>
// Funciones para cargar ciudades y validaciones client-side (fallback si main.js no carga)
function cargarCiudadesDelegado(pais) {
    cargarCiudadesGenerico(pais, 'ciudadDelegado', 'prefijoDelegado');
}

function cargarCiudadesUsuario(pais) {
    cargarCiudadesGenerico(pais, 'ciudadUsuario', 'prefijoUsuario');
}

function cargarCiudadesGenerico(pais, selectId, prefijoId) {
    if (typeof ciudadesPorPais === 'undefined') return;
    const selectCiudad = document.getElementById(selectId);
    const prefijo = document.getElementById(prefijoId);
    
    if (!selectCiudad) return;
    selectCiudad.innerHTML = '<option value="">Seleccione una ciudad</option>';
    
    if (pais && ciudadesPorPais[pais]) {
        ciudadesPorPais[pais].forEach(ciudad => {
            const option = document.createElement('option');
            option.value = ciudad;
            option.textContent = ciudad;
            selectCiudad.appendChild(option);
        });
    }
    
    if (prefijo && typeof prefijosPais !== 'undefined' && prefijosPais[pais]) {
        prefijo.textContent = prefijosPais[pais];
    }
}

// Listener: mismas validaciones y manejo de mensajes que index (envío a auth/register_public.php)
// IMPORTANT: include credentials so session cookie is sent and server recognizes internal user
document.addEventListener('DOMContentLoaded', function() {
    // UX helpers (capitalizar/limpiar)
    if (typeof capitalizarNombre === 'function') {
        const n = document.getElementById('nombreDelegado'); if (n) n.addEventListener('input', ()=> n.value = capitalizarNombre(n.value));
        const a = document.getElementById('apellidosDelegado'); if (a) a.addEventListener('input', ()=> a.value = capitalizarNombre(a.value));
        const c = document.getElementById('centroDelegado'); if (c) c.addEventListener('input', ()=> c.value = capitalizarNombre(c.value));
    }
    const tel = document.getElementById('telefonoDelegado'); if (tel) tel.addEventListener('input', ()=> tel.value = tel.value.replace(/\D/g,''));

    const form = document.getElementById('formCrearDelegado');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const errorEl = document.getElementById('errorDelegado');
        if (errorEl) errorEl.classList.add('hidden');

        // Validaciones (idénticas a las del index)
        const nombre = (document.getElementById('nombreDelegado')||{}).value || '';
        const apellidos = (document.getElementById('apellidosDelegado')||{}).value || '';
        const pais = (document.getElementById('paisDelegado')||{}).value || '';
        const ciudad = (document.getElementById('ciudadDelegado')||{}).value || '';
        const telefono = (document.getElementById('telefonoDelegado')||{}).value || '';
        const centro = (document.getElementById('centroDelegado')||{}).value || '';
        const usuario = (document.getElementById('usuarioDelegado')||{}).value || '';
        const contrasena = (document.getElementById('contrasenaDelegado')||{}).value || '';

        if (!nombre.trim()) return showError('Ingrese el nombre del delegado.');
        if (!apellidos.trim()) return showError('Ingrese los apellidos del delegado.');
        if (!pais.trim()) return showError('Seleccione el país.');
        if (!ciudad.trim()) return showError('Seleccione la ciudad.');
        const telefonoDigits = telefono.replace(/\D/g, '');
        if (!telefonoDigits || telefonoDigits.length < 6) return showError('Ingrese un teléfono válido (mínimo 6 dígitos).');
        if (!centro.trim()) return showError('Ingrese el centro de estudios.');
        if (!usuario.trim() || usuario.trim().length < 4) return showError('El usuario debe tener al menos 4 caracteres.');
        if (!contrasena || contrasena.length < 6) return showError('La contraseña debe tener al menos 6 caracteres.');

        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        // Debug: show cookies (ver en consola si la cookie de sesión está presente)
        console.debug('document.cookie before fetch:', document.cookie);

        // Enviar con los mismos nombres de campo que espera register_public.php (sin 'clave')
        const formData = new FormData(form);

        fetch('/auth/register_public.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (submitBtn) submitBtn.disabled = false;

            if (data && data.success) {
                cerrarModal('modalCrearDelegado');
                mostrarExito(
                    '¡Delegado creado exitosamente!<br><br>' +
                    '<strong>Usuario:</strong> ' + (data.usuario || '') + '<br>' +
                    '<strong>Contraseña:</strong> ' + (data.contrasena || '') + '<br><br>' +
                    'El delegado ya puede iniciar sesión.'
                );
                form.reset();
            } else {
                // Mostrar mensaje exacto del servidor
                const msg = (data && data.message) ? data.message : 'Error en la solicitud. Intente nuevamente.';
                showError(msg);
            }
        })
        .catch(err => {
            console.error('Fetch error (sidebar create delegado):', err);
            if (submitBtn) submitBtn.disabled = false;
            showError('Error en la solicitud. Intente nuevamente.');
        });

        function showError(msg) {
            const el = document.getElementById('errorDelegado');
            if (!el) return;
            el.textContent = msg;
            el.classList.remove('hidden');
        }
    });
});
</script>