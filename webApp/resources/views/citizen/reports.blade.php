<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('icono-reciclaje.png') }}">
    <link rel="stylesheet" href="{{ asset('css/citizen/reports.css') }}">
    <title>Reportes y Denuncias</title>
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
                        height="24" class="d-inline-block align-text-top"> Reportes y Denuncias</a>
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
                                <a class="nav-link" href="{{ route('home-citizen') }}"><i class="bi bi-house"></i>
                                    Inicio</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="{{ route('report-citizen') }}"><i
                                        class="bi bi-file-earmark-medical"></i> Reportes y
                                    denuncias</a>
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
        <div class="container-fluid">
            <!-- Botón para crear nuevo reporte -->
            <div class="row mb-4">
                <div class="col-12">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#staticBackdrop">
                        <i class="bi bi-plus-circle"></i> Nuevo Reporte
                    </button>
                </div>
            </div>

            <!-- Tabla de historial de reportes -->
            <div class="row">
                <div class="col-12">
                    <div class="reports-section">
                        <h2><i class="bi bi-list-check"></i> Historial de Reportes</h2>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th><i class="bi bi-calendar"></i> Fecha</th>
                                        <th><i class="bi bi-geo-alt"></i> Ubicación</th>
                                        <th><i class="bi bi-justify"></i> Descripción</th>
                                        <th><i class="bi bi-inbox"></i> Tamaño</th>
                                        <th><i class="bi bi-info-circle"></i> Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>15/02/2026</td>
                                        <td>Zona 3, Calle 5</td>
                                        <td>Basurero con residuos peligrosos</td>
                                        <td><span class="badge bg-warning">Grande</span></td>
                                        <td><span class="badge bg-success">Atendida</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                data-bs-target="#viewReportModal">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>10/02/2026</td>
                                        <td>Zona 12, Avenida Principal</td>
                                        <td>Acumulación de escombros</td>
                                        <td><span class="badge bg-warning">Mediano</span></td>
                                        <td><span class="badge bg-info">En atención</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                data-bs-target="#viewReportModal">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>05/02/2026</td>
                                        <td>Zona 2, Barrio Antiguo</td>
                                        <td>Plásticos y desechos electrónicos</td>
                                        <td><span class="badge bg-success">Pequeño</span></td>
                                        <td><span class="badge bg-secondary">Recibida</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                data-bs-target="#viewReportModal">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Agregar Nueva Denuncia -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Agregar Nueva Denuncia</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="input-group mb-3">
                        <span class="input-group-text" id="dumpLocation">Ubicación del Basurero</span>
                        <input type="text" class="form-control" placeholder="Zona, Avenida, Calle" aria-label="Username"
                            aria-describedby="dumpLocation">
                    </div>

                    <p>Espacio para el mapa</p>

                    <div class="mb-3">
                        <label for="photoInput" class="form-label">Seleccionar foto del lugar</label>
                        <input type="file" class="form-control" id="photoInput" accept="image/*" required>
                        <small class="text-muted d-block mt-2">Formatos permitidos: JPG, PNG (Máximo 5MB)</small>
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text">Descripción del problema</span>
                        <textarea class="form-control" aria-label="Descripción del problema"
                            id="problemDescription"></textarea>
                    </div>


                    <h4>Tamaño estimado del basurero</h4>
                    <div class="btn-group-vertical d-grid" role="group" aria-label="Vertical radio toggle button group">
                        <input type="radio" class="btn-check" name="vbtn-radio" id="vbtn-radio1" autocomplete="off"
                            checked>
                        <label class="btn btn-outline-success" for="vbtn-radio1">Pequeño</label>
                        <input type="radio" class="btn-check" name="vbtn-radio" id="vbtn-radio2" autocomplete="off">
                        <label class="btn btn-outline-warning" for="vbtn-radio2">Mediano</label>
                        <input type="radio" class="btn-check" name="vbtn-radio" id="vbtn-radio3" autocomplete="off">
                        <label class="btn btn-outline-danger" for="vbtn-radio3">Grande</label>
                    </div>

                    <h4>Datos del denunciante</h4>

                    <div class="input-group mb-3">
                        <span class="input-group-text" id="userName">Nombre</span>
                        <input type="text" class="form-control" placeholder="Nombre completo" aria-label="Username"
                            aria-describedby="userName">
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text" id="userPhone">Teléfono</span>
                        <input type="text" class="form-control" placeholder="Número de teléfono" aria-label="Userphone"
                            aria-describedby="userPhone">
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text" id="userEmail">Email</span>
                        <input type="email" class="form-control" placeholder="Dirección de correo electrónico"
                            aria-label="Useremail" aria-describedby="userEmail">
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text" id="complaintDate">Fecha de la Denuncia</span>
                        <input type="date" class="form-control" aria-label="ComplaintDate"
                            aria-describedby="complaintDate">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary">Agregar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Ver Detalle de Denuncia -->
    <div class="modal fade" id="viewReportModal" tabindex="-1" aria-labelledby="viewReportLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewReportLabel">
                        <i class="bi bi-eye"></i> Detalle de Denuncia
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Ubicación:</strong> Zona 3, Calle 5</p>
                            <p><strong>Tamaño:</strong> <span class="badge bg-warning">Grande</span></p>
                            <p><strong>Descripción:</strong> Basurero con residuos peligrosos</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Estado:</strong> <span class="badge bg-success">Atendida</span></p>
                            <p><strong>Fecha:</strong> 15/02/2026</p>
                            <p><strong>ID:</strong> #DEN-001-2026</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h6>Foto del Lugar</h6>
                        <img src="https://via.placeholder.com/400x300" class="img-fluid rounded" alt="Foto del lugar">
                    </div>
                    <div class="mb-3">
                        <h6>Datos del Denunciante</h6>
                        <p><strong>Nombre:</strong> Juan Pérez</p>
                        <p><strong>Teléfono:</strong> +502 1234 5678</p>
                        <p><strong>Email:</strong> juan@example.com</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

</body>

</html>