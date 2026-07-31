<x-mail::message>
# Gracias por confiar en Modelarc

Hola **{{ $clientName }}**,

Queremos agradecerte por habernos elegido para desarrollar **{{ $projectName }}**.

Ahora que el proyecto ha culminado, tu valoración y testimonio son muy importantes para nosotros: nos ayudan a seguir mejorando y a inspirar a futuras familias y empresas.

<x-mail::button :url="$url" color="primary">
Dejar mi valoración
</x-mail::button>

El enlace es personal y solo podrá usarse una vez. Cuando envíes tu testimonio, quedará cerrado automáticamente.

Si el botón no funciona, copia y pega este enlace en tu navegador:

{{ $url }}

Con cariño,<br>
**El equipo Modelarc**
</x-mail::message>
