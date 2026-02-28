<?php
/**
 * Chequea si el usuario tiene una sesión activa
 */
function isLoggedIn() {
    // Verificamos si existe la variable de sesión que guardás al loguearte
    // Normalmente es 'user_id', 'admin' o 'usuario'
    return isset($_SESSION['user_id']) || isset($_SESSION['usuario']);
}