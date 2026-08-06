<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tu valoración — Modelarc</title>
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
                                Gracias por confiar en nosotros
                            </h1>
                            <p style="margin:10px 0 0;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;color:rgba(247,244,240,0.72);">
                                Hola {{ $clientName }}, queremos agradecerte por habernos elegido para desarrollar
                                <strong style="color:#f7f4f0;">{{ $projectName }}</strong>.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 32px;background:#171717;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#1f1f1f;border:1px solid rgba(196,164,124,0.22);border-radius:2px;">
                                <tr>
                                    <td style="padding:22px 24px;">
                                        <p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.7;color:rgba(247,244,240,0.88);">
                                            Ahora que el proyecto ha culminado, tu valoración y testimonio son muy importantes para nosotros:
                                            nos ayudan a seguir mejorando y a inspirar a futuras familias y empresas.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top:28px;">
                                <tr>
                                    <td style="background:#c4a47c;border-radius:2px;">
                                        <a href="{{ $url }}" style="display:inline-block;padding:14px 22px;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:#111111;text-decoration:none;">
                                            Dejar mi valoración
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 32px 26px;border-top:1px solid rgba(196,164,124,0.2);">
                            <p style="margin:0;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.6;color:rgba(247,244,240,0.45);">
                                El enlace es personal y solo podrá usarse una vez.
                                Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
                                <a href="{{ $url }}" style="color:#c4a47c;word-break:break-all;text-decoration:none;">{{ $url }}</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
