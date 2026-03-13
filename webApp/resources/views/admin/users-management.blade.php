<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('icono-reciclaje.png') }}">
    <link rel="stylesheet" href="{{ asset('css/admin/users-management.css') }}">
    <title>Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
                                <a class="nav-link" href="{{ route('home-admin') }}"><i class="bi bi-house"></i> Inicio</a>
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
                                <a class="nav-link" href="{{ route('admin.user-profile') }}"><i class="bi bi-person-circle"></i>
                                    Mi perfil</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider my-1">
                            </li>
                            <li class="ps-2 py-0"><small class="text-muted fw-semibold">Vistas de otros roles</small></li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home-coordinator') }}"><i class="bi bi-map-fill"></i>
                                    Coordinador de Rutas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('operator.containers') }}"><i class="bi bi-recycle"></i>
                                    Operador Punto Verde</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home-auditor') }}"><i class="bi bi-clipboard-data"></i>
                                    Auditor</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home-citizen') }}"><i class="bi bi-people-fill"></i>
                                    Ciudadano</a>
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

    <main class="body-content container py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h2 class="m-0">Usuarios</h2>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="bi bi-person-plus-fill"></i> Crear usuario
            </button>
        </div>

        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Se encontraron errores:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <form class="row g-2" method="GET" action="{{ route('admin.users') }}">
                    <div class="col-md-5">
                        <label for="search" class="form-label mb-1">Búsqueda</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="search" name="search" class="form-control"
                                placeholder="Buscar por nombre, email o teléfono" value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label for="filterRole" class="form-label mb-1">Rol</label>
                        <select id="filterRole" name="role" class="form-select">
                            <option value="">Todos</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id_role }}"
                                    {{ (int) ($filters['role'] ?? 0) === (int) $role->id_role ? 'selected' : '' }}>
                                    {{ ucfirst($role->nombre) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label for="filterStatus" class="form-label mb-1">Estado</label>
                        <select id="filterStatus" name="status" class="form-select">
                            <option value="" {{ ($filters['status'] ?? '') === '' ? 'selected' : '' }}>Todos</option>
                            <option value="activo" {{ ($filters['status'] ?? '') === 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo" {{ ($filters['status'] ?? '') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary w-100">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->id_usuario }}</td>
                                    <td>{{ $user->nombre }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->telefono }}</td>
                                    <td>
                                        <span class="badge text-bg-info text-uppercase">{{ $user->rol->nombre ?? 'Sin rol' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $user->estado === 'activo' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                            {{ ucfirst($user->estado) }}
                                        </span>
                                        <form method="POST" action="{{ route('admin.users.update-status', $user->id_usuario) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="estado" value="{{ $user->estado === 'activo' ? 'inactivo' : 'activo' }}">
                                            <button type="submit" class="btn btn-link btn-sm p-0 ms-2">
                                                {{ $user->estado === 'activo' ? 'Inactivar' : 'Activar' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#editUserModal-{{ $user->id_usuario }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user->id_usuario) }}" class="d-inline"
                                            onsubmit="return confirm('¿Deseas eliminar este usuario?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No hay usuarios registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                {{ $users->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </main>

    @foreach($users as $user)
        <div class="modal fade" id="editUserModal-{{ $user->id_usuario }}" tabindex="-1"
            aria-labelledby="editUserModalLabel-{{ $user->id_usuario }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.users.update', $user->id_usuario) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title" id="editUserModalLabel-{{ $user->id_usuario }}">
                                Editar usuario #{{ $user->id_usuario }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="nombre-{{ $user->id_usuario }}" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre-{{ $user->id_usuario }}" name="nombre"
                                    value="{{ $user->nombre }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="email-{{ $user->id_usuario }}" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email-{{ $user->id_usuario }}" name="email"
                                    value="{{ $user->email }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="telefono-{{ $user->id_usuario }}" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono-{{ $user->id_usuario }}" name="telefono"
                                    value="{{ $user->telefono }}" required>
                            </div>

                            <div class="row g-2">
                                <div class="col-md-6 mb-3">
                                    <label for="id_role-{{ $user->id_usuario }}" class="form-label">Rol</label>
                                    <select class="form-select" id="id_role-{{ $user->id_usuario }}" name="id_role" required>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->id_role }}"
                                                {{ (int) $user->id_role === (int) $role->id_role ? 'selected' : '' }}>
                                                {{ ucfirst($role->nombre) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="estado-{{ $user->id_usuario }}" class="form-label">Estado</label>
                                    <select class="form-select" id="estado-{{ $user->id_usuario }}" name="estado" required>
                                        <option value="activo" {{ $user->estado === 'activo' ? 'selected' : '' }}>Activo</option>
                                        <option value="inactivo" {{ $user->estado === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password-edit-{{ $user->id_usuario }}" class="form-label">Nueva contraseña (opcional)</label>
                                <input type="password" class="form-control" id="password-edit-{{ $user->id_usuario }}" name="password">
                            </div>

                            <div class="mb-3">
                                <label for="password-confirm-edit-{{ $user->id_usuario }}" class="form-label">Confirmar nueva contraseña</label>
                                <input type="password" class="form-control" id="password-confirm-edit-{{ $user->id_usuario }}"
                                    name="password_confirmation">
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createUserModalLabel">Crear usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>

                        <div class="mb-3">
                            <label for="id_role" class="form-label">Rol</label>
                            <select class="form-select" id="id_role" name="id_role" required>
                                <option value="">Seleccione un rol</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id_role }}">{{ ucfirst($role->nombre) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="activo" selected>Activo</option>
                                <option value="inactivo">Inactivo</option>
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