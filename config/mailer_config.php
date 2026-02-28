<?php
// config/mailer_config.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
    $mail->Host       = 'sandbox.smtp.mailtrap.io'; // Host de Mailtrap
    $mail->SMTPAuth   = true;
    $mail->Username   = 'e7c11173d25454';      // Tu usuario de Mailtrap
    $mail->Password   = 'TU_PASSWORD_DE_MAILTRAP';      // <<== PEGA AQUÍ TU CONTRASEÑA REAL
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 2525;                   // Puerto de Mailtrap

    // Codificación
    $mail->CharSet = 'UTF-8';

    return $mail;
}