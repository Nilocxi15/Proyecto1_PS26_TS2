<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('icono-reciclaje.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <title>Iniciar Sesión</title>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="{{ asset(path: 'icono-reciclaje.png') }}" alt="Logo">
        </div>
        
        <h1>Sistema de Gestión de Residuos y Reciclaje Municipal</h1>
        
        <div class="login-box">
            <h2>Iniciar Sesión</h2>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error">{{ $errors->first() }}</div>
            @endif
            
            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                
                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" required placeholder="tu@email.com" value="{{ old('email') }}">
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" required placeholder="Ingresa tu contraseña">
                        <button type="button" id="togglePassword" class="toggle-btn">🙈</button>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">Iniciar Sesión</button>
            </form>
            
            <div class="signup-section">
                <p>¿No tienes cuenta?</p>
                <a href="{{ route('register') }}" class="btn-signup">Registrarse</a>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>