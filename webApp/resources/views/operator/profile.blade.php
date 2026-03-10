<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('icono-reciclaje.png') }}">
    <link rel="stylesheet" href="{{ asset('css/operator/profile.css') }}">
    <title>Mi Perfil</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>

<body>
    <div class="navbar-container">
        <nav class="navbar bg-body-tertiary fixed-top">
            <div class="container-fluid">
                <a class="navbar-brand" href="#"><img src="{{ asset('icono-reciclaje.png') }}" alt="Logo" width="30"
                        height="24" class="d-inline-block align-text-top"> Mi Perfil</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar"
                    aria-labelledby="offcanvasNavbarLabel">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menú</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home-operator') }}"><i class="bi bi-house"></i>
                                    Inicio</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('operator.containers') }}"><i
                                        class="bi bi-trash"></i>
                                    Gestión de contenedores</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="{{ route('profile-operator') }}"><i
                                        class="bi bi-person-circle"></i> Mi perfil</a>
                            </li>
                            <li class="nav-item">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                                        <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    <div class="body-content">
        <div class="container">
            <!-- Sección de Perfil -->
            <div class="profile-section">
                <div class="row">
                    <!-- Foto de Perfil -->
                    <div class="col-md-4 text-center mb-4">
                        <div class="profile-photo-container">
                            <img id="profileImage" src="{{ asset('images/default-avatar.png') }}" alt="Foto de Perfil"
                                class="profile-photo">
                            <button type="button" class="btn btn-sm btn-primary btn-change-photo" data-bs-toggle="modal"
                                data-bs-target="#changePhotoModal">
                                <i class="bi bi-camera"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Datos del Usuario -->
                    <div class="col-md-8">
                        <div class="user-data-section">
                            <h2 class="mb-4"><i class="bi bi-person-circle"></i> Mi Perfil</h2>

                            <form id="userProfileForm" class="needs-validation" novalidate>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="firstName" class="form-label">
                                            <i class="bi bi-person"></i> Nombre
                                        </label>
                                        <input type="text" class="form-control" id="firstName" value="Juan" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">
                                            <i class="bi bi-envelope"></i> Correo Electrónico
                                        </label>
                                        <input type="email" class="form-control" id="email" value="juan.perez@email.com"
                                            disabled>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">
                                            <i class="bi bi-telephone"></i> Teléfono
                                        </label>
                                        <input type="text" class="form-control" id="phone" value="+1 234 567 890"
                                            disabled>
                                    </div>
                                </div>


                        </div>

                        <div class="button-group">
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                                data-bs-target="#changePasswordModal">
                                <i class="bi bi-key"></i> Cambiar Contraseña
                            </button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Modal: Cambiar Foto de Perfil -->
    <div class="modal fade" id="changePhotoModal" tabindex="-1" aria-labelledby="changePhotoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePhotoLabel">
                        <i class="bi bi-camera"></i> Cambiar Foto de Perfil
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="changePhotoForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="photoInput" class="form-label">Seleccionar nueva foto</label>
                            <input type="file" class="form-control" id="photoInput" accept="image/*" required>
                            <small class="text-muted">Formatos permitidos: JPG, PNG (Máximo 5MB)</small>
                        </div>
                        <div id="photoPreview" class="text-center mb-3">
                            <img id="previewImage" src="" alt="Vista previa" style="max-width: 200px; display: none;"
                                class="img-thumbnail">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Guardar Foto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Cambiar Contraseña -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordLabel">
                        <i class="bi bi-key"></i> Cambiar Contraseña
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="changePasswordForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">Contraseña Actual</label>
                            <input type="password" class="form-control" id="currentPassword" required>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="newPassword" required>
                            <small class="text-muted d-block mt-2">La contraseña debe tener al menos 8 caracteres,
                                incluir mayúsculas, minúsculas y números.</small>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirmar Nueva Contraseña</label>
                            <input type="password" class="form-control" id="confirmPassword" required>
                        </div>
                        <div id="passwordMessage" class="alert" role="alert" style="display: none;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Cambiar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>