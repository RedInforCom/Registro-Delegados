<?php
// TEST LOGIN - Verificar y resetear contraseña admin
require_once 'config/database.php';

echo "<h2>TEST DE LOGIN</h2>";

try {
    $db = getDB();
    
    // 1. Ver admin actual
    echo "<h3>1. Usuario Administrador Actual:</h3>";
    $stmt = $db->query("SELECT id, nombre, apellidos, usuario, tipo_usuario FROM usuarios WHERE tipo_usuario = 'administrador' LIMIT 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "<pre>";
        print_r($admin);
        echo "</pre>";
        
        $usuario_admin = $admin['usuario'];
        $id_admin = $admin['id'];
        
        // 2. Resetear contraseña a "admin123"
        echo "<h3>2. Reseteando Contraseña...</h3>";
        $nueva_password = 'admin123';
        $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        $resultado = $stmt->execute([$password_hash, $id_admin]);
        
        if ($resultado) {
            echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
            echo "<strong>✅ Contraseña reseteada exitosamente</strong><br>";
            echo "Usuario: <strong>{$usuario_admin}</strong><br>";
            echo "Nueva Contraseña: <strong>{$nueva_password}</strong><br>";
            echo "</div>";
            
            // 3. Probar login
            echo "<h3>3. Probando Login...</h3>";
            $stmt = $db->prepare("SELECT id, password FROM usuarios WHERE usuario = ? AND tipo_usuario = 'administrador' AND estado = 1");
            $stmt->execute([$usuario_admin]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                $verificacion = password_verify($nueva_password, $user['password']);
                
                if ($verificacion) {
                    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
                    echo "✅ <strong>LOGIN FUNCIONA CORRECTAMENTE</strong><br>";
                    echo "Puedes ingresar con:<br>";
                    echo "Usuario: <strong>{$usuario_admin}</strong><br>";
                    echo "Contraseña: <strong>{$nueva_password}</strong>";
                    echo "</div>";
                } else {
                    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
                    echo "❌ Error: La contraseña no verifica correctamente";
                    echo "</div>";
                }
            } else {
                echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
                echo "❌ Error: No se encontró el usuario";
                echo "</div>";
            }
            
        } else {
            echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
            echo "❌ Error al actualizar la contraseña";
            echo "</div>";
        }
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
        echo "❌ NO SE ENCONTRÓ NINGÚN ADMINISTRADOR EN LA BASE DE DATOS<br>";
        echo "Necesitas crear uno manualmente con este SQL:<br>";
        echo "<pre>";
        echo "INSERT INTO usuarios (nombre, apellidos, usuario, password, tipo_usuario, pais_id, ciudad_id, telefono, centro_estudios, estado)
VALUES ('Admin', 'Sistema', 'admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'administrador', 1, 1, '+51999999999', 'Sistema', 1);";
        echo "</pre>";
        echo "</div>";
    }
    
    // 4. Ver todos los usuarios
    echo "<h3>4. Todos los Usuarios en el Sistema:</h3>";
    $stmt = $db->query("SELECT id, nombre, apellidos, usuario, tipo_usuario FROM usuarios ORDER BY id");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Usuario</th><th>Tipo</th></tr>";
    foreach ($usuarios as $u) {
        echo "<tr>";
        echo "<td>{$u['id']}</td>";
        echo "<td>{$u['nombre']} {$u['apellidos']}</td>";
        echo "<td>{$u['usuario']}</td>";
        echo "<td>{$u['tipo_usuario']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch(Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<strong>ERROR:</strong> " . $e->getMessage();
    echo "</div>";
}
?>