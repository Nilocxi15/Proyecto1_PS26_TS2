<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('icono-reciclaje.png') }}">
    <link rel="stylesheet" href="{{ asset('css/operator/container.css') }}">
    <title>Gestión de Contenedores</title>
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
                        height="24" class="d-inline-block align-text-top"> Gestión de Contenedores</a>
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
                                <a class="nav-link" href="{{ route('home-public') }}"><i class="bi bi-house"></i>
                                    Pantalla pública</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page"
                                    href="{{ route('operator.containers') }}"><i class="bi bi-trash"></i>
                                    Gestión de contenedores</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('profile-operator') }}"><i
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
        <div class="header">
            <h1 class="page-title"><i class="bi bi-trash"></i> Registro de Contenedores</h1>
        </div>

        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                {{ $errors->first() }}
            </div>
        @endif


        <section class="filter-bar mb-3">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-6">
                    <label for="materialFilter" class="form-label mb-1">Material</label>
                    <select id="materialFilter" class="form-select">
                        <option value="">Todos los materiales</option>
                        @foreach (($materiales ?? collect()) as $material)
                            <option value="{{ $material }}">{{ $material }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label for="fillLevelFilter" class="form-label mb-1">Capacidad de llenado</label>
                    <select id="fillLevelFilter" class="form-select">
                        <option value="">Todos los niveles</option>
                        <option value="low">0% - 25% (Bajo)</option>
                        <option value="medium">26% - 60% (Medio)</option>
                        <option value="high">61% - 85% (Alto)</option>
                        <option value="critical">86% - 100% (Crítico)</option>
                    </select>
                </div>
            </div>
        </section>

        <div class="table-responsive">
            <table class="table table-striped table-hover" id="containersTable">
                <thead class="table-dark">
                    <tr>
                        <th>Id</th>
                        <th>Material</th>
                        <th>Capacidad</th>
                        <th>Porcentaje de llenado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (($contenedores ?? collect()) as $contenedor)
                        @php
                            $material = trim((string) optional($contenedor->tipoMaterial)->nombre);
                            $material = $material !== '' ? $material : 'Sin definir';
                            $llenado = (float) ($contenedor->porcentaje_llenado ?? 0);
                            $llenadoRedondeado = (int) round($llenado);

                            if ($llenado <= 25) {
                                $badgeClass = 'text-bg-success';
                            } elseif ($llenado <= 60) {
                                $badgeClass = 'text-bg-primary';
                            } elseif ($llenado <= 85) {
                                $badgeClass = 'text-bg-warning';
                            } else {
                                $badgeClass = 'text-bg-danger';
                            }
                        @endphp
                        <tr>
                            <td>{{ $contenedor->id_contenedor }}</td>
                            <td>{{ $material }}</td>
                            <td>{{ number_format((float) ($contenedor->capacidad_kg ?? 0), 2) }} Kg</td>
                            <td class="fill-cell" data-fill="{{ $llenado }}"><span class="badge {{ $badgeClass }}">{{ $llenadoRedondeado }}%</span></td>
                            <td>
                                <form method="POST" class="mb-2"
                                    action="{{ route('operator.containers.empty-request', ['contenedor' => $contenedor->id_contenedor]) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-dark request-empty-btn"
                                        onclick="return confirm('¿Confirmas solicitar vaciado para este contenedor?');">
                                        <i class="bi bi-plus-circle"></i> Solicitar vaciado
                                    </button>
                                </form>

                                <form method="POST"
                                    action="{{ route('operator.containers.deliveries.store', ['contenedor' => $contenedor->id_contenedor]) }}">
                                    @csrf
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="cantidad_kg" class="form-control"
                                            min="0.01" step="0.01" placeholder="Kg" required>
                                        <button type="submit" class="btn btn-outline-success">
                                            <i class="bi bi-box-seam"></i> Registrar entrega
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p id="emptyState" class="empty-state d-none mb-0">No hay contenedores que coincidan con los filtros
                seleccionados.</p>
        </div>
    </div>

    <script src="{{ asset('js/operator/container.js') }}" defer></script>
</body>

</html>