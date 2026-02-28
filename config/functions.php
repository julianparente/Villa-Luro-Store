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