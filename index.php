<?php
session_start();

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Mostrar/ocultar botón "Registro de Delegados" según configuración (por defecto ON)
$showRegistroDelegados = true;
try {
    require_once __DIR__ . '/config/database.php';
    $conn_cfg = getConnection();
    if ($conn_cfg) {
        $res_cfg = $conn_cfg->query("SELECT `v` FROM `settings` WHERE `k` = 'registro_delegados' LIMIT 1");
        if ($res_cfg && $row_cfg = $res_cfg->fetch_assoc()) {
            $showRegistroDelegados = ($row_cfg['v'] === '1');
        } else {
            $showRegistroDelegados = true;
        }
    }
} catch (Throwable $e) {
    // en caso de error, mantenemos el valor por defecto (habilitado)
    $showRegistroDelegados = true;
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
    <script src="assets/js/notifications.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'navy': '#001f3f',
                        'lightblue': '#7FDBFF',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background: linear-gradient(135deg, #001f3f 0%, #0074D9 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md p-8">
        <div class="text-center mb-8">
            <div class="bg-gradient-to-b from-navy to-gray-900 p-3 rounded-lg inline-block mb-4">
                <img src="assets/images/logo.webp" alt="Logo" class="h-16 w-auto max-w-full object-contain" onerror="this.style.display='none'">
            </div>
            <h1 class="text-2xl font-bold text-gray-800" style="line-height: 1.6rem; font-size: 22px;">Escuela Internacional de Psicología</h1>
            <p class="text-gray-600 mt-2">Sistema de Delegados</p>
        </div>

        <form id="loginForm" method="POST" action="auth/login.php" class="space-y-4">
            <div>
                <label class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-user mr-2"></i>Usuario
                </label>
                <input type="text" name="usuario" required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-2">
                    <i class="fas fa-lock mr-2"></i>Contraseña
                </label>
                <input type="password" name="contrasena" required 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
            </div>

            <button type="submit" 
                class="w-full bg-navy text-white py-3 rounded-lg font-semibold hover:opacity-90 transition duration-300">
                <i class="fas fa-sign-in-alt mr-2"></i>Ingresar
            </button>
        </form>

        <div class="mt-6 text-center">
            <?php if (!isset($showRegistroDelegados) || $showRegistroDelegados): ?>
            <button onclick="openRegistroModal()" 
                class="inline-block bg-navy text-white px-4 py-2 rounded-lg font-semibold hover:opacity-90 transition duration-300">
                <i class="fas fa-user-plus mr-2"></i>Registro de Delegados
            </button>
            <?php else: ?>
            <div class="inline-block bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded" style="font-size: 0.8rem;">
                Registro de Delegados deshabilitado<br/>Consulta con tu Asesor/a
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de Registro -->
    <div id="registroModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="bg-navy text-white p-6 rounded-t-lg">
                <h2 class="text-2xl font-bold">
                    <i class="fas fa-user-plus mr-2"></i>Registro de Delegado
                </h2>
            </div>

            <form id="registroForm" class="p-6 space-y-4" novalidate>
                <div id="errorRegistro" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded text-sm" role="alert" aria-live="assertive"></div>
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
                        <input type="text" name="usuario" id="usuarioReg" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Contraseña *</label>
                        <input type="password" name="contrasena" id="contrasenaReg" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
                    </div>

                    <!-- Campo para la clave de registro (controlado) -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Clave de registro *</label>
                        <input type="password" name="clave" id="claveReg" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy" 
                            placeholder="Introduce la clave que te proporcionaron">
                    </div>

                    <!-- Honeypot: campo oculto para atrapar bots -->
                    <div style="display:none;">
                        <label>Website</label>
                        <input type="text" name="website" id="website" autocomplete="off" value="">
                    </div>

                </div>

                <div class="flex gap-4 pt-4">
                    <button type="submit" id="registroSubmit"
                        class="flex-1 bg-navy text-white py-3 rounded-lg font-semibold hover:opacity-90 transition duration-300">
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
            document.getElementById('registroForm').reset();
            const er = document.getElementById('errorRegistro'); if (er) { er.classList.add('hidden'); er.textContent = ''; }
        }

        // --- Capitalización idéntica a "Crear Delegado" ---
        (function(){
          function fallbackCapitalizeName(s) {
            return s.replace(/\b\w+/g, function(w){
              return w.charAt(0).toUpperCase() + w.slice(1).toLowerCase();
            });
          }
          function bindCapitalization(id) {
            const el = document.getElementById(id);
            if (!el) return;
            if (el.__capBound) return;
            el.__capBound = true;
            el.addEventListener('input', function(){
              try {
                const start = this.selectionStart, end = this.selectionEnd;
                if (typeof capitalizarNombre === 'function') this.value = capitalizarNombre(this.value);
                else this.value = fallbackCapitalizeName(this.value);
                this.setSelectionRange(Math.min(start, this.value.length), Math.min(end, this.value.length));
              } catch(e) {
                if (typeof capitalizarNombre === 'function') this.value = capitalizarNombre(this.value);
                else this.value = fallbackCapitalizeName(this.value);
              }
            }, { passive:true });
          }
          bindCapitalization('nombre');
          bindCapitalization('apellidos');
          bindCapitalization('centro_estudios');
        })();
        // --- fin capitalización ---

        // Fallback cargarCiudades si main.js no provee
        if (typeof cargarCiudades === 'undefined') {
          function cargarCiudades(pais) {
            if (typeof cargarCiudadesGenerico === 'function') return cargarCiudadesGenerico(pais, 'ciudad', 'prefijo');
            const select = document.getElementById('ciudad');
            const pref = document.getElementById('prefijo');
            if (!select) return;
            select.innerHTML = '<option value="">Seleccione una ciudad</option>';
            if (typeof ciudadesPorPais !== 'undefined' && ciudadesPorPais[pais]) {
              ciudadesPorPais[pais].forEach(function(c) {
                const opt = document.createElement('option'); opt.value = c; opt.textContent = c; select.appendChild(opt);
              });
            }
            if (pref && typeof prefijosPais !== 'undefined' && prefijosPais[pais]) pref.textContent = prefijosPais[pais];
          }
        }

        // Validación y envío del formulario del modal Registro (igual que Crear Delegado),
        // con campo adicional 'clave' (clave de registro) validado aquí.
        document.getElementById('registroForm').addEventListener('submit', async function(e){
            e.preventDefault();
            const errorEl = document.getElementById('errorRegistro');
            if (errorEl) errorEl.classList.add('hidden');

            const nombre = (document.getElementById('nombre')||{}).value || '';
            const apellidos = (document.getElementById('apellidos')||{}).value || '';
            const pais = (document.getElementById('pais')||{}).value || '';
            const ciudad = (document.getElementById('ciudad')||{}).value || '';
            const telefono = (document.getElementById('telefono')||{}).value || '';
            const prefijo = (document.getElementById('prefijo')||{}).textContent || '';
            const centro = (document.getElementById('centro_estudios')||{}).value || '';
            const usuario = (document.getElementById('usuarioReg')||{}).value || '';
            const contrasena = (document.getElementById('contrasenaReg')||{}).value || '';
            const clave = (document.getElementById('claveReg')||{}).value || '';
            const honeypot = (document.getElementById('website')||{}).value || '';

            function showError(msg) {
                if (!errorEl) { alert(msg); return; }
                errorEl.textContent = msg;
                errorEl.classList.remove('hidden');
            }

            // Honeypot: si viene lleno, abortar silenciosamente
            if (honeypot && honeypot.trim() !== '') {
              return;
            }

            // Validaciones idénticas a Crear Delegado
            if (!nombre.trim()) return showError('Ingrese el nombre del delegado.');
            if (!apellidos.trim()) return showError('Ingrese los apellidos del delegado.');
            if (!pais.trim()) return showError('Seleccione el país.');
            if (!ciudad.trim()) return showError('Seleccione la ciudad.');
            const telefonoDigits = (telefono || '').replace(/\D/g,'');
            if (!telefonoDigits || telefonoDigits.length < 6) return showError('Ingrese un teléfono válido (mínimo 6 dígitos).');
            if (!centro.trim()) return showError('Ingrese el centro de estudios.');
            if (!usuario.trim() || usuario.trim().length < 4) return showError('El usuario debe tener al menos 4 caracteres.');
            if (!contrasena || contrasena.length < 6) return showError('La contraseña debe tener al menos 6 caracteres.');
            if (!clave.trim() || clave.trim().length < 4) return showError('La clave de registro es obligatoria (mínimo 4 caracteres).');

            // Preparar verificación de unicidad (teléfono + usuario) igual que Crear Delegado
            const telefonoParaCheck = (prefijo ? prefijo.trim() : '') + telefonoDigits;
            const payload = {};
            if (telefonoParaCheck) payload.telefono = telefonoParaCheck;
            if (usuario) payload.usuario = usuario;

            let valida = { success: false };
            try {
                const resp = await fetch('actions/validar_unicos.php', {
                    method: 'POST',
                    body: new URLSearchParams(payload),
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                valida = await resp.json();
            } catch (err) {
                console.error('validarUnicos error (index):', err);
                valida = { success: false, error: true, message: 'Error en la validación.' };
            }

            if (valida && !valida.error && valida.exists) {
                return showError(valida.message || 'Dato duplicado detectado.');
            }

            const submitBtn = document.getElementById('registroSubmit');
            if (submitBtn) submitBtn.disabled = true;

            // Enviar al mismo endpoint que usaría Crear Delegado
            const formData = new FormData(this);
            formData.append('clave_registro', clave.trim()); // enviar clave con nombre distinto por compatibilidad
            formData.append('telefono_prefijo', prefijo || '');

            try {
                const resp = await fetch('auth/register_public.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await resp.json();
                if (submitBtn) submitBtn.disabled = false;
                if (data && data.success) {
                    closeRegistroModal();
                    // Mostrar mensaje de aceptación idéntico al de Crear Delegado
                    if (typeof mostrarExito === 'function') {
                        mostrarExito(
                          '¡Delegado creado exitosamente!<br><br>' +
                          '<strong>Usuario:</strong> ' + (data.usuario || '') + '<br>' +
                          '<strong>Contraseña:</strong> ' + (data.contrasena || '') + '<br><br>' +
                          'El delegado ya puede iniciar sesión.'
                        );
                    } else {
                        // Fallback: alert con texto plano
                        alert('Delegado creado exitosamente. Usuario: ' + (data.usuario || '') + ' Contraseña: ' + (data.contrasena || ''));
                    }
                    this.reset();
                    return;
                }
                showError((data && data.message) ? data.message : 'Error en la solicitud. Intente nuevamente.');
            } catch (err) {
                console.error('Fetch error register_public (index):', err);
                if (submitBtn) submitBtn.disabled = false;
                showError('Error en la solicitud. Intente nuevamente.');
            }
        });

        // Mostrar error si viene de login fallido
        <?php if (isset($_GET['error']) && $_GET['error'] == 'credenciales'): ?>
        window.addEventListener('DOMContentLoaded', function() {
            mostrarError('Usuario o contraseña incorrectos. Por favor, intente nuevamente.');
        });
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] == 'inactivo'): ?>
        window.addEventListener('DOMContentLoaded', function() {
            mostrarError('Su cuenta ha sido desactivada. Contacte al administrador para más información.');
        });
        <?php endif; ?>
    </script>

</body>
</html>