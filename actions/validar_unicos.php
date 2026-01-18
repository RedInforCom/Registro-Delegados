<?php
// actions/validar_unicos.php
// Comprueba existencia de teléfono o usuario en la base de datos.
// Devuelve JSON:
// { success: true, exists: false }  // no duplicado
// { success: true, exists: true, field: "telefono", message: "Teléfono ya registrado" } // duplicado
// { success: false, error: true, message: "..." } // error

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../config/database.php';

$response = ['success' => false];

try {
    // Asegurar que el usuario esté autenticado (opcional, recomendable)
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401);
        $response['error'] = true;
        $response['message'] = 'No autorizado.';
        echo json_encode($response);
        exit;
    }

    $conn = getConnection();
    if (!$conn) {
        throw new Exception('No se pudo conectar a la base de datos.');
    }

    // Solo aceptamos POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        $response['error'] = true;
        $response['message'] = 'Método no permitido.';
        echo json_encode($response);
        exit;
    }

    // Normalizar inputs
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    $usuario  = isset($_POST['usuario'])  ? trim($_POST['usuario'])  : '';

    // Si no hay parámetros, respuesta vacía
    if ($telefono === '' && $usuario === '') {
        $response['success'] = true;
        $response['exists'] = false;
        echo json_encode($response);
        exit;
    }

    // Comprobar teléfono (si se envía)
    if ($telefono !== '') {
        // Opcional: limpiar texto (eliminar espacios, +, guiones)
        $telefono_clean = preg_replace('/\s+/', '', $telefono);
        $telefono_clean = str_replace(['+', '-', '.', '(', ')'], '', $telefono_clean);

        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(telefono,'+',''),'-',''),'.',''), '(',''), ')','') = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $telefono_clean);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $response['success'] = true;
                $response['exists'] = true;
                $response['field'] = 'telefono';
                $response['message'] = 'El teléfono ya está registrado.';
                echo json_encode($response);
                $stmt->close();
                exit;
            }
            $stmt->close();
        } else {
            throw new Exception('Error en la consulta de teléfono.');
        }
    }

    // Comprobar usuario (si se envía)
    if ($usuario !== '') {
        $usuario_clean = $usuario;
        $stmt = $conn->prepare("SELECT id FROM usuarios_sistema WHERE usuario = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $usuario_clean);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $response['success'] = true;
                $response['exists'] = true;
                $response['field'] = 'usuario';
                $response['message'] = 'El nombre de usuario ya está en uso.';
                echo json_encode($response);
                $stmt->close();
                exit;
            }
            $stmt->close();
        } else {
            throw new Exception('Error en la consulta de usuario.');
        }
    }

    // Si llegamos aquí, no hay duplicados
    $response['success'] = true;
    $response['exists'] = false;
    echo json_encode($response);
    exit;

} catch (Exception $ex) {
    http_response_code(500);
    $response['success'] = false;
    $response['error'] = true;
    $response['message'] = $ex->getMessage();
    echo json_encode($response);
    exit;
}