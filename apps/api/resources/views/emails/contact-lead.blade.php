<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nueva solicitud de contacto</title>
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
                                Nueva solicitud de contacto
                            </h1>
                            <p style="margin:10px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;color:rgba(247,244,240,0.72);">
                                Recibiste una nueva consulta desde el sitio web.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 32px;background:#171717;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#1f1f1f;border:1px solid rgba(196,164,124,0.22);border-radius:2px;">
                                <tr>
                                    <td style="padding:22px 24px;">
                                        <p style="margin:0 0 18px;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:#c4a47c;">
                                            Datos del interesado
                                        </p>

                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="padding:0 0 14px;">
                                                    <p style="margin:0 0 4px;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:0.12em;text-transform:uppercase;color:rgba(247,244,240,0.45);">Nombre</p>
                                                    <p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:16px;color:#f7f4f0;">{{ $lead->name }}</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:0 0 14px;">
                                                    <p style="margin:0 0 4px;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:0.12em;text-transform:uppercase;color:rgba(247,244,240,0.45);">Email</p>
                                                    <p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:16px;">
                                                        <a href="mailto:{{ $lead->email }}" style="color:#c4a47c;text-decoration:none;">{{ $lead->email }}</a>
                                                    </p>
                                                </td>
                                            </tr>
                                            @if ($lead->phone)
                                            <tr>
                                                <td style="padding:0 0 14px;">
                                                    <p style="margin:0 0 4px;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:0.12em;text-transform:uppercase;color:rgba(247,244,240,0.45);">Teléfono</p>
                                                    <p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:16px;color:#f7f4f0;">{{ $lead->phone }}</p>
                                                </td>
                                            </tr>
                                            @endif
                                            @if ($lead->project_type)
                                            <tr>
                                                <td style="padding:0 0 14px;">
                                                    <p style="margin:0 0 4px;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:0.12em;text-transform:uppercase;color:rgba(247,244,240,0.45);">Servicio de interés</p>
                                                    <p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:16px;color:#f7f4f0;">{{ $lead->project_type }}</p>
                                                </td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <td style="padding:0;">
                                                    <p style="margin:0 0 4px;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:0.12em;text-transform:uppercase;color:rgba(247,244,240,0.45);">Recibido</p>
                                                    <p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:16px;color:#f7f4f0;">{{ $receivedAt ?? now()->format('d/m/Y H:i') }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            @if ($lead->message)
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:18px;background:#1f1f1f;border:1px solid rgba(196,164,124,0.22);border-radius:2px;">
                                <tr>
                                    <td style="padding:22px 24px;">
                                        <p style="margin:0 0 12px;font-family:Arial,Helvetica,sans-serif;font-size:11px;letter-spacing:0.18em;text-transform:uppercase;color:#c4a47c;">
                                            Mensaje
                                        </p>
                                        <p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.7;color:rgba(247,244,240,0.88);white-space:pre-wrap;">{{ $lead->message }}</p>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            @if (!empty($adminUrl))
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top:28px;">
                                <tr>
                                    <td style="background:#c4a47c;border-radius:2px;">
                                        <a href="{{ $adminUrl }}/solicitudes" style="display:inline-block;padding:14px 22px;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:#111111;text-decoration:none;">
                                            Ver en el panel
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 32px 26px;border-top:1px solid rgba(196,164,124,0.2);">
                            <p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:rgba(247,244,240,0.45);">
                                Puedes responder este correo directamente para contactar a {{ $lead->name }}.
                                <br>
                                Origen: {{ $lead->source ?: 'website' }} · ID solicitud #{{ $lead->id }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
