<header class="bg-white shadow-lg h-16 flex items-center justify-between px-6 fixed top-0 right-0 left-64 z-30">
    <h1 class="text-xl font-bold text-gray-800">
        <i class="fas fa-graduation-cap text-purple-600 mr-2"></i>
        Escuela Internacional de Psicología - Delegados
    </h1>
    
    <div class="flex items-center gap-4">
        <span class="text-gray-600">
            <i class="fas fa-user-circle mr-2"></i>
            <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
            <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full ml-2">
                <?php echo ucfirst($_SESSION['usuario_tipo']); ?>
            </span>
        </span>
        
        <?php if ($_SESSION['usuario_tipo'] != 'delegado'): ?>
        <a href="actions/editar_perfil.php" class="text-blue-600 hover:text-blue-800" title="Editar perfil">
            <i class="fas fa-user-edit text-xl"></i>
        </a>
        <?php endif; ?>
        
        <a href="auth/logout.php" class="text-red-600 hover:text-red-800" title="Cerrar sesión">
            <i class="fas fa-sign-out-alt text-xl"></i>
        </a>
    </div>
</header>