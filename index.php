<?php
/**
 * =====================================================
 * INDEX - LOGIN DEL SISTEMA
 * Escuela Internacional de Psicología
 * Sistema de Delegados
 * =====================================================
 */

session_start();

// Si ya tiene sesión activa, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit();
}

require_once 'config/database.php';

// Obtener configuración del sistema
$registro_activo = obtenerConfiguracion('registro_publico_activo');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Delegados | Escuela Internacional de Psicología</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/webp" href="assets/img/favicon.webp">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="login-page">
    
    <!-- Contenedor principal -->
    <div class="login-container">
        <div class="container">
            <div class="row justify-content-center align-items-center min-vh-100">
                <div class="col-12 col-md-10 col-lg-8 col-xl-5">
                    
                    <!-- Card de Login -->
                    <div class="login-card">
                        
                        <!-- Logo y Título -->
                        <div class="login-header text-center">
                            <div class="logo-container">
                                <img src="assets/img/logo.webp" alt="Logo" class="logo-img">
                            </div>
                            <h1 class="system-title">Sistema de Delegados</h1>
                            <p class="institution-name">Escuela Internacional de Psicología</p>
                        </div>
                        
                        <!-- Formulario de Login -->
                        <div class="login-body">
                            <form id="formLogin" method="POST" novalidate>
                                
                                <!-- Usuario -->
                                <div class="mb-3">
                                    <label for="loginUsuario" class="form-label">
                                        <i class="bi bi-person-circle"></i> Usuario
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-person"></i>
                                        </span>
                                        <input 
                                            type="text" 
                                            class="form-control" 
                                            id="loginUsuario" 
                                            name="usuario"
                                            placeholder="Ingrese su usuario"
                                            autocomplete="username"
                                            required
                                        >
                                    </div>
                                    <div class="invalid-feedback"></div>
                                </div>
                                
                                <!-- Contraseña -->
                                <div class="mb-4">
                                    <label for="loginPassword" class="form-label">
                                        <i class="bi bi-lock-fill"></i> Contraseña
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-key"></i>
                                        </span>
                                        <input 
                                            type="password" 
                                            class="form-control" 
                                            id="loginPassword" 
                                            name="password"
                                            placeholder="Ingrese su contraseña"
                                            autocomplete="current-password"
                                            required
                                        >
                                        <button 
                                            class="btn btn-outline-secondary" 
                                            type="button" 
                                            id="togglePassword"
                                            title="Mostrar/Ocultar contraseña"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback"></div>
                                </div>
                                
                                <!-- Botón Ingresar -->
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg" id="btnIngresar">
                                        <i class="bi bi-box-arrow-in-right"></i> Ingresar
                                    </button>
                                </div>
                                
                            </form>
                        </div>
                        
                        <!-- Registro de Delegados -->
                        <?php if ($registro_activo == '1'): ?>
                        <div class="login-footer text-center">
                            <hr class="my-4">
                            <p class="text-muted mb-3">¿No tienes cuenta?</p>
                            <button 
                                type="button" 
                                class="btn btn-outline-primary" 
                                id="btnAbrirRegistro"
                                data-bs-toggle="modal" 
                                data-bs-target="#modalRegistroDelegado"
                            >
                                <i class="bi bi-person-plus"></i> Registro de Delegados
                            </button>
                        </div>
                        <?php endif; ?>
                        
                    </div>
                    
                    <!-- Copyright -->
                    <div class="text-center mt-4">
                        <p class="copyright-text">
                            © Escuela Internacional de Psicología <?php echo date('Y'); ?>
                        </p>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal: Registro de Delegado -->
    <?php include 'modals/modal-registro-delegado.php'; ?>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
    
    <!-- Modales Script -->
    <script src="assets/js/modales.js"></script>

    <!-- Validaciones -->
    <script src="assets/js/validaciones.js"></script>
    
    <!-- Script de Login -->
    <script>
    $(document).ready(function() {
        
        // =============================================
        // TOGGLE PASSWORD VISIBILITY
        // =============================================
        $('#togglePassword').on('click', function() {
            const passwordInput = $('#loginPassword');
            const icon = $(this).find('i');
            
            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                icon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                passwordInput.attr('type', 'password');
                icon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        });
        
        // =============================================
        // VALIDACIÓN EN TIEMPO REAL
        // =============================================
        $('#loginUsuario, #loginPassword').on('input blur', function() {
            validarCampo($(this));
        });
        
        function validarCampo($campo) {
            const valor = $campo.val().trim();
            let esValido = true;
            let mensaje = '';
            
            if (valor === '') {
                esValido = false;
                mensaje = `El campo ${$campo.attr('name') === 'usuario' ? 'Usuario' : 'Contraseña'} no debe estar vacío`;
            }
            
            if (esValido) {
                $campo.removeClass('is-invalid').addClass('is-valid');
                $campo.next('.invalid-feedback').text('');
            } else {
                $campo.removeClass('is-valid').addClass('is-invalid');
                $campo.next('.invalid-feedback').text(mensaje);
            }
            
            return esValido;
        }
        
        // =============================================
        // SUBMIT DEL FORMULARIO DE LOGIN
        // =============================================
        $('#formLogin').on('submit', function(e) {
            e.preventDefault();
            
            // Validar campos
            const usuarioValido = validarCampo($('#loginUsuario'));
            const passwordValido = validarCampo($('#loginPassword'));
            
            if (!usuarioValido || !passwordValido) {
                return;
            }
            
            // Deshabilitar botón y mostrar loading
            const $btnIngresar = $('#btnIngresar');
            $btnIngresar.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm me-2"></span>Ingresando...');
            
            // Enviar datos por AJAX
            $.ajax({
                url: 'ajax/login.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Mostrar mensaje de éxito
                        Swal.fire({
                            icon: 'success',
                            title: '¡Bienvenido!',
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: true
                        }).then(() => {
                            // Redirigir al dashboard
                            window.location.href = 'dashboard.php';
                        });
                    } else {
                        // Mostrar mensaje de error
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de acceso',
                            text: response.message,
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#FF3600'
                        });
                        
                        // Habilitar botón nuevamente
                        $btnIngresar.prop('disabled', false)
                            .html('<i class="bi bi-box-arrow-in-right"></i> Ingresar');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error del sistema',
                        text: 'Ocurrió un error al procesar la solicitud. Por favor, intente nuevamente.',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#FF3600'
                    });
                    
                    // Habilitar botón nuevamente
                    $btnIngresar.prop('disabled', false)
                        .html('<i class="bi bi-box-arrow-in-right"></i> Ingresar');
                }
            });
        });
        
        // =============================================
        // LIMPIAR FORMULARIO AL CERRAR
        // =============================================
        $('#formLogin').on('reset', function() {
            $(this).find('.form-control').removeClass('is-valid is-invalid');
            $(this).find('.invalid-feedback').text('');
        });
        
    });
    </script>
    
</body>
</html>