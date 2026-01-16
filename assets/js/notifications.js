// Sistema de Notificaciones con Modales Bonitos

function mostrarNotificacion(tipo, titulo, mensaje, callback = null) {
    // Remover modal anterior si existe
    const modalAnterior = document.getElementById('modalNotificacion');
    if (modalAnterior) {
        modalAnterior.remove();
    }

    // Definir colores según tipo
    const colores = {
        'exito': { 
            bg: 'bg-green-600', 
            bgLight: 'bg-green-50',
            icon: 'fa-check-circle', 
            iconColor: 'text-green-600',
            borderColor: 'border-green-600'
        },
        'error': { 
            bg: 'bg-red-600',
            bgLight: 'bg-red-50',
            icon: 'fa-times-circle', 
            iconColor: 'text-red-600',
            borderColor: 'border-red-600'
        },
        'advertencia': { 
            bg: 'bg-yellow-600',
            bgLight: 'bg-yellow-50',
            icon: 'fa-exclamation-triangle', 
            iconColor: 'text-yellow-600',
            borderColor: 'border-yellow-600'
        },
        'info': { 
            bg: 'bg-blue-600',
            bgLight: 'bg-blue-50',
            icon: 'fa-info-circle', 
            iconColor: 'text-blue-600',
            borderColor: 'border-blue-600'
        },
        'confirmacion': { 
            bg: 'bg-purple-600',
            bgLight: 'bg-purple-50',
            icon: 'fa-question-circle', 
            iconColor: 'text-purple-600',
            borderColor: 'border-purple-600'
        }
    };

    const config = colores[tipo] || colores['info'];

    // Crear modal
    const modal = document.createElement('div');
    modal.id = 'modalNotificacion';
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
    modal.style.animation = 'fadeIn 0.3s ease';

    modal.innerHTML = `
        <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full transform border-4 ${config.borderColor}" style="animation: slideIn 0.3s ease;">
            <div class="${config.bg} text-white p-4 rounded-t-lg">
                <div class="flex items-center">
                    <div class="bg-white bg-opacity-20 p-2 rounded-full mr-3">
                        <i class="fas ${config.icon} text-xl"></i>
                    </div>
                    <h2 class="text-lg font-bold">${titulo}</h2>
                </div>
            </div>
            
            <div class="p-4 ${config.bgLight}">
                <p class="text-gray-800 text-sm leading-relaxed">${mensaje}</p>
            </div>
            
            <div class="p-4 bg-gray-50 rounded-b-lg">
                <div class="flex gap-2">
                    ${tipo === 'confirmacion' ? `
                        <button onclick="confirmarAccion(true)" class="flex-1 ${config.bg} text-white py-2 px-4 rounded-lg font-bold text-sm hover:opacity-90 transition duration-300">
                            <i class="fas fa-check mr-1"></i>Sí
                        </button>
                        <button onclick="confirmarAccion(false)" class="flex-1 bg-gray-600 text-white py-2 px-4 rounded-lg font-bold text-sm hover:bg-gray-700 transition duration-300">
                            <i class="fas fa-times mr-1"></i>No
                        </button>
                    ` : `
                        <button onclick="cerrarNotificacion()" class="flex-1 ${config.bg} text-white py-2 px-4 rounded-lg font-bold text-sm hover:opacity-90 transition duration-300">
                            <i class="fas fa-check mr-1"></i>OK
                        </button>
                    `}
                </div>
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
                from { 
                    transform: scale(0.8) translateY(-30px); 
                    opacity: 0; 
                }
                to { 
                    transform: scale(1) translateY(0); 
                    opacity: 1; 
                }
            }
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
            @keyframes slideOut {
                from { 
                    transform: scale(1) translateY(0); 
                    opacity: 1; 
                }
                to { 
                    transform: scale(0.8) translateY(-30px); 
                    opacity: 0; 
                }
            }
        `;
        document.head.appendChild(style);
    }

    // Cerrar con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarNotificacion();
        }
    });
}

function cerrarNotificacion() {
    const modal = document.getElementById('modalNotificacion');
    if (modal) {
        modal.style.animation = 'fadeOut 0.3s ease';
        const contenido = modal.querySelector('div.transform');
        if (contenido) {
            contenido.style.animation = 'slideOut 0.3s ease';
        }
        
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
    setTimeout(() => {
        if (window.callbackConfirmacion) {
            window.callbackConfirmacion(confirmado);
            window.callbackConfirmacion = null;
        }
    }, 350);
}

// Funciones auxiliares específicas
function mostrarExito(mensaje, callback = null) {
    mostrarNotificacion('exito', '¡Éxito!', mensaje, callback);
}

function mostrarError(mensaje, callback = null) {
    mostrarNotificacion('error', '¡Atención!', mensaje, callback);
}

function mostrarAdvertencia(mensaje, callback = null) {
    mostrarNotificacion('advertencia', '⚠️ Advertencia', mensaje, callback);
}

function mostrarInfo(mensaje, callback = null) {
    mostrarNotificacion('info', 'ℹ️ Información', mensaje, callback);
}

function mostrarConfirmacion(mensaje, callback) {
    mostrarNotificacion('confirmacion', '❓ Confirmación Requerida', mensaje, callback);
}