<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('icono-reciclaje.png') }}">
    <link rel="stylesheet" href="{{ asset('css/errors/404.css') }}">
    <title>Página no encontrada - 404</title>
</head>

<body>
    <main class="error-wrapper">
        <section class="error-card">
            <div class="error-logo">
                <img src="{{ asset('icono-reciclaje.png') }}" alt="Logo del sistema">
            </div>

            <p class="error-code">404</p>
            <h1>Página no encontrada</h1>
            <p class="error-message">La página que estás buscando no existe, fue movida o la dirección ingresada no es válida.</p>

            <a href="{{ route('login') }}" class="btn-back">Volver al inicio</a>
        </section>
    </main>

</body>

</html>