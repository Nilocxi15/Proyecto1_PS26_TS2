<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('icono-reciclaje.png') }}">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
    <title>Registrarse</title>
</head>

<body>
    <div class="register-container">
        <h1>Registro de Usuario</h1>

        <form method="POST" action="#">
            @csrf

            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" required placeholder="tu@email.com">
            </div>

            <div class="form-group">
                <label for="name">Nombre:</label>
                <input type="text" id="name" name="name" required placeholder="Tu nombre">
            </div>

            <div class="form-group">
                <label for="password">Contraseña:</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required placeholder="Ingresa tu contraseña">
                    <button type="button" id="togglePassword" class="toggle-btn">🙈</button>
                </div>

            </div>

            <div class="form-group">
                <label for="confirm-password">Confirmar Contraseña:</label>
                <div class="password-container">
                    <input type="password" id="confirm-password" name="confirm-password" required
                        placeholder="Confirma tu contraseña">
                    <button type="button" id="toggleConfirmPassword" class="toggle-btn">🙈</button>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn-register">Registrarse</button>
            </div>

            <div class="login-section">
                <p>¿Ya tienes una cuenta?</p>
                <a href="{{ route('login') }}" class="btn-login">Iniciar Sesión</a>
            </div>
        </form>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
</body>

</html>