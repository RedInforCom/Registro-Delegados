<?php
// includes/sidebar.php
// Versión adaptada: comprobación defensiva de sesión + uso de isset() en validaciones de tipo de usuario.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<aside class="bg-gradient-to-b from-navy to-gray-900 w-64 min-h-screen fixed left-0 top-0 z-20 shadow-2xl">
    <div class="p-6">
        <div class="rounded-lg p-0 mb-4">
            <img src="assets/images/logo.webp" alt="Logo" class="w-full h-auto max-h-16 object-contain" onerror="this.style.display='none'">
        </div>
    </div>
    
    <nav class="px-4">
      <a href="dashboard.php" class="flex items-center px-4 py-3 text-white hover:bg-blue-600 rounded-lg mb-2 transition duration-300">
        <i class="fas fa-home mr-3"></i> Dashboard
      </a>
    
      <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'administrador'): ?>
        <!-- Registros (submenú) -->
        <div class="mb-2">
          <button type="button"
                  class="w-full flex items-center justify-between px-4 py-3 text-white hover:bg-blue-600 rounded-lg transition duration-300"
                  aria-expanded="false"
                  data-toggle="submenu"
                  data-target="submenuRegistros">
            <span class="flex items-center">
              <i class="fas fa-clipboard-list mr-3"></i>
              Registros
            </span>
            <i class="fas fa-chevron-down transition-transform duration-200"></i>
          </button>
    
          <div id="submenuRegistros" class="overflow-hidden max-h-0 transition-[max-height] duration-300" aria-hidden="true">
            <div class="mt-2 ml-4 mr-2 pb-2 flex flex-col gap-2">
              <button type="button" onclick="abrirModal('modalCrearAsesor')"
                      class="w-full text-left px-4 py-3 text-white hover:bg-blue-600 rounded-lg transition duration-300">
                <i class="fas fa-user-tie mr-3"></i> Crear Asesor
              </button>
    
              <button type="button" onclick="abrirModal('modalCrearDelegado')"
                      class="w-full text-left px-4 py-3 text-white hover:bg-blue-600 rounded-lg transition duration-300">
                <i class="fas fa-users mr-3"></i> Crear Delegado
              </button>
    
              <button type="button" onclick="abrirModal('modalCrearUsuario')"
                      class="w-full text-left px-4 py-3 text-white hover:bg-blue-600 rounded-lg transition duration-300">
                <i class="fas fa-user-plus mr-3"></i> Crear Usuario
              </button>
            </div>
          </div>
        </div>
    
        <a href="#" onclick="abrirModal('modalChangeKey')" class="flex items-center px-4 py-3 text-white hover:bg-blue-600 rounded-lg mb-2 transition duration-300">
          <i class="fas fa-key mr-3"></i> Clave de Registro
        </a>
    
        <!-- Logs (submenú) -->
        <div class="mb-2">
          <button type="button"
                  class="w-full flex items-center justify-between px-4 py-3 text-white hover:bg-blue-600 rounded-lg transition duration-300"
                  aria-expanded="false"
                  data-toggle="submenu"
                  data-target="submenuLogs">
            <span class="flex items-center">
              <i class="fas fa-list-alt mr-3"></i>
              Logs
            </span>
            <i class="fas fa-chevron-down transition-transform duration-200"></i>
          </button>
    
          <div id="submenuLogs" class="overflow-hidden max-h-0 transition-[max-height] duration-300" aria-hidden="true">
            <div class="mt-2 ml-4 mr-2 pb-2 flex flex-col gap-2">
              <button type="button" onclick="openLogsModal()"
                      class="w-full text-left px-4 py-3 text-white hover:bg-blue-600 rounded-lg transition duration-300">
                <i class="fas fa-eye mr-3"></i> Ver Logs
              </button>
    
              <button type="button" onclick="openClearLogsModal()"
                      class="w-full text-left px-4 py-3 text-white hover:bg-red-600 rounded-lg transition duration-300">
                <i class="fas fa-trash-alt mr-3"></i> Vaciar Logs
              </button>
            </div>
          </div>
        </div>
    
      <?php elseif (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'asesor'): ?>
    
        <button type="button" onclick="abrirModal('modalCrearDelegado')" class="w-full flex items-center px-4 py-3 text-white hover:bg-blue-600 rounded-lg mb-2 transition duration-300">
          <i class="fas fa-users mr-3"></i> Crear Delegado
        </button>
    
        <button type="button" onclick="abrirModal('modalCrearUsuario')" class="w-full flex items-center px-4 py-3 text-white hover:bg-blue-600 rounded-lg mb-2 transition duration-300">
          <i class="fas fa-user-plus mr-3"></i> Crear Usuario
        </button>
    
      <?php else: ?>
    
        <button type="button" onclick="abrirModal('modalCrearUsuario')" class="w-full flex items-center px-4 py-3 text-white hover:bg-blue-600 rounded-lg mb-2 transition duration-300">
          <i class="fas fa-user-plus mr-3"></i> Crear Usuario
        </button>
    
      <?php endif; ?>
    </nav>

    <!-- BLOQUE ADMIN: Control del registro público (sólo visible para administradores).
         Esta es la añadidura que habilita/deshabilita el botón "Registro de Delegados"
         en la página pública (index). -->
    <?php
    $registro_delegados_habilitado = true;
    if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'administrador') {
        try {
            require_once __DIR__ . '/../config/database.php';
            $conn_tmp = getConnection();
            if ($conn_tmp) {
                $res = $conn_tmp->query("SELECT `v` FROM `settings` WHERE `k` = 'registro_delegados' LIMIT 1");
                if ($res && ($row = $res->fetch_assoc())) {
                    $registro_delegados_habilitado = ($row['v'] === '1');
                } else {
                    $registro_delegados_habilitado = true;
                }
            }
        } catch (Throwable $_e) {
            $registro_delegados_habilitado = true;
        }
    }
    ?>

    <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'administrador'): ?>
    <div class="px-4 py-4 border-t border-white/10">
      <div class="text-xs text-white/80 mb-2">Registro en el sitio público</div>
      <div class="flex items-center gap-2">
        <span class="text-sm text-white"><?php echo $registro_delegados_habilitado ? 'Habilitado' : 'Deshabilitado'; ?></span>
        <button id="btnToggleRegistro" class="ml-auto bg-white/10 hover:bg-white/20 text-white px-3 py-1 rounded text-sm" type="button" onclick="abrirModal('modalToggleRegistro')">
          Cambiar
        </button>
      </div>
    </div>
    <?php endif; ?>

    <!-- BOTÓN "Resetear la BD" añadido: solo visible para administradores -->
    <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'administrador'): ?>
    <div class="px-4 py-4 border-t border-white/10">
      <button type="button" onclick="abrirModal('modalResetBD')" class="w-full flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white py-2 px-3 rounded-lg font-semibold">
        <i class="fas fa-database"></i> Resetear la BD
      </button>
    </div>
    <?php endif; ?>
</aside>

<!-- --- Resto de modales y scripts del sidebar: MANTENIDOS exactamente como en tu versión original --- -->

<!-- Modal Crear Asesor (sin cambios) -->
<?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'administrador'): ?>
<div id="modalCrearAsesor" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
    <div class="bg-green-600 text-white p-6 rounded-t-lg">
      <h2 class="text-2xl font-bold"><i class="fas fa-user-tie mr-2"></i>Crear Asesor</h2>
    </div>

    <form id="formCrearAsesor" class="p-6 space-y-4">
      <div id="errorAsesor" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded text-sm"></div>
      <!-- ... campos Asesor ... -->
    </form>
  </div>
</div>
<?php endif; ?>

<!-- Modal Crear Delegado (NO TOCAR: mantengo tu HTML/IDs/JS tal como los tienes en tu versión original) -->
<?php if (isset($_SESSION['usuario_tipo']) && ( $_SESSION['usuario_tipo'] === 'administrador' || $_SESSION['usuario_tipo'] === 'asesor' )): ?>
<div id="modalCrearDelegado" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
    <div class="bg-navy text-white p-6 rounded-t-lg">
      <h2 class="text-2xl font-bold"><i class="fas fa-users mr-2"></i>Crear Delegado</h2>
    </div>

    <!-- novalidate para evitar tooltip nativo, el JS maneja mensajes en #errorRegistro -->
    <form id="registroForm" class="p-6 space-y-4" novalidate>
      <div id="errorRegistro" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded text-sm" role="alert" aria-live="assertive"></div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-gray-700 font-semibold mb-2">Nombre *</label>
          <input type="text" name="nombre" id="nombre" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-2">Apellidos *</label>
          <input type="text" name="apellidos" id="apellidos" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-2">País *</label>
          <select name="pais" id="pais" onchange="cargarCiudades(this.value)" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy">
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
          <select name="ciudad" id="ciudad" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
            <option value="">Seleccione primero un país</option>
          </select>
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-2">Teléfono / WhatsApp *</label>
          <div class="flex">
            <span id="prefijo" class="inline-flex items-center px-3 border border-r-0 border-gray-300 bg-gray-50 rounded-l-lg text-gray-600"></span>
            <input type="tel" name="telefono" id="telefono" pattern="[0-9]+" class="flex-1 px-4 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
          </div>
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-2">Centro de Estudios *</label>
          <input type="text" name="centro_estudios" id="centro_estudios" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-2">Usuario *</label>
          <input type="text" name="usuario" id="usuarioReg" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
        </div>

        <div>
          <label class="block text-gray-700 font-semibold mb-2">Contraseña *</label>
          <input type="password" name="contrasena" id="contrasenaReg" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
        </div>

        <!-- Honeypot (oculto) -->
        <div style="display:none;">
          <label>Website</label>
          <input type="text" name="website" id="website" autocomplete="off" value="">
        </div>
      </div>

      <div class="flex gap-4 pt-4">
        <button type="submit" class="flex-1 bg-navy text-white py-3 rounded-lg font-semibold hover:opacity-90 transition duration-300">
          <i class="fas fa-save mr-2"></i>Crear Delegado
        </button>
        <button type="button" onclick="closeRegistroModal()" class="flex-1 bg-gray-500 text-white py-3 rounded-lg font-semibold hover:bg-gray-600 transition duration-300">
          <i class="fas fa-times mr-2"></i>Cancelar
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script src="assets/js/main.js"></script>

<script>
/* Solo definiciones ligeras si no existen (no sobrescribo main.js) */
if (typeof window.abrirModal !== 'function') {
  window.abrirModal = function(modalId) {
    const el = document.getElementById(modalId);
    if (!el) return;
    el.classList.remove('hidden');
    el.classList.add('flex');
    try { document.documentElement.style.overflow = 'hidden'; document.body.style.overflow = 'hidden'; } catch(e){}
  };
}
if (typeof window.cerrarModal !== 'function') {
  window.cerrarModal = function(modalId) {
    const el = document.getElementById(modalId);
    if (!el) return;
    el.classList.add('hidden');
    el.classList.remove('flex');
    try { document.documentElement.style.overflow = ''; document.body.style.overflow = ''; } catch(e){}
  };
}
if (typeof window.closeRegistroModal !== 'function') {
  window.closeRegistroModal = function() {
    try { cerrarModal('modalCrearDelegado'); } catch(e){}
    const f = document.getElementById('registroForm');
    if (f) f.reset();
    const er = document.getElementById('errorRegistro');
    if (er) { er.classList.add('hidden'); er.textContent = ''; }
  };
}
</script>

<script>
/* Mantengo EXACTAMENTE el submit handler del registroForm (Crear Delegado) tal como lo tenías. */
document.getElementById('registroForm')?.addEventListener('submit', async function(e){
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

  function showError(msg) {
    const el = document.getElementById('errorRegistro');
    if (!el) { alert(msg); return; }
    el.textContent = msg;
    el.classList.remove('hidden');
  }

  if (!nombre.trim()) return showError('Ingrese el nombre del delegado.');
  if (!apellidos.trim()) return showError('Ingrese los apellidos del delegado.');
  if (!pais.trim()) return showError('Seleccione el país.');
  if (!ciudad.trim()) return showError('Seleccione la ciudad.');
  const telefonoDigits = (telefono || '').replace(/\D/g,'');
  if (!telefonoDigits || telefonoDigits.length < 6) return showError('Ingrese un teléfono válido (mínimo 6 dígitos).');
  if (!centro.trim()) return showError('Ingrese el centro de estudios.');
  if (!usuario.trim() || usuario.trim().length < 4) return showError('El usuario debe tener al menos 4 caracteres.');
  if (!contrasena || contrasena.length < 6) return showError('La contraseña debe tener al menos 6 caracteres.');

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
    console.error('validarUnicos error:', err);
    valida = { success: false, error: true, message: 'Error en la validación.' };
  }

  if (valida && !valida.error && valida.exists) {
    return showError(valida.message || 'Dato duplicado detectado.');
  }

  const submitBtn = this.querySelector('button[type="submit"]');
  if (submitBtn) submitBtn.disabled = true;

  const formData = new FormData(this);
  fetch('auth/register_public.php', {
    method: 'POST',
    body: formData,
    credentials: 'same-origin',
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(r => r.json())
  .then(data => {
    if (submitBtn) submitBtn.disabled = false;
    if (data && data.success) {
      closeRegistroModal();
      if (typeof mostrarExito === 'function') {
        mostrarExito(
          '¡Delegado creado exitosamente!<br><br>' +
          '<strong>Usuario:</strong> ' + (data.usuario || '') + '<br>' +
          '<strong>Contraseña:</strong> ' + (data.contrasena || '') + '<br><br>' +
          'El delegado ya puede iniciar sesión.'
        );
      } else {
        alert('Delegado creado exitosamente');
      }
      this.reset();
      return;
    }
    showError((data && data.message) ? data.message : 'Error en la solicitud. Intente nuevamente.');
  })
  .catch(err => {
    console.error('Fetch error register_public (sidebar):', err);
    if (submitBtn) submitBtn.disabled = false;
    showError('Error en la solicitud. Intente nuevamente.');
  });
});
</script>

<!-- Handler Crear Usuario, listeners, capitalización, etc. (sin cambios) -->
<?php
// Cargar delegados para el select (sin tocar modal Delegado)
$delegados_lista = [];
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'delegado') {
    require_once __DIR__ . '/../config/database.php';
    $conn_sidebar = getConnection();
    $res_del = $conn_sidebar->query("SELECT id, nombre, apellidos FROM usuarios_sistema WHERE tipo_usuario = 'delegado' ORDER BY nombre");
    if ($res_del) $delegados_lista = $res_del->fetch_all(MYSQLI_ASSOC);
}
?>
<div id="modalCrearUsuario" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
    <div class="bg-navy text-white p-6 rounded-t-lg">
      <h2 class="text-2xl font-bold"><i class="fas fa-user-friends mr-2"></i>Crear Usuario</h2>
    </div>

    <form id="formCrearUsuario" class="p-6 space-y-4" novalidate>
      <div id="errorUsuario" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded text-sm" role="alert" aria-live="assertive"></div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="nombreUsuario" class="block text-gray-700 font-semibold mb-2">Nombre *</label>
          <input type="text" name="nombre" id="nombreUsuario" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600" autocomplete="given-name">
        </div>

        <div>
          <label for="apellidosUsuario" class="block text-gray-700 font-semibold mb-2">Apellidos *</label>
          <input type="text" name="apellidos" id="apellidosUsuario" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600" autocomplete="family-name">
        </div>

        <div>
          <label for="paisUsuario" class="block text-gray-700 font-semibold mb-2">País *</label>
          <select name="pais" id="paisUsuario" required onchange="cargarCiudadesUsuario(this.value)" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy">
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
          <label for="ciudadUsuario" class="block text-gray-700 font-semibold mb-2">Ciudad *</label>
          <select name="ciudad" id="ciudadUsuario" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
            <option value="">Seleccione primero un país</option>
          </select>
        </div>

        <div>
          <label for="telefonoUsuario" class="block text-gray-700 font-semibold mb-2">Teléfono / WhatsApp *</label>
          <div class="flex">
            <span id="prefijoUsuario" class="inline-flex items-center px-3 border border-r-0 border-gray-300 bg-gray-50 rounded-l-lg text-gray-600"></span>
            <input type="tel" name="telefono" id="telefonoUsuario" required pattern="[0-9]+" class="flex-1 px-4 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-purple-600" autocomplete="tel">
          </div>
        </div>

        <div>
          <label for="centroUsuario" class="block text-gray-700 font-semibold mb-2">Centro de Estudios *</label>
          <input type="text" name="centro_estudios" id="centroUsuario" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600" autocomplete="organization">
        </div>

        <?php if (!empty($delegados_lista)): ?>
          <div>
            <label for="delegadoSelect" class="block text-gray-700 font-semibold mb-2">Delegado *</label>
            <select name="delegado_id" id="delegadoSelect" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-navy">
              <option value="">Seleccione un delegado</option>
              <?php foreach ($delegados_lista as $d): ?>
                <option value="<?php echo intval($d['id']); ?>"><?php echo htmlspecialchars($d['nombre'] . ' ' . $d['apellidos']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endif; ?>

      </div>

      <div class="flex gap-4 pt-4">
        <button type="submit" class="flex-1 bg-navy text-white py-3 rounded-lg font-semibold hover:opacity-90 transition duration-300">
          <i class="fas fa-save mr-2"></i>Crear Usuario
        </button>
        <button type="button" onclick="cerrarModal('modalCrearUsuario')" class="flex-1 bg-gray-500 text-white py-3 rounded-lg font-semibold hover:bg-gray-600 transition duration-300">
          <i class="fas fa-times mr-2"></i>Cancelar
        </button>
      </div>
    </form>
  </div>
</div>

<script>
(function(){
  // Definir capitalizarNombre local si no existe (robusta: quita etiquetas, colapsa espacios) para el modal CREAR USUARIO
  if (typeof capitalizarNombre !== 'function') {
    window.capitalizarNombre = function(texto) {
      if (!texto) return '';
      // eliminar etiquetas HTML, colapsar espacios y normalizar
      texto = String(texto).replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim().toLowerCase();
      const palabrasMinus = ['de','del','la','las','los','el','y','e','o','u'];
      return texto.split(' ').map((w, i) => {
        if (!w) return '';
        if (i > 0 && palabrasMinus.includes(w)) return w;
        return w.charAt(0).toUpperCase() + w.slice(1);
      }).join(' ');
    };
  }

  // Conjuntos de IDs a normalizar
  const camposCap = new Set([
    'nombre', 'apellidos', 'centro_estudios',
    'nombreUsuario', 'apellidosUsuario', 'centroUsuario'
  ]);
  const camposNum = new Set(['telefono', 'telefonoUsuario']);

  // Handler de input (delegación, fase de captura para adelantarnos a otros handlers)
  document.addEventListener('input', function(e) {
    try {
      const el = e.target;
      if (!el || !el.id) return;
      const id = el.id;
      if (camposCap.has(id)) {
        // normalizar capitalización en tiempo real
        el.value = capitalizarNombre(el.value || '');
      } else if (camposNum.has(id)) {
        // dejar solo dígitos
        el.value = (el.value || '').replace(/\D/g, '');
      }
    } catch (err) {
      // no romper otros scripts
      console.error('sidebar normalize input error', err);
    }
  }, true); // use capture phase

  // Normalización previa al submit: captura el submit y limpia campos relevantes
  document.addEventListener('submit', function(e) {
    try {
      const form = e.target;
      if (!form || !form.id) return;

      // Mapas por formulario
      const formularioMappings = {
        'formCrearUsuario': [
          { id: 'nombreUsuario', type: 'cap' },
          { id: 'apellidosUsuario', type: 'cap' },
          { id: 'centroUsuario', type: 'cap' },
          { id: 'telefonoUsuario', type: 'num' }
        ],
        'registroForm': [
          { id: 'nombre', type: 'cap' },
          { id: 'apellidos', type: 'cap' },
          { id: 'centro_estudios', type: 'cap' },
          { id: 'telefono', type: 'num' }
        ]
      };

      const mapping = formularioMappings[form.id];
      if (!mapping) return;

      // Ejecutar normalización sincronamente antes de que otros submit handlers (bubbling) lean valores
      mapping.forEach(m => {
        const el = document.getElementById(m.id);
        if (!el) return;
        if (m.type === 'cap') el.value = capitalizarNombre(el.value || '');
        if (m.type === 'num') el.value = (el.value || '').replace(/\D/g, '');
      });
    } catch (err) {
      console.error('sidebar normalize submit error', err);
    }
  }, true); // captura para ejecutarse antes de listeners en fase de burbuja

  // Util: Limpieza idempotente al cerrar modal (evita que queden valores ocultos)
  // (Aprovecha el observer que ya tienes para limpiar; aquí añadimos una llamada rápida)
  window.normalizeModalNow = function(modalId) {
    try {
      const m = document.getElementById(modalId);
      if (!m) return;
      ['nombreUsuario','apellidosUsuario','centroUsuario','telefonoUsuario','nombre','apellidos','centro_estudios','telefono'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        if (camposCap.has(id)) el.value = capitalizarNombre(el.value || '');
        if (camposNum.has(id)) el.value = (el.value || '').replace(/\D/g, '');
      });
    } catch(e){ console.error('normalizeModalNow error', e); }
  };

})();
</script>

<script>
/* Fallback cargarCiudadesUsuario si no existe global */
if (typeof cargarCiudadesUsuario === 'undefined') {
  function cargarCiudadesUsuario(pais) {
    if (typeof cargarCiudadesGenerico === 'function') return cargarCiudadesGenerico(pais, 'ciudadUsuario', 'prefijoUsuario');
    const select = document.getElementById('ciudadUsuario');
    const pref = document.getElementById('prefijoUsuario');
    if (!select) return;
    select.innerHTML = '<option value="">Seleccione una ciudad</option>';
    if (typeof ciudadesPorPais !== 'undefined' && ciudadesPorPais[pais]) {
      ciudadesPorPais[pais].forEach(function(c){ const opt = document.createElement('option'); opt.value = c; opt.textContent = c; select.appendChild(opt); });
    }
    if (pref && typeof prefijosPais !== 'undefined' && prefijosPais[pais]) pref.textContent = prefijosPais[pais];
  }
}
</script>

<script>
/* Handler independiente para formCrearUsuario (no modifica Delegado) */
(function(){
  const formUsuario = document.getElementById('formCrearUsuario');
  if (!formUsuario) return;
  if (formUsuario.__bound) return;
  formUsuario.__bound = true;

  formUsuario.addEventListener('submit', async function(e){
    e.preventDefault();
    const errorEl = document.getElementById('errorUsuario'); if (errorEl) errorEl.classList.add('hidden');

    const nombre = (document.getElementById('nombreUsuario')||{}).value || '';
    const apellidos = (document.getElementById('apellidosUsuario')||{}).value || '';
    const pais = (document.getElementById('paisUsuario')||{}).value || '';
    const ciudad = (document.getElementById('ciudadUsuario')||{}).value || '';
    const telefono = (document.getElementById('telefonoUsuario')||{}).value || '';
    const prefijoU = (document.getElementById('prefijoUsuario')||{}).textContent || '';
    const centro = (document.getElementById('centroUsuario')||{}).value || '';

    function showError(msg){
      const el = document.getElementById('errorUsuario');
      if (!el) { alert(msg); return; }
      el.textContent = msg; el.classList.remove('hidden');
    }

    if (!nombre.trim()) return showError('Ingrese el nombre del usuario.');
    if (!apellidos.trim()) return showError('Ingrese los apellidos del usuario.');
    if (!pais.trim()) return showError('Seleccione el país.');
    if (!ciudad.trim()) return showError('Seleccione la ciudad.');
    const telefonoDigits = (telefono || '').replace(/\D/g,'');
    if (!telefonoDigits || telefonoDigits.length < 6) return showError('Ingrese un teléfono válido (mínimo 6 dígitos).');
    if (!centro.trim()) return showError('Ingrese el centro de estudios.');

    <?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] != 'delegado'): ?>
      const delegadoVal = (document.getElementById('delegadoSelect')||{}).value || '';
      if (!delegadoVal) return showError('Debe asignar un delegado');
    <?php endif; ?>

    const telefonoParaCheck = (prefijoU ? prefijoU.trim() : '') + telefonoDigits;
    let valida = { success:false };
    try {
      const resp = await fetch('actions/validar_unicos.php', {
        method:'POST',
        body: new URLSearchParams({ telefono: telefonoParaCheck }),
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      valida = await resp.json();
    } catch(err){
      console.error('validarUnicos error (modalCrearUsuario):', err);
      valida = { success:false, error:true, message:'Error en la validación.' };
    }

    if (valida && !valida.error && valida.exists) {
      return showError('El teléfono ' + telefonoParaCheck + ' ya está registrado');
    }

    const submitBtn = this.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;

    const formData = new FormData(this);
    try {
      const resp = await fetch('actions/crear_usuario.php', {
        method:'POST',
        body: formData,
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const data = await resp.json();
      if (submitBtn) submitBtn.disabled = false;
      if (data && data.success) {
        cerrarModal('modalCrearUsuario');
        if (typeof mostrarExito === 'function') mostrarExito(data.message || '¡Usuario creado exitosamente!');
        else alert(data.message || '¡Usuario creado exitosamente!');
        this.reset();
        return;
      }
      showError((data && data.message) ? data.message : 'Error al crear usuario');
    } catch(err){
      console.error('Fetch error crear_usuario (modalCrearUsuario):', err);
      if (submitBtn) submitBtn.disabled = false;
      showError('Error en la solicitud. Intente nuevamente.');
    }
  });
})();
</script>

<!-- Modal y JS para "Activar/Desactivar Registro de Delegados" (sólo visible para admin) -->
<?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'administrador'): ?>
<div id="modalToggleRegistro" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-lg shadow-2xl w-full max-w-md">
    <div class="p-6">
      <h3 class="text-lg font-bold mb-2">Activar / Desactivar Registro de Delegados</h3>
      <p class="text-sm text-gray-700 mb-4">
        Al desactivar, el botón "Registro de Delegados" en la página pública (index) dejará de mostrarse.
        Solo los administradores pueden cambiar esta opción.
      </p>

      <div class="mb-4">
        <label class="inline-flex items-center">
          <input id="toggleRegistroCheckbox" type="checkbox" class="form-checkbox h-5 w-5 text-navy" <?php echo $registro_delegados_habilitado ? 'checked' : ''; ?>>
          <span class="ml-2 text-gray-700">Habilitar registro público</span>
        </label>
      </div>

      <div id="toggleRegistroAlert" class="hidden bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-2 rounded mb-4 text-sm"></div>

      <div class="flex justify-end gap-3">
        <button type="button" onclick="cerrarModal('modalToggleRegistro')" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">Cancelar</button>
        <button id="btnSaveToggleRegistro" type="button" class="bg-navy text-white px-4 py-2 rounded hover:opacity-90">Guardar</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const saveBtn = document.getElementById('btnSaveToggleRegistro');
  if (!saveBtn) return;
  saveBtn.addEventListener('click', async function(){
    const checkbox = document.getElementById('toggleRegistroCheckbox');
    const alertEl = document.getElementById('toggleRegistroAlert');
    if (alertEl) alertEl.classList.add('hidden');

    const enable = checkbox.checked ? '1' : '0';
    saveBtn.disabled = true;
    try {
      const resp = await fetch('actions/toggle_registration.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({ enable: enable })
      });
      const data = await resp.json();
      if (resp.status === 200 && data && data.success) {
        cerrarModal('modalToggleRegistro');
        if (typeof mostrarExito === 'function') {
          mostrarExito(data.message || 'Configuración actualizada.');
        } else {
          alert(data.message || 'Configuración actualizada.');
        }
        setTimeout(()=> location.reload(), 700);
        return;
      } else {
        const msg = (data && data.message) ? data.message : 'Error al guardar la configuración.';
        if (alertEl) { alertEl.textContent = msg; alertEl.classList.remove('hidden'); }
        else alert(msg);
      }
    } catch (err) {
      console.error('toggle_registration error:', err);
      if (alertEl) { alertEl.textContent = 'Error de red al intentar guardar.'; alertEl.classList.remove('hidden'); }
      else alert('Error de red al intentar guardar.');
    } finally {
      saveBtn.disabled = false;
    }
  });
})();
</script>
<?php endif; ?>

<!-- Modal cambiar clave de registro (admin) -->
<?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'administrador'): ?>
<div id="modalChangeKey" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-lg shadow-2xl w-full max-w-md">
    <div class="p-6">
      <h3 class="text-lg font-bold mb-2"><i class="fas fa-key mr-2"></i>Cambiar clave de registro</h3>
      <p class="text-sm text-gray-700 mb-4">Introduce la nueva clave que se usará para el registro público de delegados.</p>

      <div id="changeKeyAlert" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4 text-sm"></div>

      <form id="formChangeKey">
        <label class="block text-sm mb-2">Nueva clave</label>
        <input id="newKeyInput" name="new_key" type="text" class="w-full px-3 py-2 border rounded mb-3" required>

        <label class="block text-sm mb-2">Confirmar nueva clave</label>
        <input id="confirmKeyInput" name="confirm_key" type="text" class="w-full px-3 py-2 border rounded mb-4" required>

        <div class="flex justify-end gap-3">
          <button type="button" onclick="cerrarModal('modalChangeKey')" class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300">Cancelar</button>
          <button id="btnSaveKey" type="submit" class="bg-navy text-white px-4 py-2 rounded hover:opacity-90">Guardar</button>
        </div>
      </form>

      <div id="changeKeyProgress" class="mt-3 hidden text-sm text-gray-600">Guardando...</div>
    </div>
  </div>
</div>

<script>
(function(){
  const form = document.getElementById('formChangeKey');
  const alertEl = document.getElementById('changeKeyAlert');
  const progress = document.getElementById('changeKeyProgress');
  const btnSave = document.getElementById('btnSaveKey');

  if (!form) return;

  function showAlert(msg, isError = true) {
    if (!alertEl) { alert(msg); return; }
    alertEl.textContent = msg;
    alertEl.classList.remove('hidden');
    if (!isError) {
      alertEl.classList.remove('bg-red-100','border-red-400','text-red-700');
      alertEl.classList.add('bg-green-100','border-green-400','text-green-700');
    } else {
      alertEl.classList.add('bg-red-100','border-red-400','text-red-700');
      alertEl.classList.remove('bg-green-100','border-green-400','text-green-700');
    }
  }

  form.addEventListener('submit', async function(e){
    e.preventDefault();
    if (alertEl) alertEl.classList.add('hidden');

    const newKey = (document.getElementById('newKeyInput')||{}).value || '';
    const confirmKey = (document.getElementById('confirmKeyInput')||{}).value || '';

    if (!newKey.trim()) return showAlert('La nueva clave no puede estar vacía.');
    if (newKey !== confirmKey) return showAlert('Las claves no coinciden.');

    btnSave.disabled = true;
    progress.classList.remove('hidden');

    try {
      const body = new URLSearchParams({ new_key: newKey, confirm_key: confirmKey });
      const resp = await fetch('config/update_registration_key.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: body
      });

      let data;
      try { data = await resp.json(); } catch(e) { data = null; }

      if (resp.ok && data && data.success) {
        showAlert(data.message || 'Clave actualizada', false);
        // cerrar modal tras breve delay y ocultar mensaje
        setTimeout(function(){
          cerrarModal('modalChangeKey');
          if (alertEl) { alertEl.classList.add('hidden'); }
        }, 900);
        return;
      } else {
        const msg = (data && data.message) ? data.message : ('Error del servidor (' + resp.status + ')');
        showAlert(msg);
      }
    } catch (err) {
      console.error('update key error:', err);
      showAlert('Error de red al intentar guardar la clave.');
    } finally {
      btnSave.disabled = false;
      progress.classList.add('hidden');
    }
  });
})();
</script>
<?php endif; ?>

<!-- Bloque: Modal y script para "Resetear la BD" (sin petición de contraseña; solo confirma escribiendo ELIMINAR) -->
<?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'administrador'): ?>
<div id="modalResetBD" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-lg shadow-2xl w-full max-w-xl">
    <div class="p-6">
      <h3 class="text-xl font-bold text-red-600 mb-2"><i class="fas fa-exclamation-triangle mr-2"></i>Restablecer Base de Datos</h3>
      <p class="text-sm text-gray-700 mb-4">
        Esta acción eliminará todos los datos de la base de datos excepto las cuentas con tipo <strong>administrador</strong>.
        Es irreversible. Asegúrate de tener una copia de seguridad antes de continuar.
      </p>

      <div id="resetAlert" class="hidden bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-2 rounded mb-4 text-sm"></div>

      <label class="block text-gray-700 font-semibold mb-2">Para confirmar, escribe <code class="font-mono">ELIMINAR</code> en el campo y presiona "Confirmar"</label>
      <input id="confirmResetInput" type="text" class="w-full px-3 py-2 border rounded mb-4" placeholder="Escribe ELIMINAR">

      <div class="flex gap-3 justify-end">
        <button type="button" onclick="cerrarModal('modalResetBD')" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Cancelar</button>
        <button id="confirmResetBtn" type="button" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
          Confirmar
        </button>
      </div>

      <div id="resetProgress" class="mt-4 hidden">
        <p class="text-sm text-gray-600">Ejecutando reseteo... esto puede tardar varios segundos.</p>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const btn = document.getElementById('confirmResetBtn');
  if (!btn) return;

  function showResetAlert(msg) {
    const alertEl = document.getElementById('resetAlert');
    if (alertEl) {
      alertEl.textContent = msg;
      alertEl.classList.remove('hidden');
    } else {
      alert(msg);
    }
  }

  btn.addEventListener('click', async function(){
    const input = document.getElementById('confirmResetInput');
    const alertEl = document.getElementById('resetAlert');
    const progress = document.getElementById('resetProgress');
    if (!input) return;

    // Validación cliente: solo pedir "ELIMINAR"
    if ((input.value || '').trim() !== 'ELIMINAR') {
      showResetAlert('Debes escribir exactamente ELIMINAR para confirmar.');
      return;
    }

    if (alertEl) alertEl.classList.add('hidden');
    btn.disabled = true;
    progress.classList.remove('hidden');

    try {
      const body = new URLSearchParams({ confirm: 'ELIMINAR' });

      const resp = await fetch('actions/limpiar_bd.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: body
      });

      // Si el servidor redirige (ej. a login), mostramos mensaje claro y evitamos prompts
      if (resp.redirected) {
        showResetAlert('La sesión ha expirado o se requiere inicio de sesión. Por favor, inicia sesión como administrador y vuelve a intentarlo.');
        setTimeout(()=> window.location.href = resp.url, 1200);
        return;
      }

      // Manejar códigos 401/403 explícitamente
      if (resp.status === 401 || resp.status === 403) {
        try {
          const j = await resp.json();
          showResetAlert(j.message || 'No autorizado.');
        } catch (e) {
          const txt = await resp.text();
          showResetAlert(txt || 'No autorizado.');
        }
        return;
      }

      // Intentar parsear JSON; si falla mostrar texto
      let data = null;
      try {
        data = await resp.json();
      } catch (parseErr) {
        const txt = await resp.text();
        showResetAlert(txt || 'Respuesta inesperada del servidor.');
        return;
      }

      if (resp.ok && data && data.success) {
        cerrarModal('modalResetBD');
        if (typeof mostrarExito === 'function') {
          mostrarExito(data.message || 'Base de datos reseteada. Se conservaron los administradores.');
        } else {
          alert(data.message || 'Base de datos reseteada. Se conservaron los administradores.');
        }
        // Forzar logout/recarga para evitar inconsistencias en sesión
        window.location.href = 'auth/logout.php';
        return;
      } else {
        const msg = (data && data.message) ? data.message : 'Error al resetear la base de datos.';
        showResetAlert(msg);
      }
    } catch (err) {
      console.error('limpiar_bd error:', err);
      showResetAlert('Error de red al intentar resetear la base de datos.');
    } finally {
      btn.disabled = false;
      progress.classList.add('hidden');
      if (input) input.value = '';
    }
  });
})();
</script>
<?php endif; ?>

<!-- MODAL Logs de actividad (admin) -->
<?php if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'administrador'): ?>
<!-- Modal Logs (alto fijo: 75vh, ancho max 75rem) -->
<div id="modalLogs" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-lg shadow-2xl w-full" 
       style="max-width:75rem; width:100%; height:75vh; display:flex; flex-direction:column; overflow:hidden;">
    <!-- Header (fijo) -->
    <div style="background:#1e293b; color:#fff; padding:1rem 1.25rem; display:flex; align-items:center; justify-content:space-between;">
      <h2 style="font-size:1.125rem; font-weight:700; display:flex; align-items:center; gap:0.5rem;">
        <i class="fas fa-list" aria-hidden="true"></i>
        Registro de Actividad
      </h2>
      <div style="display:flex; gap:0.5rem; align-items:center;">
        <button id="btnExportCSVHeader" style="background:#374151;color:#fff;padding:0.35rem 0.6rem;border-radius:0.25rem;font-size:0.875rem;">Exportar CSV</button>
        <button onclick="closeLogsModal()" aria-label="Cerrar" style="color:#fff;font-size:1.25rem;font-weight:700;background:transparent;border:0;padding:0 0.5rem;line-height:1;">×</button>
      </div>
    </div>

    <!-- Body (flex) -->
    <div style="padding:1rem; display:flex; flex-direction:column; gap:0.75rem; flex:1; min-height:0;">
      <!-- filtros en una sola fila (responsive) -->
      <form id="formLogsFilter" style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
        <input type="text" name="q" placeholder="Buscar (acción, ruta, detalle)" style="flex:1; min-width:180px; padding:0.5rem; border:1px solid #ddd; border-radius:6px;" />
        <select name="tipo" style="width:12.5rem; padding:0.5rem; border:1px solid #ddd; border-radius:6px;">
          <option value="">Todos los tipos</option>
          <option value="administrador">Administrador</option>
          <option value="asesor">Asesor</option>
          <option value="delegado">Delegado</option>
        </select>
        <input type="text" name="usuario" placeholder="Usuario (nombre o id)" style="width:14rem; padding:0.5rem; border:1px solid #ddd; border-radius:6px;" />
        <input type="date" name="desde" style="padding:0.5rem; border:1px solid #ddd; border-radius:6px;" />
        <input type="date" name="hasta" style="padding:0.5rem; border:1px solid #ddd; border-radius:6px;" />
        <!-- Espacio final para botones opcionales -->
        <div style="margin-left:auto; display:flex; gap:0.5rem; align-items:center;">
          <button type="button" id="btnVaciarLogsSmall" style="background:#dc2626;color:#fff;padding:0.45rem 0.65rem;border-radius:6px;border:0;display:none;">Vaciar</button>
        </div>
      </form>

      <!-- contenedor de logs: ocupa el espacio restante y es scrollable -->
      <div id="logsContainer" style="flex:1; min-height:0; overflow:auto; border:1px solid #e6e6e6; border-radius:6px; background:#fff; padding:0.25rem;">
        <!-- loader o tabla vendrán aquí; altura fija evita saltos -->
        <div style="padding:1rem; color:#4b5563; font-size:0.9rem;">Cargando...</div>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const modalId = 'modalLogs';
  const containerId = 'logsContainer';
  const formId = 'formLogsFilter'; // debe coincidir con el HTML del modal
  const batchLimit = 50;          // tamaño del lote
  const POLL_MS = 5000;           // intervalo polling 5s (ajustable)
  const loaderId = 'logsLoaderOverlay';

  let offset = 0;
  let loading = false;
  let more = true;
  let currentController = null;
  let pollInterval = null;
  let lastTimestamp = null;

  function getEl(id){ return document.getElementById(id); }

  function debounce(fn, wait) {
    let t;
    return function(...args) {
      clearTimeout(t);
      t = setTimeout(() => fn.apply(this, args), wait);
    };
  }

  // ---------- Helpers para manejar filas y tbody sin reemplazar todo ----------
  function parseRowsFromHtml(html) {
    const tmp = document.createElement('div');
    tmp.innerHTML = html;
    const trs = tmp.querySelectorAll('table tbody tr');
    // Return clones so they can be inserted into the real DOM
    return Array.from(trs).map(tr => tr.cloneNode(true));
  }

  function replaceTbodyRows(container, newRows) {
    let table = container.querySelector('table');
    if (!table) {
      // If server returned a table with thead, it would have been in html; fallback create table skeleton
      container.innerHTML = '<table class="w-full text-sm"><thead class="bg-gray-100"><tr><th class="px-3 py-2">Fecha</th><th class="px-3 py-2">Usuario</th><th class="px-3 py-2">Tipo</th><th class="px-3 py-2">Acción</th><th class="px-3 py-2">IP</th><th class="px-3 py-2">Detalle</th></tr></thead><tbody></tbody></table>';
      table = container.querySelector('table');
    }
    let tbody = table.querySelector('tbody');
    if (!tbody) {
      tbody = document.createElement('tbody');
      table.appendChild(tbody);
    }
    // Replace content preserving tbody element reference
    tbody.innerHTML = '';
    newRows.forEach(tr => tbody.appendChild(tr));
  }

  function appendRowsToTbody(container, newRows) {
    let table = container.querySelector('table');
    if (!table) {
      const wrapper = document.createElement('div');
      wrapper.innerHTML = '<table class="w-full text-sm"><thead class="bg-gray-100"><tr><th class="px-3 py-2">Fecha</th><th class="px-3 py-2">Usuario</th><th class="px-3 py-2">Tipo</th><th class="px-3 py-2">Acción</th><th class="px-3 py-2">IP</th><th class="px-3 py-2">Detalle</th></tr></thead><tbody></tbody></table>';
      container.appendChild(wrapper);
      table = container.querySelector('table');
    }
    let tbody = table.querySelector('tbody');
    if (!tbody) {
      tbody = document.createElement('tbody');
      table.appendChild(tbody);
    }
    newRows.forEach(tr => tbody.appendChild(tr));
  }

  // ---------- Carga por lotes (inteligente: reemplaza sólo tbody o añade filas) ----------
  async function loadBatch(reset = false) {
    if (loading) return;
    if (reset) {
      offset = 0;
      more = true;
    }
    if (!more && !reset) return;

    const container = getEl(containerId);
    if (!container) return;

    // loader overlay (non-destructive)
    if (reset) {
      // reduce opacity of current content
      container.querySelectorAll('*').forEach(n => { if (n.id !== loaderId) n.style.opacity = '0.35'; });
      // create overlay loader
      const loaderEl = document.createElement('div');
      loaderEl.id = loaderId;
      loaderEl.style.cssText = 'position:absolute; inset:0; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.6); z-index:5;';
      loaderEl.innerHTML = '<div style="padding:1rem; background:#fff; border-radius:6px; box-shadow:0 2px 6px rgba(0,0,0,0.08); color:#374151;">Cargando...</div>';
      // ensure container is positioned
      if (!container.style.position) container.style.position = 'relative';
      container.appendChild(loaderEl);
    } else {
      // small inline loader at the end when loading more
      if (!document.getElementById(loaderId + '_mini')) {
        const mini = document.createElement('div');
        mini.id = loaderId + '_mini';
        mini.className = 'logs-mini-loader';
        mini.style.cssText = 'padding:0.5rem; text-align:center; color:#374151;';
        mini.textContent = 'Cargando más registros...';
        container.appendChild(mini);
      }
    }

    // build params from form
    const params = new URLSearchParams();
    const form = getEl(formId);
    if (form) {
      const fd = new FormData(form);
      for (const [k,v] of fd.entries()) {
        const vs = (v || '').toString().trim();
        if (vs !== '') params.append(k, vs);
      }
    }
    params.append('limit', String(batchLimit));
    params.append('offset', String(offset));

    // abort previous
    try { if (currentController) { currentController.abort(); } } catch(e){}

    loading = true;
    currentController = new AbortController();

    try {
      const resp = await fetch('actions/get_logs.php?' + params.toString(), {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        signal: currentController.signal
      });

      if (!resp.ok) {
        if (resp.status === 403) {
          container.innerHTML = '<div style="padding:1rem;color:#b91c1c;">No autorizado.</div>';
        } else {
          container.innerHTML = '<div style="padding:1rem;color:#b91c1c;">Error al cargar logs.</div>';
        }
        loading = false;
        return;
      }

      const data = await resp.json();
      if (!data || !data.success) {
        container.innerHTML = '<div style="padding:1rem;color:#b91c1c;">' + (data && data.message ? data.message : 'Error al cargar logs.') + '</div>';
        loading = false;
        return;
      }

      const html = data.html || '';
      const newRows = parseRowsFromHtml(html);

      if (reset) {
        // Replace only tbody (avoid reflow of entire modal)
        replaceTbodyRows(container, newRows);
        // reset scroll to top of the scrollable area
        container.scrollTop = 0;

        // update lastTimestamp from html if possible
        const match = html.match(/<td[^>]*>(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})<\/td>/);
        if (match) lastTimestamp = match[1];
        else {
          const now = new Date();
          const pad = n => String(n).padStart(2, '0');
          lastTimestamp = now.getFullYear() + '-' + pad(now.getMonth()+1) + '-' + pad(now.getDate()) + ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
        }
      } else {
        // append incremental
        const prevScrollHeight = container.scrollHeight;
        if (newRows.length > 0) appendRowsToTbody(container, newRows);
        const isAtBottom = (container.scrollTop + container.clientHeight + 10) >= prevScrollHeight;
        if (isAtBottom) container.scrollTop = container.scrollHeight;
      }

      more = !!data.more;
      offset += (data.count || 0);

      if (offset === 0 && !more && newRows.length === 0) {
        container.innerHTML = '<div style="padding:1rem;color:#374151;">No hay registros recientes</div>';
      }
    } catch (err) {
      if (err && err.name === 'AbortError') {
        // aborted - ignore
      } else {
        console.error('fetchLogs error:', err);
        if (container) container.innerHTML = '<div style="padding:1rem;color:#b91c1c;">Error de red al cargar logs.</div>';
      }
    } finally {
      // remove loaders
      try {
        const loader = document.getElementById(loaderId);
        if (loader && loader.parentNode) loader.parentNode.removeChild(loader);
        const mini = document.getElementById(loaderId + '_mini');
        if (mini && mini.parentNode) mini.parentNode.removeChild(mini);
        // restore opacity
        if (container) container.querySelectorAll('*').forEach(n => { if (n.id !== loaderId) n.style.opacity = ''; });
      } catch (e) { /* ignore cleanup errors */ }

      loading = false;
      currentController = null;
    }
  }

  // ---------- Polling: insertar nuevas filas al inicio del tbody y resaltarlas ----------
  async function pollNew() {
    if (!lastTimestamp) return;
    try {
      const resp = await fetch('actions/get_new_logs.php?since=' + encodeURIComponent(lastTimestamp), {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (!resp.ok) return;
      const data = await resp.json();
      if (!data || !data.success || !data.rows || data.rows.length === 0) return;

      const rows = data.rows; // ASC: oldest -> newest
      const container = getEl(containerId);
      if (!container) return;

      const tbody = container.querySelector && container.querySelector('table tbody');

      if (tbody) {
        // create tr elements and prepend (so newest appears first)
        const created = [];
        for (let i = 0; i < rows.length; i++) {
          const r = rows[i];
          const tr = document.createElement('tr');
          tr.className = 'border-b hover:bg-gray-50';

          const tdFecha = document.createElement('td');
          tdFecha.className = 'px-3 py-2 align-top';
          tdFecha.textContent = r.fecha || '';

          const tdUsuario = document.createElement('td');
          tdUsuario.className = 'px-3 py-2 align-top';
          tdUsuario.textContent = r.usuario_nombre ? r.usuario_nombre : (r.usuario_id ? 'ID:' + r.usuario_id : 'Anónimo');

          const tdTipo = document.createElement('td');
          tdTipo.className = 'px-3 py-2 align-top';
          tdTipo.textContent = r.tipo_usuario || '';

          const tdAccion = document.createElement('td');
          tdAccion.className = 'px-3 py-2 align-top';
          tdAccion.textContent = r.accion || '';

          const tdIp = document.createElement('td');
          tdIp.className = 'px-3 py-2 align-top';
          tdIp.textContent = r.ip || '';

          const tdDetalle = document.createElement('td');
          tdDetalle.className = 'px-3 py-2 align-top';
          const pre = document.createElement('pre');
          pre.style.whiteSpace = 'pre-wrap';
          pre.style.wordBreak = 'break-word';
          pre.style.maxWidth = '36rem';
          let detalleText = '';
          if (r.detalle) {
            try { const det = JSON.parse(r.detalle); detalleText = JSON.stringify(det); } catch(e) { detalleText = r.detalle; }
          }
          pre.textContent = detalleText;
          tdDetalle.appendChild(pre);

          tr.appendChild(tdFecha);
          tr.appendChild(tdUsuario);
          tr.appendChild(tdTipo);
          tr.appendChild(tdAccion);
          tr.appendChild(tdIp);
          tr.appendChild(tdDetalle);

          created.push(tr);
        }

        // prepend in reverse order so newest is at top
        for (let i = created.length - 1; i >= 0; i--) {
          const tr = created[i];
          tbody.insertBefore(tr, tbody.firstChild);
          // highlight temporarily
          tr.style.backgroundColor = '#fffbeb'; // light yellow
          (function(el){ setTimeout(()=>{ el.style.backgroundColor = ''; }, 3000); })(tr);
        }

        // update lastTimestamp
        const newest = rows[rows.length - 1];
        if (newest && newest.fecha) lastTimestamp = newest.fecha;
      } else {
        // fallback reload
        await loadBatch(true);
      }
    } catch (e) {
      console.error('Error polling logs:', e);
    }
  }

  // ---------- Abrir / Cerrar modal ----------
  window.openLogsModal = function() {
    const form = getEl(formId); if (form) form.reset();
    if (typeof abrirModal === 'function') abrirModal(modalId);
    else {
      const el = getEl(modalId); if (!el) return; el.classList.remove('hidden'); el.classList.add('flex');
    }
    offset = 0; more = true;
    loadBatch(true).then(() => {
      if (pollInterval) clearInterval(pollInterval);
      pollInterval = setInterval(pollNew, POLL_MS);
    });
  };

  window.closeLogsModal = function(){
    try { if (currentController) { currentController.abort(); currentController = null; } } catch(e){}
    const form = getEl(formId); if (form) form.reset();
    const container = getEl(containerId);
    if (container) {
      container.innerHTML = '<div style="padding:1rem;color:#374151;">Cerrado — abre el modal para ver los logs.</div>';
      container.scrollTop = 0;
    }
    if (typeof cerrarModal === 'function') cerrarModal(modalId);
    else {
      const el = getEl(modalId); if (!el) return; el.classList.add('hidden'); el.classList.remove('flex');
    }
    if (pollInterval) { clearInterval(pollInterval); pollInterval = null; }
  };

  // ---------- Scroll infinito ----------
  const containerEl = getEl(containerId);
  if (containerEl) {
    containerEl.addEventListener('scroll', function() {
      if (loading || !more) return;
      const threshold = 300;
      if (containerEl.scrollTop + containerEl.clientHeight + threshold >= containerEl.scrollHeight) {
        loadBatch(false);
      }
    }, { passive: true });
  }

  // ---------- Filtros: en tiempo real (debounced) ----------
  const formEl = getEl(formId);
  if (formEl) {
    formEl.addEventListener('submit', function(e){ e.preventDefault(); offset = 0; more = true; loadBatch(true); });
    formEl.querySelectorAll('input, select').forEach(inp => {
      inp.addEventListener('change', function(){ offset = 0; more = true; loadBatch(true); });
    });
    const qInput = formEl.querySelector('input[name="q"]');
    const usuarioInput = formEl.querySelector('input[name="usuario"]');
    if (qInput) qInput.addEventListener('input', debounce(()=>{ offset = 0; more = true; loadBatch(true); }, 300));
    if (usuarioInput) usuarioInput.addEventListener('input', debounce(()=>{ offset = 0; more = true; loadBatch(true); }, 300));
  }

  // Exponer carga manual si se necesita
  window.loadLogs = function() { offset = 0; more = true; loadBatch(true); };

  // limpiar intervalos al salir de la página (por seguridad)
  window.addEventListener('beforeunload', function(){ if (pollInterval) clearInterval(pollInterval); });

})();
</script>
<?php endif; ?>

<!-- MODAL: Mostrar SQL para vaciar logs + copiar al portapapeles -->
<div id="modalClearLogs" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl" style="max-width:80rem; outline:none;">
    <div class="p-4 border-b flex items-center justify-between">
      <div>
        <h3 class="text-lg font-bold"><i class="fas fa-exclamation-triangle mr-2 text-red-600"></i> Vaciar tabla de Logs</h3>
        <p class="text-sm text-gray-600 mt-1">Este código vacía la tabla <code>logs_actividad</code> y reinicia el contador de IDs. Haz backup antes de ejecutar en producción.</p>
      </div>
      <div><button onclick="closeClearLogsModal()" class="px-3 py-1 rounded bg-gray-200">Cerrar</button></div>
    </div>

    <div class="p-4">
      <label class="block text-sm font-semibold mb-2">SQL para ejecutar</label>
      <pre id="clearLogsSql" class="bg-gray-100 p-3 rounded text-sm overflow-auto" style="max-height:40vh; white-space:pre-wrap;">
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `logs_actividad`;
SET FOREIGN_KEY_CHECKS = 1;
      </pre>

      <div class="mt-3 flex items-center gap-2">
        <button id="btnCopyClearLogs" class="bg-navy text-white px-4 py-2 rounded hover:opacity-90">
          <i class="fas fa-copy mr-2"></i> Copiar SQL
        </button>

        <a id="btnOpenPhpMyAdmin" class="px-4 py-2 border rounded text-sm text-gray-700 hover:bg-gray-100" href="#" target="_blank" rel="noopener noreferrer" style="display:none;">
          Abrir phpMyAdmin
        </a>

        <span id="copyStatus" class="text-sm text-green-600 ml-2 hidden">Copiado ✔</span>
      </div>

      <div class="mt-4 text-sm text-gray-600">
        Recomendación: copia y pega el SQL en phpMyAdmin o en la consola mysql. Si deseas, puedo generar un archivo .sql para descargar.
      </div>
    </div>
  </div>
</div>

<script>
/* JS para modal y copiar SQL (pega esto en el mismo archivo includes/sidebar.php, después del modal) */
(function(){
  const modalId = 'modalClearLogs';
  const sqlElId = 'clearLogsSql';
  const copyBtnId = 'btnCopyClearLogs';
  const statusId = 'copyStatus';

  window.openClearLogsModal = function() {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.classList.remove('hidden'); modal.classList.add('flex');
    // Ocultar estado de copia si estaba visible
    const st = document.getElementById(statusId); if (st) st.classList.add('hidden');
  };

  window.closeClearLogsModal = function() {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.classList.add('hidden'); modal.classList.remove('flex');
  };

  document.getElementById(copyBtnId)?.addEventListener('click', async function(){
    const pre = document.getElementById(sqlElId);
    if (!pre) return;
    // Obtener texto limpio
    const sqlText = pre.innerText.trim();
    // Intentar navigator.clipboard
    try {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        await navigator.clipboard.writeText(sqlText);
      } else {
        // Fallback: crear textarea temporal
        const ta = document.createElement('textarea');
        ta.value = sqlText;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
      }
      const st = document.getElementById(statusId);
      if (st) { st.classList.remove('hidden'); st.textContent = 'Copiado ✔'; setTimeout(()=> st.classList.add('hidden'), 2500); }
    } catch (err) {
      alert('No se pudo copiar automáticamente. Selecciona y copia manualmente el SQL.\n\n' + err);
    }
  });
})();
</script>

<!-- Script para submenús -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  function toggleSubmenu(button) {
    const targetId = button.getAttribute('data-target');
    const panel = document.getElementById(targetId);
    if (!panel) return;

    const expanded = button.getAttribute('aria-expanded') === 'true';
    // Toggle aria
    button.setAttribute('aria-expanded', (!expanded).toString());
    panel.setAttribute('aria-hidden', expanded ? 'true' : 'false');

    // Toggle chevron rotation
    const chevron = button.querySelector('.fa-chevron-down');
    if (chevron) chevron.classList.toggle('rotate-180', !expanded);

    // Smooth open/close using max-height
    if (!expanded) {
      // open: set maxHeight to scrollHeight
      panel.style.maxHeight = panel.scrollHeight + 'px';
    } else {
      // close: set maxHeight to 0
      panel.style.maxHeight = '0px';
    }
  }

  // Initialize buttons
  document.querySelectorAll('button[data-toggle="submenu"]').forEach(btn => {
    // Ensure initial state closed
    const targetId = btn.getAttribute('data-target');
    const panel = document.getElementById(targetId);
    if (panel) {
      panel.style.maxHeight = '0px';
      panel.setAttribute('aria-hidden', 'true');
    }
    btn.setAttribute('aria-expanded', 'false');

    btn.addEventListener('click', function(e){
      e.preventDefault();
      toggleSubmenu(btn);
    });
  });

  // Close other submenus when opening one (optional): uncomment if desired
  // document.querySelectorAll('button[data-toggle="submenu"]').forEach(btn => {
  //   btn.addEventListener('click', function() {
  //     document.querySelectorAll('button[data-toggle="submenu"]').forEach(other => {
  //       if (other !== btn && other.getAttribute('aria-expanded') === 'true') toggleSubmenu(other);
  //     });
  //   });
  // });

});
</script>

<script>
/*
  Limpia y resetea formularios y errores cuando se cierra un modal.
  Aplica a: modalCrearDelegado y modalCrearUsuario.
  Inserta este script al final de includes/sidebar.php (una única vez).
*/

(function(){
  const modalIds = ['modalCrearDelegado', 'modalCrearUsuario'];

  // Limpia el estado interno del modal: resetea forms y oculta mensajes de error
  function clearModalState(modal) {
    if (!modal) return;

    // Resetear formularios (si hay varios)
    modal.querySelectorAll('form').forEach(f => {
      try { f.reset(); } catch(e) {}
    });

    // Limpiar inputs visibles (por si algún script añade texto fuera de formulario)
    modal.querySelectorAll('input, textarea, select').forEach(el => {
      // Si hay atributos personalizados de error, retirarlos
      el.classList.remove('is-invalid', 'input-error');
    });

    // Ocultar y limpiar mensajes de error: ids que empiecen por "error", o clases comunes
    const errorSelectors = [
      '[id^="error"]',
      '.error',
      '.error-message',
      '.alert[role="alert"]',
      '.text-red-600',
      '.text-red-700',
      '.bg-red-100'
    ];
    modal.querySelectorAll(errorSelectors.join(',')).forEach(el => {
      try {
        // Revertir a estado "oculto" conservador
        el.classList.add('hidden');
        // limpiar texto para que no reaparezca
        if (el.tagName.toLowerCase() === 'input' || el.tagName.toLowerCase() === 'textarea') {
          el.value = '';
        } else {
          el.textContent = '';
        }
      } catch(e){}
    });
  }

  // AbortController map por modal (si hubiera fetchs asociados)
  const controllers = {};

  function abortModalFetch(modalId) {
    const c = controllers[modalId];
    if (c && typeof c.abort === 'function') {
      try { c.abort(); } catch(e){}
      delete controllers[modalId];
    }
  }

  // Observador: cuando cambie la clase del modal y detectemos que se ocultó, limpiamos estado.
  modalIds.forEach(modalId => {
    const modal = document.getElementById(modalId);
    if (!modal) return;

    // Si ya existe un close button que llama a cerrarModal(modalId), este observer se encargará igual.
    // Usamos MutationObserver para detectar cambios de atributo 'class'
    const mo = new MutationObserver(muts => {
      muts.forEach(m => {
        if (m.attributeName === 'class') {
          const cls = modal.className || '';
          const hidden = cls.indexOf('hidden') !== -1;
          if (hidden) {
            // Abort any pending fetches for this modal
            abortModalFetch(modalId);
            // Clear form fields and error messages
            clearModalState(modal);
          }
        }
      });
    });
    mo.observe(modal, { attributes: true, attributeFilter: ['class'] });

    // Además: si existen botones de cerrar internos (con data-close o class .close-modal), los engancha también
    modal.querySelectorAll('[data-close], .close-modal, .btn-close').forEach(btn => {
      btn.addEventListener('click', function() {
        // give the closing animation a tick then clear (in case class hidden applied via JS)
        setTimeout(() => {
          abortModalFetch(modalId);
          clearModalState(modal);
        }, 120);
      });
    });

    // También limpiar si el modal tiene overlay y se hace click fuera (si el proyecto permite cerrar así)
    modal.addEventListener('click', function(e){
      // si se hace click en el fondo oscuro (overlay) y no dentro del contenido:
      if (e.target === modal) {
        // asíncrono para permitir que la lógica de ocultar modal se ejecute antes
        setTimeout(() => {
          abortModalFetch(modalId);
          clearModalState(modal);
        }, 120);
      }
    });
  });

  // Helper público: por si quieres forzar limpieza manualmente (ej: al llamar cerrarModal)
  window.clearAndResetModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    abortModalFetch(modalId);
    clearModalState(modal);
  };

  // Si tu función global cerrarModal(modalId) existe y quieres que limpie automáticamente,
  // puedes envolverla así (no obliga a nada si no existe):
  if (typeof window.cerrarModal === 'function') {
    const originalCerrar = window.cerrarModal;
    window.cerrarModal = function(modalId) {
      try { originalCerrar(modalId); } catch(e) {}
      // limpiar un poco después para dejar que la clase 'hidden' se aplique
      setTimeout(() => clearAndResetModal(modalId), 120);
    };
  }

})();
</script>

<script>
/*
  Protege modales para que NO se cierren al hacer click fuera ni con Escape.
  Pégalo directamente en includes/sidebar.php (antes de </body>).
  - Solo permite cerrar el modal mediante botones internos (ej. Cancelar) que llamen a las funciones de cierre existentes.
  - No resetea formularios; evita que handlers inline o globales ejecuten el cierre/limpieza por backdrop.
*/
(function(){
  try {
    const modalIds = ['registroModal', 'modalCrearUsuario', 'modalCrearDelegado'];
    const extraSelector = '[role="dialog"], .modal'; // detecta modales generales
    const protectedModals = new Set();

    function isVisible(modal) {
      if (!modal) return false;
      // consider visible if in DOM and not hidden by 'hidden' class or display:none
      if (modal.classList && modal.classList.contains('hidden')) return false;
      const style = window.getComputedStyle(modal);
      return style && style.display !== 'none' && style.visibility !== 'hidden' && document.body.contains(modal);
    }

    function getContentElement(modal) {
      if (!modal) return null;
      // Prefer explicit content selectors
      const selectors = ['[role="document"]', '[role="dialog"] *[role="document"]', '.modal-content', '.modal-body', '.modal-inner', '.modal-card', '[data-modal-content]'];
      for (const sel of selectors) {
        const el = modal.querySelector(sel);
        if (el) return el;
      }
      // Fallback: first element child that is not an overlay
      const children = Array.from(modal.children).filter(c => c.nodeType === 1);
      if (children.length === 1) return children[0];
      // If many, try to pick the deepest with many descendants
      let best = null, bestCount = -1;
      children.forEach(c => {
        const cnt = c.getElementsByTagName('*').length;
        if (cnt > bestCount) { bestCount = cnt; best = c; }
      });
      return best || modal;
    }

    function gatherModals() {
      const list = [];
      modalIds.forEach(id => {
        const m = document.getElementById(id);
        if (m) list.push(m);
      });
      // also include generic modal selectors
      document.querySelectorAll(extraSelector).forEach(m => {
        if (m && m.id && modalIds.indexOf(m.id) === -1) { /* if has id not already included, add */ }
        list.push(m);
      });
      // Deduplicate
      return Array.from(new Set(list));
    }

    function protect(modal) {
      if (!modal || protectedModals.has(modal)) return;
      protectedModals.add(modal);
      const content = getContentElement(modal) || modal;

      // Capture phase listener on document to intercept clicks BEFORE inline handlers/bubbling
      function onDocClickCapture(e) {
        try {
          if (!isVisible(modal)) return;
          // If click inside modal content, allow it
          if (content.contains(e.target)) return;
          // Click outside content while modal visible: prevent further handling (so modal won't close/reset)
          e.stopImmediatePropagation();
          e.preventDefault();
          // Don't modify modal visibility; keep it open
        } catch (err) {
          console.error('modal-protect click error:', err);
        }
      }

      // Capture Escape keypresss
      function onDocKeydownCapture(e) {
        try {
          if (!isVisible(modal)) return;
          if (e.key === 'Escape' || e.key === 'Esc') {
            e.stopImmediatePropagation();
            e.preventDefault();
          }
        } catch (err) {
          console.error('modal-protect keydown error:', err);
        }
      }

      document.addEventListener('click', onDocClickCapture, true);
      document.addEventListener('keydown', onDocKeydownCapture, true);

      // Also neutralize inline onclick on the modal element itself if it closes/reset
      try {
        if (modal.hasAttribute && modal.hasAttribute('onclick')) {
          // preserve original as data attribute just in case, then remove to avoid accidental resets
          const orig = modal.getAttribute('onclick');
          modal.setAttribute('data-onclick-removed', orig);
          modal.removeAttribute('onclick');
        }
      } catch (err) { /* ignore */ }

      // Observe modal for removal/disconnect to cleanup listeners if needed (lightweight)
      const mo = new MutationObserver(() => {
        if (!document.body.contains(modal)) {
          try { document.removeEventListener('click', onDocClickCapture, true); } catch(e){}
          try { document.removeEventListener('keydown', onDocKeydownCapture, true); } catch(e){}
          protectedModals.delete(modal);
          mo.disconnect();
        }
      });
      mo.observe(document.documentElement || document.body, { childList: true, subtree: true });
    }

    // Initial protect for existing modals
    const initial = gatherModals();
    initial.forEach(m => protect(m));

    // Watch for future modals added to DOM
    const observer = new MutationObserver((mutations) => {
      try {
        const nodes = [];
        mutations.forEach(mu => {
          mu.addedNodes && mu.addedNodes.forEach(n => { if (n.nodeType === 1) nodes.push(n); });
        });
        if (nodes.length === 0) return;
        nodes.forEach(n => {
          // if node itself matches modal selectors
          if (modalIds.includes(n.id) || n.matches && n.matches(extraSelector)) protect(n);
          // or contains modals inside
          modalIds.forEach(id => {
            const el = n.querySelector && n.querySelector('#' + id);
            if (el) protect(el);
          });
          const found = n.querySelectorAll && n.querySelectorAll(extraSelector);
          if (found && found.length) {
            found.forEach(f => protect(f));
          }
        });
      } catch (err) { /* ignore */ }
    });
    observer.observe(document.documentElement || document.body, { childList: true, subtree: true });

  } catch (e) {
    console.error('modal-protect init error', e);
  }
})();
</script>
