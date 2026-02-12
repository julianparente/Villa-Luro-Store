<?php
// config/mailer_config.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- Carga de PHPMailer ---
$composer_autoload = __DIR__ . '/../vendor/autoload.php';

if (file_exists($composer_autoload)) {
    // Método 1 (Recomendado): Carga a través de Composer
    require_once $composer_autoload;
} else {
    // Método 2: Carga manual (si no usas Composer)
    // Asegúrate de que los archivos de PHPMailer estén en 'vendor/phpmailer/src/'
    $manual_path = __DIR__ . '/../vendor/phpmailer/src/';
    if (!file_exists($manual_path . 'PHPMailer.php')) {
        die("<h1>Error Crítico: Librería PHPMailer no encontrada.</h1><p>Por favor, instale las dependencias ejecutando <code>composer install</code> o <code>composer require phpmailer/phpmailer</code> en la raíz del proyecto, o descargue PHPMailer manualmente y colóquelo en la carpeta <code>/vendor/phpmailer/</code>.</p>");
    }
    require_once $manual_path . 'Exception.php';
    require_once $manual_path . 'PHPMailer.php';
    require_once $manual_path . 'SMTP.php';
}

function getMailer(): PHPMailer
{
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