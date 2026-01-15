# Sistema de Delegados - Escuela Internacional de Psicología

## 📋 Descripción
Sistema profesional de gestión de delegados y usuarios para la Escuela Internacional de Psicología, desarrollado con PHP, MySQL, Tailwind CSS y Font Awesome.

## 🚀 Instalación en cPanel

### Paso 1: Crear la Base de Datos
1. Accede a **phpMyAdmin** desde tu cPanel
2. Selecciona la base de datos: `zqgikadc_delegados`
3. Ve a la pestaña **SQL**
4. Copia y pega todo el contenido del archivo `sql/database.sql`
5. Haz clic en **Continuar**

### Paso 2: Subir los Archivos
1. Accede al **Administrador de Archivos** de cPanel
2. Navega a la carpeta de tu subdominio: `public_html/delegados/`
3. Sube todos los archivos manteniendo la estructura de carpetas
4. Asegúrate de que los permisos sean correctos (normalmente 644 para archivos y 755 para carpetas)

### Paso 3: Agregar Logo y Favicon
1. Sube tus archivos `logo.webp` y `favicon.webp` a la carpeta `assets/images/`
2. Si no tienes estas imágenes aún, el sistema funcionará con íconos por defecto

### Paso 4: Verificar Configuración
1. Abre el archivo `config/database.php`
2. Verifica que las credenciales sean correctas:
   - **Host:** localhost
   - **Usuario:** zqgikadc_admin
   - **Contraseña:** aBjar1BKI4sW
   - **Base de datos:** zqgikadc_delegados

### Paso 5: Acceder al Sistema
1. Abre tu navegador y ve a: `https://psicologiaenvivo.com/delegados/`
2. Usa las credenciales del administrador:
   - **Usuario:** admin
   - **Contraseña:** 12345

## 📁 Estructura de Archivos

```
/delegados/
├── index.php                    # Página de login
├── dashboard.php                # Panel principal
├── config/
│   └── database.php            # Conexión a BD
├── includes/
│   ├── header.php              # Header fijo
│   ├── sidebar.php             # Sidebar fijo
│   └── footer.php              # Footer fijo
├── auth/
│   ├── login.php               # Procesar login
│   ├── logout.php              # Cerrar sesión
│   └── register.php            # Registro de delegados
├── actions/
│   ├── crear_asesor.php        # Crear asesor
│   ├── crear_delegado.php      # Crear delegado
│   ├── crear_usuario.php       # Crear usuario
│   └── eliminar_usuario.php    # Eliminar registros
├── assets/
│   ├── css/
│   │   └── style.css           # Estilos personalizados
│   ├── js/
│   │   └── main.js             # JavaScript validaciones
│   └── images/
│       ├── logo.webp           # Logo del sistema
│       └── favicon.webp        # Favicon
└── sql/
    └── database.sql            # Estructura de BD
```

## 👥 Tipos de Usuario

### Administrador
- Crear asesores, delegados y usuarios
- Ver todas las estadísticas
- Editar y eliminar cualquier registro
- Asignar usuarios a delegados

### Asesor
- Crear delegados y usuarios
- Ver estadísticas generales
- Editar registros de delegados y usuarios
- Asignar usuarios a delegados

### Delegado
- Crear usuarios
- Ver solo sus usuarios asignados
- Editar sus propios usuarios

## ✨ Características

- ✅ 100% Responsive con Tailwind CSS
- ✅ Validaciones en tiempo real
- ✅ Capitalización automática de nombres
- ✅ Prefijos telefónicos automáticos por país
- ✅ Ciudades dinámicas según país seleccionado
- ✅ Modales modernos y profesionales
- ✅ Gráficos de estadísticas con Chart.js
- ✅ Búsqueda en tiempo real
- ✅ Iconos con Font Awesome
- ✅ Sin errores de código

## 🎨 Diseño

- **Colores principales:** Púrpura (#7C3AED), Azul (#3B82F6), Verde (#22C55E)
- **Bordes redondeados:** 5px
- **Fuente:** Sistema predeterminado
- **Iconos:** Font Awesome 6.4.0

## 🔒 Seguridad

- Contraseñas encriptadas con `password_hash()`
- Protección contra inyección SQL con prepared statements
- Sanitización de datos de entrada
- Validación de sesiones en todas las páginas

## 📝 Notas Importantes

1. **Primer acceso:**
   - Usuario: admin
   - Contraseña: 12345
   - **¡CAMBIA ESTA CONTRASEÑA INMEDIATAMENTE!**

2. **Países soportados:** 22 países de Latinoamérica y España

3. **Capitalización:** El sistema capitaliza automáticamente nombres, apellidos y centros de estudios, excepto palabras de enlace (de, del, la, y, etc.)

4. **Teléfonos:** Se agregan automáticamente los prefijos según el país seleccionado

## 🐛 Solución de Problemas

### Error de conexión a la base de datos
- Verifica las credenciales en `config/database.php`
- Asegúrate de que la base de datos exista
- Verifica que el usuario tenga permisos

### Las imágenes no se muestran
- Verifica que las imágenes estén en `assets/images/`
- Verifica los permisos de la carpeta (755)
- Verifica que los archivos sean formato .webp

### Los estilos no se cargan
- Verifica tu conexión a internet (Tailwind CSS se carga desde CDN)
- Limpia la caché del navegador

## 📞 Soporte

Para cualquier problema o consulta, contacta al desarrollador del sistema.

---

**© Escuela Internacional de Psicología 2026**