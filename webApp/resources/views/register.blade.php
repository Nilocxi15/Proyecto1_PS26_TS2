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

        {{-- Mensajes de éxito --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- Mensajes de error general --}}
        @if (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        {{-- Resumen de errores de validación --}}
        @if ($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}">
            @csrf

            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" 
                       required placeholder="Tu nombre" class="@error('nombre') input-error @enderror">
                @error('nombre')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" 
                       required placeholder="tu@email.com" class="@error('email') input-error @enderror">
                @error('email')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="telefono">Teléfono:</label>
                <input type="tel" id="telefono" name="telefono" value="{{ old('telefono') }}" 
                       required placeholder="12345678" class="@error('telefono') input-error @enderror">
                @error('telefono')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Contraseña:</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" 
                           required placeholder="Ingresa tu contraseña" class="@error('password') input-error @enderror">
                    <button type="button" id="togglePassword" class="toggle-btn">🙈</button>
                </div>
                @error('password')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirmar Contraseña:</label>
                <div class="password-container">
                    <input type="password" id="password_confirmation" name="password_confirmation" 
                           required placeholder="Confirma tu contraseña" class="@error('password_confirmation') input-error @enderror">
                    <button type="button" id="toggleConfirmPassword" class="toggle-btn">🙈</button>
                </div>
                @error('password_confirmation')
                    <span class="error-message">{{ $message }}</span>
                @enderror
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