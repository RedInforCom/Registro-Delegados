<?php
/**
 * =====================================================
 * DASHBOARD PRINCIPAL
 * Escuela Internacional de Psicología
 * Sistema de Delegados
 * =====================================================
 */

session_start();
require_once 'config/database.php';

// Validar que el usuario tenga sesión activa
validarSesion();

// Obtener datos de sesión
$usuario_id = $_SESSION['usuario_id'];
$nombre_completo = $_SESSION['nombre_completo'];
$tipo_usuario = $_SESSION['tipo_usuario'];
$usuario = $_SESSION['usuario'];

// Verificar que no sea un integrante
if ($tipo_usuario === 'integrante') {
    session_destroy();
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Dashboard | Sistema de Delegados</title>

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
    <link rel="stylesheet" href="assets/css/dashboard.css">

    <style>
    body {
        margin: 0;
        padding: 0;
        overflow-x: hidden;
    }
    .dashboard-wrapper {
        display: flex;
        min-height: 100vh;
    }
    .dashboard-sidebar-wrapper {
        width: 250px;
        min-height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        z-index: 1000;
        background: white;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }
    .dashboard-main-wrapper {
        margin-left: 250px;
        width: calc(100% - 250px);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }
    .dashboard-header-wrapper {
        position: sticky;
        top: 0;
        z-index: 999;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .dashboard-content-wrapper {
        flex: 1;
        padding: 20px;
    }
    .dashboard-footer-wrapper {
        margin-top: auto;
    }
    @media (max-width: 768px) {
        .dashboard-sidebar-wrapper {
            width: 100%;
            position: relative;
        }
        .dashboard-main-wrapper {
            margin-left: 0;
            width: 100%;
        }
    }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <div class="dashboard-sidebar-wrapper">
            <?php include 'includes/sidebar.php'; ?>
        </div>
        
        <!-- Main Content -->
        <div class="dashboard-main-wrapper">
            <!-- Header -->
            <div class="dashboard-header-wrapper">
                <?php include 'includes/header.php'; ?>
            </div>
            
            <!-- Content -->
            <div class="dashboard-content-wrapper">
                <!-- Título de Bienvenida -->
                <div class="welcome-section mb-4">
                    <h2 class="welcome-title">
                        <i class="bi bi-speedometer2"></i> 
                        Dashboard
                    </h2>
                    <p class="welcome-text">
                        Bienvenido, <strong><?php echo htmlspecialchars($nombre_completo); ?></strong>
                        <span class="badge bg-primary ms-2"><?php echo ucfirst($tipo_usuario); ?></span>
                    </p>
                </div>
                
                <!-- Estadísticas -->
                <div id="estadisticas-container">
                    <!-- Las estadísticas se cargan dinámicamente según el tipo de usuario -->
                </div>
                
                <!-- Tabla de Usuarios -->
                <div id="tabla-usuarios-container">
                    <!-- La tabla se carga dinámicamente según el tipo de usuario -->
                </div>
            </div>
            
            <!-- Footer -->
            <div class="dashboard-footer-wrapper">
                <?php include 'includes/footer.php'; ?>
            </div>
        </div>
    </div>
    
    <!-- Modales -->
    <?php 
    // Modales según el tipo de usuario
    if ($tipo_usuario === 'administrador') {
        include 'modals/modal-crear-asesor.php';
        include 'modals/modal-crear-delegado.php';
        include 'modals/modal-crear-integrante.php';
        include 'modals/modal-editar-usuario.php';
        include 'modals/modal-asignar-delegado.php';
        include 'modals/modal-clave-registro.php';
        include 'modals/modal-logs.php';
        include 'modals/modal-paises-ciudades.php';
        include 'modals/modal-resetear-bd.php';
        include 'modals/modal-boton-registro.php';
        include 'modals/modal-importar-excel.php';
    } elseif ($tipo_usuario === 'asesor') {
        include 'modals/modal-crear-delegado.php';
        include 'modals/modal-crear-integrante.php';
        include 'modals/modal-editar-usuario.php';
        include 'modals/modal-asignar-delegado.php';
    } elseif ($tipo_usuario === 'delegado') {
        include 'modals/modal-crear-integrante.php';
        include 'modals/modal-editar-usuario.php';
    }
    ?>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
    
    <!-- SheetJS para exportar a Excel -->
    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
    
    <!-- Validaciones -->
    <script src="assets/js/validaciones.js"></script>
    
    <!-- Dashboard Script -->
    <script src="assets/js/dashboard.js"></script>
    
    <!-- Modales Script -->
    <script src="assets/js/modales.js"></script>
    
    <!-- Script específico según tipo de usuario -->
    <script>
    // Variable global con tipo de usuario
    const TIPO_USUARIO = '<?php echo $tipo_usuario; ?>';
    const USUARIO_ID = <?php echo $usuario_id; ?>;
    </script>
    
</body>
</html>