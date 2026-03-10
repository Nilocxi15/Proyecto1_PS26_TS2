<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('icono-reciclaje.png') }}">
    <link rel="stylesheet" href="{{ asset('css/citizen/home.css') }}">
    <title>Inicio</title>
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
                        height="24" class="d-inline-block align-text-top"> Inicio</a>
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
                                <a class="nav-link active" aria-current="page" href="{{ route('home-citizen') }}"><i class="bi bi-house"></i>
                                    Inicio</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('report-citizen') }}"><i
                                        class="bi bi-file-earmark-medical"></i> Reportes y denuncias</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('citizen.public-statistics') }}"><i
                                        class="bi bi-bar-chart"></i> Estadísticas
                                    Públicas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('profile-citizen') }}"><i
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
        <h1><i class="bi bi-calendar-event"></i> Horarios y Rutas de Recolección</h1>
        <section class="schedule-tools mb-3">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-6">
                    <label for="searchInput" class="form-label mb-1">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="searchInput" class="form-control"
                            placeholder="Buscar por ruta, residuo u horario...">
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="dayFilter" class="form-label mb-1">Filtrar por día</label>
                    <select id="dayFilter" class="form-select">
                        <option value="">Todos los días</option>
                        <option value="Lunes">Lunes</option>
                        <option value="Martes">Martes</option>
                        <option value="Miércoles">Miércoles</option>
                        <option value="Jueves">Jueves</option>
                        <option value="Viernes">Viernes</option>
                        <option value="Sábado">Sábado</option>
                    </select>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="wasteTypeFilter" class="form-label mb-1">Filtrar por residuo</label>
                    <select id="wasteTypeFilter" class="form-select">
                        <option value="">Todos los tipos</option>
                        <option value="Orgánico">Orgánico</option>
                        <option value="Plástico">Plástico</option>
                        <option value="Papel y cartón">Papel y cartón</option>
                        <option value="Vidrio">Vidrio</option>
                    </select>
                </div>
            </div>
        </section>

        <div class="table-responsive">
            <table class="table table-striped table-hover" id="scheduleTable">
                <thead class="table-dark">
                    <tr>
                        <th><i class="bi bi-geo-alt"></i> Ruta</th>
                        <th><i class="bi bi-recycle"></i> Tipo de Residuo</th>
                        <th><i class="bi bi-clock-history"></i> Hora Inicio</th>
                        <th><i class="bi bi-clock"></i> Hora Fin</th>
                        <th><i class="bi bi-calendar-week"></i> Día</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Zona Centro</td>
                        <td>Orgánico</td>
                        <td>08:00 AM</td>
                        <td>12:00 PM</td>
                        <td>Lunes</td>
                    </tr>
                    <tr>
                        <td>Zona Norte</td>
                        <td>Plástico</td>
                        <td>02:00 PM</td>
                        <td>06:00 PM</td>
                        <td>Miércoles</td>
                    </tr>
                    <tr>
                        <td>Zona Sur</td>
                        <td>Papel y cartón</td>
                        <td>08:00 AM</td>
                        <td>12:00 PM</td>
                        <td>Viernes</td>
                    </tr>
                    <tr>
                        <td>Zona 5</td>
                        <td>Vidrio</td>
                        <td>01:00 PM</td>
                        <td>04:00 PM</td>
                        <td>Martes</td>
                    </tr>
                </tbody>
            </table>
            <p id="emptyState" class="empty-state d-none mb-0">No hay rutas que coincidan con los filtros aplicados.</p>
        </div>

        <p>Espacio para mapa</p>

        <h1><i class="bi bi-tree"></i> Puntos Verdes Cercanos</h1>

        <p>Espacio para mapa</p>
    </div>

</body>

</html>