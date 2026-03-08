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
                                <a class="nav-link" href="{{ route('home-operator') }}"><i class="bi bi-house"></i>
                                    Inicio</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" aria-current="page" href="{{ route('operator.containers') }}"><i class="bi bi-trash"></i>
                                    Gestión de contenedores</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('profile-operator') }}"><i
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

    <div class="body-content">
        <div class="header">
            <h1 class="page-title"><i class="bi bi-trash"></i> Registro de Contenedores</h1>

            <!-- Button trigger modal -->
            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                <i class="bi bi-plus-circle"></i> Solicitar vaciado de contenedor
            </button>
        </div>


        <section class="filter-bar mb-3">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-6">
                    <label for="materialFilter" class="form-label mb-1">Material</label>
                    <select id="materialFilter" class="form-select">
                        <option value="">Todos los materiales</option>
                        <option value="Plástico">Plástico</option>
                        <option value="Metal">Metal</option>
                        <option value="Vidrio">Vidrio</option>
                        <option value="Orgánico">Orgánico</option>
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
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Plástico</td>
                        <td>120 L</td>
                        <td data-fill="20"><span class="badge text-bg-success">20%</span></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Metal</td>
                        <td>240 L</td>
                        <td data-fill="54"><span class="badge text-bg-primary">54%</span></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Vidrio</td>
                        <td>180 L</td>
                        <td data-fill="72"><span class="badge text-bg-warning">72%</span></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Orgánico</td>
                        <td>300 L</td>
                        <td data-fill="91"><span class="badge text-bg-danger">91%</span></td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Plástico</td>
                        <td>200 L</td>
                        <td data-fill="37"><span class="badge text-bg-primary">37%</span></td>
                    </tr>
                </tbody>
            </table>
            <p id="emptyState" class="empty-state d-none mb-0">No hay contenedores que coincidan con los filtros
                seleccionados.</p>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Solicitar Vaciado</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">ID del contenedor</span>
                        <input type="number" class="form-control" aria-describedby="basic-addon1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary">Solicitar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function getFillRange(fillValue) {
            if (fillValue <= 25) {
                return 'low';
            }

            if (fillValue <= 60) {
                return 'medium';
            }

            if (fillValue <= 85) {
                return 'high';
            }

            return 'critical';
        }

        function applyContainerFilters() {
            const materialSelected = document.getElementById('materialFilter').value.toLowerCase();
            const fillLevelSelected = document.getElementById('fillLevelFilter').value;
            const rows = document.querySelectorAll('#containersTable tbody tr');
            const emptyState = document.getElementById('emptyState');

            let visibleRows = 0;

            rows.forEach(row => {
                const material = row.children[1].textContent.trim().toLowerCase();
                const fillValue = Number(row.children[3].dataset.fill || 0);
                const fillRange = getFillRange(fillValue);

                const matchesMaterial = !materialSelected || material === materialSelected;
                const matchesFillLevel = !fillLevelSelected || fillRange === fillLevelSelected;
                const shouldShow = matchesMaterial && matchesFillLevel;

                row.style.display = shouldShow ? '' : 'none';

                if (shouldShow) {
                    visibleRows += 1;
                }
            });

            emptyState.classList.toggle('d-none', visibleRows > 0);
        }

        document.getElementById('materialFilter').addEventListener('change', applyContainerFilters);
        document.getElementById('fillLevelFilter').addEventListener('change', applyContainerFilters);
        window.addEventListener('load', applyContainerFilters);
    </script>
</body>

</html>