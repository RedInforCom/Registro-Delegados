<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'zqgikadc_admin');
define('DB_PASS', 'aBjar1BKI4sW');
define('DB_NAME', 'zqgikadc_delegados');

// Crear conexión
function getConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8mb4");
        return $conn;
        
    } catch (Exception $e) {
        die("Error al conectar con la base de datos: " . $e->getMessage());
    }
}

// Función para sanitizar datos
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Función para capitalizar correctamente (sin enlaces)
function capitalizarNombre($texto) {
    $palabras_minusculas = ['de', 'del', 'la', 'las', 'los', 'el', 'y', 'e', 'o', 'u'];
    $palabras = explode(' ', mb_strtolower($texto, 'UTF-8'));
    
    foreach ($palabras as $key => &$palabra) {
        if ($key === 0 || !in_array($palabra, $palabras_minusculas)) {
            $palabra = mb_convert_case($palabra, MB_CASE_TITLE, 'UTF-8');
        }
    }
    
    return implode(' ', $palabras);
}

// Prefijos telefónicos por país
function getPrefijoPais($pais) {
    $prefijos = [
        'Argentina' => '+54',
        'Bolivia' => '+591',
        'Brasil' => '+55',
        'Chile' => '+56',
        'Colombia' => '+57',
        'Costa Rica' => '+506',
        'Cuba' => '+53',
        'Ecuador' => '+593',
        'El Salvador' => '+503',
        'España' => '+34',
        'Estados Unidos' => '+1',
        'Guatemala' => '+502',
        'Honduras' => '+504',
        'México' => '+52',
        'Nicaragua' => '+505',
        'Panamá' => '+507',
        'Paraguay' => '+595',
        'Perú' => '+51',
        'Puerto Rico' => '+1',
        'República Dominicana' => '+1',
        'Uruguay' => '+598',
        'Venezuela' => '+58'
    ];
    
    return $prefijos[$pais] ?? '';
}
?>