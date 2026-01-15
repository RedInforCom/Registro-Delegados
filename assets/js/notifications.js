// Sistema de Notificaciones con Modales Bonitos

function mostrarNotificacion(tipo, titulo, mensaje, callback = null) {
    // Remover modal anterior si existe
    const modalAnterior = document.getElementById('modalNotificacion');
    if (modalAnterior) {
        modalAnterior.remove();
    }

    // Definir colores según tipo
    const colores = {
        'exito': { bg: 'bg-green-600', icon: 'fa-check-circle', iconColor: 'text-green-600' },
        'error': { bg: 'bg-red-600', icon: 'fa-times-circle', iconColor: 'text-red-600' },
        'advertencia': { bg: 'bg-yellow-600', icon: 'fa-exclamation-triangle', iconColor: 'text-yellow-600' },
        'info': { bg: 'bg-blue-600', icon: 'fa-info-circle', iconColor: 'text-blue-600' },
        'confirmacion': { bg: 'bg-purple-600', icon: 'fa-question-circle', iconColor: 'text-purple-600' }
    };

    const config = colores[tipo] || colores['info'];

    // Crear modal
    const modal = document.createElement('div');
    modal.id = 'modalNotificacion';
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
    modal.style.animation = 'fadeIn 0.3s ease';

    modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-2xl max-w-md w-full transform" style="animation: slideIn 0.3s ease;">
            <div class="${config.bg} text-white p-6 rounded-t-lg">
                <div class="flex items-center">
                    <i class="fas ${config.icon} text-3xl mr-4"></i>
                    <h2 class="text-2xl font-bold">${titulo}</h2>
                </div>
            </div>
            
            <div class="p-6">
                <p class="text-gray-700 text-lg">${mensaje}</p>
            </div>
            
            <div class="p-6 pt-0 flex gap-3">
                ${tipo === 'confirmacion' ? `
                    <button onclick="confirmarAccion(true)" class="flex-1 ${config.bg} text-white py-3 rounded-lg font-semibold hover:opacity-90 transition duration-300">
                        <i class="fas fa-check mr-2"></i>Confirmar
                    </button>
                    <button onclick="confirmarAccion(false)" class="flex-1 bg-gray-500 text-white py-3 rounded-lg font-semibold hover:bg-gray-600 transition duration-300">
                        <i class="fas fa-times mr-2"></i>Cancelar
                    </button>
                ` : `
                    <button onclick="cerrarNotificacion()" class="flex-1 ${config.bg} text-white py-3 rounded-lg font-semibold hover:opacity-90 transition duration-300">
                        <i class="fas fa-check mr-2"></i>Aceptar
                    </button>
                `}
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Guardar callback para confirmación
    if (tipo === 'confirmacion' && callback) {
        window.callbackConfirmacion = callback;
    } else if (callback) {
        window.callbackNotificacion = callback;
    }

    // Agregar animaciones CSS si no existen
    if (!document.getElementById('notificationStyles')) {
        const style = document.createElement('style');
        style.id = 'notificationStyles';
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes slideIn {
                from { transform: scale(0.9) translateY(-20px); opacity: 0; }
                to { transform: scale(1) translateY(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }
}

function cerrarNotificacion() {
    const modal = document.getElementById('modalNotificacion');
    if (modal) {
        modal.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => {
            modal.remove();
            if (window.callbackNotificacion) {
                window.callbackNotificacion();
                window.callbackNotificacion = null;
            }
        }, 300);
    }
}

function confirmarAccion(confirmado) {
    cerrarNotificacion();
    if (window.callbackConfirmacion) {
        window.callbackConfirmacion(confirmado);
        window.callbackConfirmacion = null;
    }
}

// Funciones auxiliares específicas
function mostrarExito(mensaje, callback = null) {
    mostrarNotificacion('exito', '¡Éxito!', mensaje, callback);
}

function mostrarError(mensaje, callback = null) {
    mostrarNotificacion('error', 'Error', mensaje, callback);
}

function mostrarAdvertencia(mensaje, callback = null) {
    mostrarNotificacion('advertencia', 'Advertencia', mensaje, callback);
}

function mostrarInfo(mensaje, callback = null) {
    mostrarNotificacion('info', 'Información', mensaje, callback);
}

function mostrarConfirmacion(mensaje, callback) {
    mostrarNotificacion('confirmacion', 'Confirmación', mensaje, callback);
}

// Agregar animación de salida
const styleOut = document.createElement('style');
styleOut.textContent = `
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
`;
document.head.appendChild(styleOut);