<?php
/**
 * Chequea si el usuario tiene una sesión activa
 */
function isLoggedIn() {
    // Verificamos si existe la variable de sesión que guardás al loguearte
    // Normalmente es 'user_id', 'admin' o 'usuario'
    return isset($_SESSION['usuario_id']);
}

function logout() {
    session_unset();
    session_destroy();
}

/**
 * Obtiene un valor de configuración de la base de datos.
 * Si no existe o falla, devuelve un valor por defecto.
 */
function get_config($key, $default = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT valor FROM configuracion WHERE clave = ?");
        $stmt->execute([$key]);
        return $stmt->fetchColumn() ?: $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Genera un token CSRF y lo guarda en la sesión si no existe.
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida el token CSRF recibido contra el guardado en sesión.
 */
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirecciona a una URL y detiene la ejecución.
 */
function redirect($url) {
    if (!headers_sent()) {
        header("Location: $url");
    } else {
        echo "<script>window.location.href='$url';</script>";
    }
    exit;
}

/**
 * Formatea un número como moneda.
 */
function format_currency($amount) {
    return '$' . number_format((float)$amount, 2);
}

/**
 * Valida un número de teléfono argentino.
 */
function validate_argentina_phone($phone) {
    $tel_clean = preg_replace('/[^0-9]/', '', $phone);
    // Normalizar quitando 0 inicial si existe
    if (substr($tel_clean, 0, 1) === '0') {
        $tel_clean = substr($tel_clean, 1);
    }
    $len = strlen($tel_clean);
    // Validar: 10 (Area+Num), 12 (54+Area+Num), 13 (549+Area+Num)
    return ($len === 10 || ($len === 12 && strpos($tel_clean, '54') === 0) || ($len === 13 && strpos($tel_clean, '549') === 0));
}

/**
 * Maneja la subida de imágenes de forma segura.
 */
function upload_image($file_input, $target_dir, $allowed_types = ['jpg', 'jpeg', 'png', 'webp', 'svg']) {
    if (!isset($file_input) || $file_input['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'No se subió ningún archivo o hubo un error.'];
    }

    $file_ext = strtolower(pathinfo($file_input['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed_types)) {
        return ['success' => false, 'error' => 'Formato no permitido. Permitidos: ' . implode(', ', $allowed_types)];
    }

    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            return ['success' => false, 'error' => 'No se pudo crear el directorio de destino.'];
        }
    }

    // Generar nombre único para evitar colisiones y problemas de seguridad
    $new_filename = uniqid('img_', true) . '.' . $file_ext;
    $target_file = rtrim($target_dir, '/') . '/' . $new_filename;

    if (move_uploaded_file($file_input['tmp_name'], $target_file)) {
        return ['success' => true, 'path' => $target_file];
    }

    return ['success' => false, 'error' => 'Error al mover el archivo subido.'];
}

/**
 * Valida el formato de un email y verifica si ya existe en la base de datos.
 *
 * @param string $email El correo electrónico a validar.
 * @param PDO $pdo La instancia de conexión a la base de datos.
 * @return array Retorna un array donde el primer valor es booleano (éxito) y el segundo es un mensaje.
 */
function validarEmail($email, $pdo) {
    // 1. Normalización: Eliminar espacios y convertir a minúsculas
    $email = strtolower(trim($email));

    // 2. Validación de Formato
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [false, 'Formato incorrecto'];
    }

    // 3. Validación de Duplicados (Prepared Statement)
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        return [false, 'El email ya está registrado'];
    }

    return [true, 'Email válido'];
}