<?php
// public/views/recuperar-password.php

require_once __DIR__ . '/../../config/mailer_config.php';

$mensaje = '';
$error = '';

/**
 * Genera el cuerpo HTML del correo de recuperación.
 * @param string $reset_link El enlace para restablecer la contraseña.
 * @return string El cuerpo del correo en HTML.
 */
function getEmailBody(string $reset_link): string
{
    // El logo se enlaza directamente desde la URL pública.
    $logo_url = 'http://' . $_SERVER['HTTP_HOST'] . '/Catalogo/public/img/logo.png';

    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"></head>
<body style="font-family: 'Montserrat', sans-serif; background-color: #f9f9f9; color: #1A1A1A; padding: 20px; margin: 0;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                    <tr>
                        <td align="center" style="padding: 40px 20px;">
                            <img src="{$logo_url}" alt="Villa Luro Store Logo" style="width: 100px; height: auto; margin-bottom: 20px;">
                            <h1 style="font-family: 'Playfair Display', serif; font-size: 28px; color: #1A1A1A; margin: 0 0 15px 0;">Recuperación de Contraseña</h1>
                            <p style="font-size: 16px; line-height: 1.6; color: #555; margin: 0 0 30px 0;">
                                Hemos recibido una solicitud para restablecer la contraseña de su cuenta. Haga clic en el botón de abajo para continuar.
                            </p>
                            <a href="{$reset_link}" style="display: inline-block; background-color: #1A1A1A; color: #D4AF37; padding: 15px 30px; font-size: 14px; font-weight: bold; text-decoration: none; border-radius: 4px; text-transform: uppercase; letter-spacing: 1px;">
                                Restablecer Contraseña
                            </a>
                            <p style="font-size: 12px; color: #999; margin-top: 30px;">
                                Si no solicitó este cambio, puede ignorar este correo. Este enlace es válido por 1 hora.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="background-color: #1A1A1A; padding: 15px; font-size: 12px; color: #888;">
                            &copy; " . date('Y') . " Villa Luro Store. Todos los derechos reservados.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $error = "Por favor, ingrese una dirección de correo electrónico válida.";
    } else {
        // 1. Verificar si el usuario existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            try {
                // 2. Generar un token seguro
                $token = bin2hex(random_bytes(32));
                $token_hash = hash('sha256', $token);

                // 3. Guardar el token en la base de datos (o actualizarlo si ya existe)
                $stmt_token = $pdo->prepare(
                    "INSERT INTO password_resets (email, token) VALUES (?, ?)
                     ON DUPLICATE KEY UPDATE token = VALUES(token), created_at = NOW()"
                );
                $stmt_token->execute([$email, $token_hash]);

                // 4. Construir el enlace y enviar el correo con PHPMailer
                $reset_link = 'http://' . $_SERVER['HTTP_HOST'] . '/Catalogo/index.php?page=restablecer-password&token=' . $token;
                
                $mail = getMailer();
                $mail->setFrom('info@villalurostore.com', 'Villa Luro Store');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Recuperación de Contraseña - Villa Luro Store';
                $mail->Body    = getEmailBody($reset_link);
                
                $mail->send();

            } catch (PHPMailer\PHPMailer\Exception $e) {
                error_log("PHPMailer Error: {$mail->ErrorInfo}");
                // No se muestra el error al usuario por seguridad, pero se registra.
            } catch (Exception $e) {
                error_log('Error al generar token de recuperación: ' . $e->getMessage());
            }
        }
        // 5. Por seguridad, siempre mostrar un mensaje genérico para evitar la enumeración de usuarios.
        $mensaje = "Si existe una cuenta asociada a ese correo, se ha enviado un enlace para restablecer la contraseña. Revise su bandeja de entrada (y spam).";
    }
}
?>

<div class="min-h-[80vh] flex items-center justify-center bg-luxury-bone py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-lg shadow-sm">
        <div>
            <h2 class="mt-6 text-center text-3xl font-serif text-luxury-matte">
                Recuperar Contraseña
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 font-light">
                Ingresa tu dirección de email y te enviaremos un enlace para restablecer tu contraseña.
            </p>
        </div>

        <?php if ($mensaje): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 text-sm" role="alert"><?= $mensaje ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 text-sm" role="alert"><?= $error ?></div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" method="POST">
            <div class="rounded-md shadow-sm">
                <input id="email-address" name="email" type="email" autocomplete="email" required class="appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-luxury-gold focus:border-luxury-gold sm:text-sm" placeholder="Email">
            </div>

            <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold uppercase tracking-widest rounded-md text-white bg-luxury-matte hover:bg-luxury-gold hover:text-luxury-matte focus:outline-none transition-colors">
                Enviar enlace de recuperación
            </button>
        </form>

        <div class="text-sm text-center">
            <a href="index.php?page=login" class="font-medium text-luxury-gold hover:underline">Volver a Iniciar Sesión</a>
        </div>
    </div>
</div>