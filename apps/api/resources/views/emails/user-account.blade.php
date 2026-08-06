<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $kind === 'password_reset' ? 'Restablecer contraseña' : 'Activar cuenta' }}</title>
</head>
<body style="margin:0;padding:0;background:#f0ebe4;font-family:Georgia,'Times New Roman',serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f0ebe4;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;background:#111111;border-radius:2px;overflow:hidden;">
                    <tr>
                        <td style="padding:28px 32px 20px;border-bottom:1px solid rgba(196,164,124,0.35);">
                            <p style="margin:0 0 6px;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:0.22em;text-transform:uppercase;color:#c4a47c;">
                                Modelarc
                            </p>
                            <h1 style="margin:0;font-size:28px;line-height:1.25;font-weight:400;color:#f7f4f0;">
                                @if ($kind === 'password_reset')
                                    Restablece tu contraseña
                                @else
                                    Bienvenido al panel
                                @endif
                            </h1>
                            <p style="margin:10px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;color:rgba(247,244,240,0.72);">
                                Hola {{ $user->name }},
                                @if ($kind === 'password_reset')
                                    recibimos una solicitud para actualizar la contraseña de tu cuenta.
                                @else
                                    te invitaron a colaborar en el panel de administración de Modelarc.
                                @endif
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 32px;background:#171717;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#1f1f1f;border:1px solid rgba(196,164,124,0.22);border-radius:2px;">
                                <tr>
                                    <td style="padding:22px 24px;">
                                        <p style="margin:0 0 18px;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:#c4a47c;">
                                            Tu acceso
                                        </p>
                                        <p style="margin:0 0 4px;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:0.12em;text-transform:uppercase;color:rgba(247,244,240,0.45);">Email</p>
                                        <p style="margin:0 0 16px;font-family:Arial,Helvetica,sans-serif;font-size:16px;color:#f7f4f0;">{{ $user->email }}</p>
                                        <p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.65;color:rgba(247,244,240,0.72);">
                                            @if ($kind === 'password_reset')
                                                El enlace es de un solo uso y caduca en 7 días. Si no solicitaste este cambio, puedes ignorar este correo.
                                            @else
                                                Activa tu cuenta con el botón de abajo, elige tu contraseña y empieza a usar el panel. El enlace es de un solo uso y caduca en 7 días.
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top:28px;">
                                <tr>
                                    <td style="background:#c4a47c;border-radius:2px;">
                                        <a href="{{ $actionUrl }}" style="display:inline-block;padding:14px 22px;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:#111111;text-decoration:none;">
                                            @if ($kind === 'password_reset')
                                                Restablecer contraseña
                                            @else
                                                Activar cuenta
                                            @endif
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 32px 26px;border-top:1px solid rgba(196,164,124,0.2);">
                            <p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:rgba(247,244,240,0.45);">
                                Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
                                <a href="{{ $actionUrl }}" style="color:#c4a47c;word-break:break-all;text-decoration:none;">{{ $actionUrl }}</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
