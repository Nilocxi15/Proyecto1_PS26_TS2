<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('icono-reciclaje.png') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/users-management.css') }}">
    <title>Gestión de Usuarios</title>
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
                <a class="navbar-brand" href="#">
                    <img src="{{ asset('icono-reciclaje.png') }}" alt="Logo" width="30" height="24"
                        class="d-inline-block align-text-top">
                    Gestión de Usuarios
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
                    aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar"
                    aria-labelledby="offcanvasNavbarLabel">
                    <div class="offcanvas-header">
                        <h5 class="offcanvas-title" id="offcanvasNavbarLabel">Menú</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                            aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home-admin') }}"><i class="bi bi-house"></i>
                                    Inicio</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="{{ route('admin.users') }}"><i
                                        class="bi bi-file-earmark-medical"></i> Gestión de Usuarios</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.system-settings') }}"><i class="bi bi-gear"></i>
                                    Configuración del Sistema</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.reports') }}"><i class="bi bi-bar-chart"></i>
                                    Reportes y Estadísticas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.user-profile') }}"><i
                                        class="bi bi-person-circle"></i> Mi perfil</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    <main class="body-content container py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h2 class="m-0">Usuarios</h2>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="bi bi-person-plus-fill"></i> Crear usuario
            </button>
        </div>

        <!-- Barra de búsqueda y filtros -->
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form class="row g-2" method="GET" action="#">
                    <div class="col-md-5">
                        <label for="search" class="form-label mb-1">Búsqueda</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="search" name="search" class="form-control"
                                placeholder="Buscar por nombre o email">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label for="filterRole" class="form-label mb-1">Rol</label>
                        <select id="filterRole" name="role" class="form-select">
                            <option value="">Todos</option>
                            <option value="admin">Administrador</option>
                            <option value="moderador">Moderador</option>
                            <option value="usuario">Usuario</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="filterStatus" class="form-label mb-1">Estado</label>
                        <select id="filterStatus" name="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        <a href="#" class="btn btn-outline-secondary w-100">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de usuarios -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($users ?? []) as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge text-bg-info text-uppercase">{{ $user->role }}</span>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox"
                                                id="status-{{ $user->id }}"
                                                {{ ($user->status ?? 'inactivo') === 'activo' ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="status-{{ $user->id }}">
                                                {{ ($user->status ?? 'inactivo') === 'activo' ? 'Activo' : 'Inactivo' }}
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <!-- Datos de ejemplo cuando no hay colección -->
                                <tr>
                                    <td>1</td>
                                    <td>Juan Pérez</td>
                                    <td>juan@email.com</td>
                                    <td><span class="badge text-bg-info text-uppercase">admin</span></td>
                                    <td>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" id="status-demo-1" checked>
                                            <label class="form-check-label small" for="status-demo-1">Activo</label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Ana López</td>
                                    <td>ana@email.com</td>
                                    <td><span class="badge text-bg-info text-uppercase">usuario</span></td>
                                    <td>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" id="status-demo-2">
                                            <label class="form-check-label small" for="status-demo-2">Inactivo</label>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal crear usuario -->
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="#">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createUserModalLabel">Crear usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="phone" name="phone" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Rol</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Seleccione un rol</option>
                                <option value="admin">Administrador</option>
                                <option value="moderador">Moderador</option>
                                <option value="usuario">Usuario</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>