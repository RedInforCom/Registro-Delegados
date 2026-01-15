// Ciudades por país
const ciudadesPorPais = {
    'Argentina': ['Buenos Aires', 'Córdoba', 'Rosario', 'Mendoza', 'La Plata', 'San Miguel de Tucumán', 'Mar del Plata', 'Salta', 'Santa Fe', 'San Juan'],
    'Bolivia': ['La Paz', 'Santa Cruz de la Sierra', 'Cochabamba', 'Sucre', 'Oruro', 'Potosí', 'Tarija', 'Trinidad', 'Cobija'],
    'Brasil': ['São Paulo', 'Río de Janeiro', 'Brasilia', 'Salvador', 'Fortaleza', 'Belo Horizonte', 'Manaos', 'Curitiba', 'Recife', 'Porto Alegre'],
    'Chile': ['Santiago', 'Valparaíso', 'Concepción', 'La Serena', 'Antofagasta', 'Temuco', 'Rancagua', 'Talca', 'Arica', 'Puerto Montt'],
    'Colombia': ['Bogotá', 'Medellín', 'Cali', 'Barranquilla', 'Cartagena', 'Cúcuta', 'Bucaramanga', 'Pereira', 'Santa Marta', 'Ibagué'],
    'Costa Rica': ['San José', 'Alajuela', 'Cartago', 'Heredia', 'Puntarenas', 'Limón', 'Liberia'],
    'Cuba': ['La Habana', 'Santiago de Cuba', 'Camagüey', 'Holguín', 'Santa Clara', 'Guantánamo', 'Bayamo', 'Las Tunas', 'Cienfuegos'],
    'Ecuador': ['Quito', 'Guayaquil', 'Cuenca', 'Santo Domingo', 'Machala', 'Manta', 'Portoviejo', 'Loja', 'Ambato', 'Riobamba'],
    'El Salvador': ['San Salvador', 'Santa Ana', 'San Miguel', 'Soyapango', 'Santa Tecla', 'Apopa', 'Delgado', 'Mejicanos'],
    'España': ['Madrid', 'Barcelona', 'Valencia', 'Sevilla', 'Zaragoza', 'Málaga', 'Murcia', 'Palma de Mallorca', 'Las Palmas', 'Bilbao'],
    'Estados Unidos': ['Nueva York', 'Los Ángeles', 'Chicago', 'Houston', 'Phoenix', 'Filadelfia', 'San Antonio', 'San Diego', 'Dallas', 'Miami'],
    'Guatemala': ['Ciudad de Guatemala', 'Mixco', 'Villa Nueva', 'Quetzaltenango', 'Escuintla', 'Chinautla', 'Cobán', 'Huehuetenango'],
    'Honduras': ['Tegucigalpa', 'San Pedro Sula', 'Choloma', 'La Ceiba', 'El Progreso', 'Choluteca', 'Comayagua', 'Villanueva'],
    'México': ['Ciudad de México', 'Guadalajara', 'Monterrey', 'Puebla', 'Tijuana', 'León', 'Juárez', 'Zapopan', 'Mérida', 'Cancún'],
    'Nicaragua': ['Managua', 'León', 'Masaya', 'Matagalpa', 'Chinandega', 'Granada', 'Estelí', 'Tipitapa', 'Puerto Cabezas'],
    'Panamá': ['Ciudad de Panamá', 'San Miguelito', 'Tocumen', 'David', 'Arraiján', 'Colón', 'Las Cumbres', 'La Chorrera'],
    'Paraguay': ['Asunción', 'Ciudad del Este', 'San Lorenzo', 'Luque', 'Capiatá', 'Fernando de la Mora', 'Lambaré', 'Encarnación'],
    'Perú': ['Lima', 'Arequipa', 'Trujillo', 'Chiclayo', 'Piura', 'Iquitos', 'Cusco', 'Huancayo', 'Tacna', 'Ica'],
    'Puerto Rico': ['San Juan', 'Bayamón', 'Carolina', 'Ponce', 'Caguas', 'Guaynabo', 'Arecibo', 'Mayagüez', 'Trujillo Alto'],
    'República Dominicana': ['Santo Domingo', 'Santiago de los Caballeros', 'La Romana', 'San Pedro de Macorís', 'San Cristóbal', 'Puerto Plata', 'La Vega'],
    'Uruguay': ['Montevideo', 'Salto', 'Ciudad de la Costa', 'Paysandú', 'Las Piedras', 'Rivera', 'Maldonado', 'Tacuarembó'],
    'Venezuela': ['Caracas', 'Maracaibo', 'Valencia', 'Barquisimeto', 'Maracay', 'Ciudad Guayana', 'Barcelona', 'Maturín', 'San Cristóbal']
};

// Prefijos telefónicos
const prefijosPais = {
    'Argentina': '+54',
    'Bolivia': '+591',
    'Brasil': '+55',
    'Chile': '+56',
    'Colombia': '+57',
    'Costa Rica': '+506',
    'Cuba': '+53',
    'Ecuador': '+593',
    'El Salvador': '+503',
    'España': '+34',
    'Estados Unidos': '+1',
    'Guatemala': '+502',
    'Honduras': '+504',
    'México': '+52',
    'Nicaragua': '+505',
    'Panamá': '+507',
    'Paraguay': '+595',
    'Perú': '+51',
    'Puerto Rico': '+1',
    'República Dominicana': '+1',
    'Uruguay': '+598',
    'Venezuela': '+58'
};

// Cargar ciudades según el país
function cargarCiudades(pais) {
    const selectCiudad = document.getElementById('ciudad');
    const prefijo = document.getElementById('prefijo');
    
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

// Capitalizar nombre correctamente
function capitalizarNombre(texto) {
    const palabrasMinusculas = ['de', 'del', 'la', 'las', 'los', 'el', 'y', 'e', 'o', 'u'];
    return texto.toLowerCase().split(' ').map((palabra, index) => {
        if (index === 0 || !palabrasMinusculas.includes(palabra)) {
            return palabra.charAt(0).toUpperCase() + palabra.slice(1);
        }
        return palabra;
    }).join(' ');
}

// Validación en tiempo real para nombres
document.addEventListener('DOMContentLoaded', function() {
    const nombreInput = document.getElementById('nombre');
    const apellidosInput = document.getElementById('apellidos');
    const centroInput = document.getElementById('centro_estudios');
    const telefonoInput = document.getElementById('telefono');

    if (nombreInput) {
        nombreInput.addEventListener('input', function() {
            this.value = capitalizarNombre(this.value);
        });
    }

    if (apellidosInput) {
        apellidosInput.addEventListener('input', function() {
            this.value = capitalizarNombre(this.value);
        });
    }

    if (centroInput) {
        centroInput.addEventListener('input', function() {
            this.value = capitalizarNombre(this.value);
        });
    }

    if (telefonoInput) {
        telefonoInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
    }
});

// Mostrar mensaje de éxito
function mostrarMensajeExito(titulo, mensaje) {
    alert(titulo + '\n\n' + mensaje);
}