<?php
// config/mailer_config.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Asegurar que las constantes estén definidas (por si se accede directamente)
if (!defined('SMTP_HOST')) {
    require_once __DIR__ . '/db.php';
}

// --- Carga de PHPMailer ---
$composer_autoload = __DIR__ . '/../vendor/autoload.php';

// 1. Intentar cargar vía Composer
if (file_exists($composer_autoload)) {
    require_once $composer_autoload;
}

// 2. Si la clase no se cargó (Composer falló o no se usó), intentar carga manual
if (!class_exists(PHPMailer::class)) {
    $manual_path = __DIR__ . '/../vendor/phpmailer/src/';
    // Verificar si existe el archivo antes de incluir
    if (file_exists($manual_path . 'PHPMailer.php')) {
        require_once $manual_path . 'Exception.php';
        require_once $manual_path . 'PHPMailer.php';
        require_once $manual_path . 'SMTP.php';
    }
}

function getMailer(): PHPMailer
{
    if (!class_exists(PHPMailer::class)) {
        throw new \Exception("La librería PHPMailer no se encuentra instalada.");
    }
    $mail = new PHPMailer(true);

    // Configuración del servidor SMTP (usando Mailtrap como ejemplo)
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port       = SMTP_PORT;

    // Codificación
    $mail->CharSet = 'UTF-8';

    return $mail;
}