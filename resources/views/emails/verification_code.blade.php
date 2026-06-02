<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de verificación</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden;">
        <tr>
            <td style="background: #2c3e50; color: white; padding: 24px; text-align: center;">
                <h1 style="margin: 0; font-size: 24px;">Santa Cruz Segura</h1>
                <p style="margin: 4px 0 0;">Código de verificación</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 24px; color: #333;">
                <p>Hola {{ $user->name }},</p>
                <p>Gracias por crear tu cuenta. Utiliza el siguiente código para verificar tu correo electrónico:</p>
                <p style="font-size: 24px; font-weight: bold; letter-spacing: 2px;">{{ $code }}</p>
                <p>Si no solicitaste este código, ignora este correo.</p>
                <p>Después de verificar tu cuenta podrás ingresar al sistema.</p>
                <p>Saludos,<br>Santa Cruz Segura</p>
            </td>
        </tr>
    </table>
</body>
</html>
