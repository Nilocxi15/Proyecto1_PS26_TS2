<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\CitizenReportController;
use App\Models\Denuncias;
use App\Models\Contenedores;
use App\Models\EntregasReciclaje;
use App\Models\HistorialEstadoDenuncia;
use App\Models\PuntosVerdes;
use App\Models\Rutas;
use App\Models\TiposMaterial;
use App\Models\VaciadoContenedores;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

$normalizeLabel = function (?string $value, bool $titleCase = false): string {
    $text = trim((string) ($value ?? ''));

    if ($text === '') {
        return 'Sin definir';
    }

    $knownFixes = [
        'Mi├®rcoles' => 'Miércoles',
        'S├íbado' => 'Sábado',
        'MiÃ©rcoles' => 'Miércoles',
        'SÃ¡bado' => 'Sábado',
        'Miercoles' => 'Miércoles',
        'Sabado' => 'Sábado',
        'Organico' => 'Orgánico',
        'Plastico' => 'Plástico',
        'Papel y carton' => 'Papel y Cartón',
    ];

    $text = $knownFixes[$text] ?? $text;

    if ($titleCase) {
        $text = mb_convert_case(mb_strtolower($text, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    return $text;
};

$buildCitizenHomeData = function () use ($normalizeLabel): array {
    try {
        $tiposMaterial = TiposMaterial::query()
            ->orderBy('nombre')
            ->pluck('nombre')
            ->map(fn ($tipo) => $normalizeLabel($tipo, true))
            ->unique()
            ->values();

        $rutas = Rutas::query()
            ->with([
                'dias:id_dia,nombre',
                'coordenadas' => function ($query): void {
                    $query->orderBy('orden');
                },
            ])
            ->orderBy('nombre')
            ->get();

        $greenPoints = PuntosVerdes::query()
            ->orderBy('nombre')
            ->get(['nombre', 'direccion', 'latitud', 'longitud', 'horario'])
            ->map(function ($punto): array {
                return [
                    'nombre' => $punto->nombre ?: 'Punto verde',
                    'direccion' => $punto->direccion ?: 'Sin direccion',
                    'latitud' => (float) $punto->latitud,
                    'longitud' => (float) $punto->longitud,
                    'horario' => $punto->horario ?: 'Sin definir',
                ];
            })
            ->filter(function (array $punto): bool {
                return $punto['latitud'] !== 0.0 || $punto['longitud'] !== 0.0;
            })
            ->values()
            ->all();

        $horarios = collect();
        $mapRoutes = [];

        foreach ($rutas as $ruta) {
            $dias = $ruta->dias->pluck('nombre')->filter()->values();

            if ($dias->isEmpty()) {
                $dias = collect(['Sin definir']);
            }

            foreach ($dias as $dia) {
                $horarios->push([
                    'ruta' => $ruta->nombre ?: 'Ruta sin nombre',
                    'tipo_residuo' => $normalizeLabel($ruta->tipo_residuo, true),
                    'hora_inicio' => $ruta->horario_inicio ?: '--:--',
                    'hora_fin' => $ruta->horario_fin ?: '--:--',
                    'dia' => $normalizeLabel($dia, false),
                ]);
            }

            $points = $ruta->coordenadas
                ->map(function ($coord): array {
                    return [(float) $coord->latitud, (float) $coord->longitud];
                })
                ->filter(function (array $point): bool {
                    return $point[0] !== 0.0 || $point[1] !== 0.0;
                })
                ->values()
                ->all();

            if (count($points) < 2 && $ruta->lat_inicio && $ruta->lon_inicio && $ruta->lat_fin && $ruta->lon_fin) {
                $points = [
                    [(float) $ruta->lat_inicio, (float) $ruta->lon_inicio],
                    [(float) $ruta->lat_fin, (float) $ruta->lon_fin],
                ];
            }

            if (count($points) >= 2) {
                $mapRoutes[] = [
                    'nombre' => $ruta->nombre ?: 'Ruta sin nombre',
                    'tipo_residuo' => $normalizeLabel($ruta->tipo_residuo, true),
                    'puntos' => $points,
                ];
            }
        }

        if ($horarios->isEmpty()) {
            $horarios = collect([
                ['ruta' => 'Zona Centro', 'tipo_residuo' => 'Organico', 'hora_inicio' => '08:00', 'hora_fin' => '12:00', 'dia' => 'Lunes'],
                ['ruta' => 'Zona Norte', 'tipo_residuo' => 'Plastico', 'hora_inicio' => '14:00', 'hora_fin' => '18:00', 'dia' => 'Miercoles'],
                ['ruta' => 'Zona Sur', 'tipo_residuo' => 'Papel y carton', 'hora_inicio' => '08:00', 'hora_fin' => '12:00', 'dia' => 'Viernes'],
                ['ruta' => 'Zona 5', 'tipo_residuo' => 'Vidrio', 'hora_inicio' => '13:00', 'hora_fin' => '16:00', 'dia' => 'Martes'],
            ]);
        }

        if (empty($mapRoutes)) {
            $mapRoutes = [
                [
                    'nombre' => 'Zona Centro',
                    'tipo_residuo' => 'Organico',
                    'puntos' => [[14.6349, -90.5069], [14.6401, -90.5002], [14.6468, -90.4975]],
                ],
                [
                    'nombre' => 'Zona Norte',
                    'tipo_residuo' => 'Plastico',
                    'puntos' => [[14.6613, -90.5222], [14.669, -90.516], [14.6744, -90.5098]],
                ],
            ];
        }

        if (empty($greenPoints)) {
            $greenPoints = [
                [
                    'nombre' => 'Punto Verde Centro',
                    'direccion' => 'Zona 1, Centro Historico',
                    'latitud' => 14.6373,
                    'longitud' => -90.5135,
                    'horario' => '08:00-17:00',
                ],
                [
                    'nombre' => 'Punto Verde Norte',
                    'direccion' => 'Zona 17, Boulevard Principal',
                    'latitud' => 14.6684,
                    'longitud' => -90.4979,
                    'horario' => '09:00-18:00',
                ],
            ];
        }

        return compact('tiposMaterial', 'horarios', 'mapRoutes', 'greenPoints');
    } catch (\Throwable $exception) {
        return [
            'tiposMaterial' => collect(['Organico', 'Plastico', 'Papel y carton', 'Vidrio']),
            'horarios' => collect([
                ['ruta' => 'Zona Centro', 'tipo_residuo' => 'Organico', 'hora_inicio' => '08:00', 'hora_fin' => '12:00', 'dia' => 'Lunes'],
                ['ruta' => 'Zona Norte', 'tipo_residuo' => 'Plastico', 'hora_inicio' => '14:00', 'hora_fin' => '18:00', 'dia' => 'Miercoles'],
                ['ruta' => 'Zona Sur', 'tipo_residuo' => 'Papel y carton', 'hora_inicio' => '08:00', 'hora_fin' => '12:00', 'dia' => 'Viernes'],
                ['ruta' => 'Zona 5', 'tipo_residuo' => 'Vidrio', 'hora_inicio' => '13:00', 'hora_fin' => '16:00', 'dia' => 'Martes'],
            ]),
            'mapRoutes' => [
                [
                    'nombre' => 'Zona Centro',
                    'tipo_residuo' => 'Organico',
                    'puntos' => [[14.6349, -90.5069], [14.6401, -90.5002], [14.6468, -90.4975]],
                ],
                [
                    'nombre' => 'Zona Norte',
                    'tipo_residuo' => 'Plastico',
                    'puntos' => [[14.6613, -90.5222], [14.669, -90.516], [14.6744, -90.5098]],
                ],
            ],
            'greenPoints' => [
                [
                    'nombre' => 'Punto Verde Centro',
                    'direccion' => 'Zona 1, Centro Historico',
                    'latitud' => 14.6373,
                    'longitud' => -90.5135,
                    'horario' => '08:00-17:00',
                ],
                [
                    'nombre' => 'Punto Verde Norte',
                    'direccion' => 'Zona 17, Boulevard Principal',
                    'latitud' => 14.6684,
                    'longitud' => -90.4979,
                    'horario' => '09:00-18:00',
                ],
            ],
        ];
    }
};

// ---------------------------------------------------------------------------
// Rutas de inicio de sesión
// ---------------------------------------------------------------------------
Route::get('/', function () use ($buildCitizenHomeData) {
    return view('citizen.home', $buildCitizenHomeData());
})->name('home-public');
Route::get('/login', [LoginController::class, 'mostrarFormulario'])->name('login');
Route::post('/login', [LoginController::class, 'iniciarSesion'])->name('login.store');
Route::post('/logout', [LoginController::class, 'cerrarSesion'])->name('logout');

// ---------------------------------------------------------------------------
// Rutas para registro de usuarios (Rol Ciudadano)
// ---------------------------------------------------------------------------
Route::get('/register', [RegistroController::class, 'mostrarFormulario'])->name('register');
Route::post('/register', [RegistroController::class, 'registrar'])->name('register.store');

// ---------------------------------------------------------------------------
// Ruta global para actualizar telefono de perfil (roles autenticados)
// ---------------------------------------------------------------------------
Route::middleware('role:1,2,3,4,5')
    ->post('/profile/change-phone', [PerfilController::class, 'cambiarTelefono'])
    ->name('profile.change-phone');

// ---------------------------------------------------------------------------
// Rutas para la página de inicio del ciudadano
// ---------------------------------------------------------------------------
Route::middleware('role:4')->group(function () use ($buildCitizenHomeData) {
    Route::get('/citizen/home', function () use ($buildCitizenHomeData) {
        return view('citizen.home', $buildCitizenHomeData());
    })->name('home-citizen');

    // ---------------------------------------------------------------------------
    // Rutas para el perfil de los usuarios
    // ---------------------------------------------------------------------------
    Route::get('/citizen/profile', function () {
        return view('citizen.profile');
    })->name('profile-citizen');

    // ---------------------------------------------------------------------------
    // Rutas para la página de reportes del ciudadano
    // ---------------------------------------------------------------------------
    Route::get('/citizen/reports', [CitizenReportController::class, 'index'])->name('report-citizen');
    Route::post('/citizen/reports', [CitizenReportController::class, 'store'])->name('report-citizen.store');

});

// ---------------------------------------------------------------------------
// Ruta pública de estadísticas
// ---------------------------------------------------------------------------
Route::get('/citizen/public-statistics', function () {
    return view('citizen.stats');
})->name('citizen.public-statistics');

// ---------------------------------------------------------------------------
// Rutas para la página de inicio del operador de punto verde
// ---------------------------------------------------------------------------
Route::middleware('role:3')->group(function () {
    Route::get('/operator/containers', function () {
        $usuarioId = auth()->id();

        $contenedores = Contenedores::query()
            ->with('tipoMaterial:id_material,nombre')
            ->whereHas('puntoVerde', function ($query) use ($usuarioId): void {
                $query->where('id_encargado', $usuarioId);
            })
            ->orderBy('id_contenedor')
            ->get();

        $materiales = $contenedores
            ->map(fn ($contenedor) => trim((string) optional($contenedor->tipoMaterial)->nombre))
            ->filter(fn ($nombre) => $nombre !== '')
            ->unique()
            ->values();

        return view('operator.container', [
            'contenedores' => $contenedores,
            'materiales' => $materiales,
        ]);
    })->name('operator.containers');

    // ---------------------------------------------------------------------------
    // Rutas para el perfil de los usuarios (Rol operador de punto verde)
    // ---------------------------------------------------------------------------
    Route::get('/operator/profile', function () {
        return view('operator.profile');
    })->name('profile-operator');

    Route::post('/operator/containers/{contenedor}/empty-request', function (int $contenedor) {
        $usuarioId = auth()->id();

        $contenedorModel = Contenedores::query()
            ->where('id_contenedor', $contenedor)
            ->whereHas('puntoVerde', function ($query) use ($usuarioId): void {
                $query->where('id_encargado', $usuarioId);
            })
            ->firstOrFail();

        DB::transaction(function () use ($contenedorModel): void {
            $porcentajeLlenado = (float) $contenedorModel->porcentaje_llenado;
            $capacidadKg = (float) $contenedorModel->capacidad_kg;
            $cantidadRetirada = round(($capacidadKg * $porcentajeLlenado) / 100, 2);

            VaciadoContenedores::query()->create([
                'id_contenedor' => $contenedorModel->id_contenedor,
                'fecha' => now(),
                'cantidad_retirada_kg' => $cantidadRetirada,
            ]);

            $contenedorModel->porcentaje_llenado = 0;
            $contenedorModel->save();
        });

        return redirect()
            ->route('operator.containers')
            ->with('status', 'Solicitud de vaciado registrada correctamente.');
    })->name('operator.containers.empty-request');

    Route::post('/operator/containers/{contenedor}/deliveries', function (Request $request, int $contenedor) {
        $usuarioId = auth()->id();

        $validated = $request->validate([
            'cantidad_kg' => ['required', 'numeric', 'gt:0'],
        ], [
            'cantidad_kg.required' => 'Debes ingresar una cantidad en kilogramos.',
            'cantidad_kg.numeric' => 'La cantidad debe ser numérica.',
            'cantidad_kg.gt' => 'La cantidad debe ser mayor que 0.',
        ]);

        $contenedorModel = Contenedores::query()
            ->where('id_contenedor', $contenedor)
            ->whereHas('puntoVerde', function ($query) use ($usuarioId): void {
                $query->where('id_encargado', $usuarioId);
            })
            ->firstOrFail();

        $cantidadKg = round((float) $validated['cantidad_kg'], 2);
        $capacidadKg = (float) $contenedorModel->capacidad_kg;
        $porcentajeActual = (float) $contenedorModel->porcentaje_llenado;
        $cantidadActualKg = round(($capacidadKg * $porcentajeActual) / 100, 2);
        $capacidadDisponibleKg = round(max($capacidadKg - $cantidadActualKg, 0), 2);

        if ($cantidadKg > $capacidadDisponibleKg) {
            return redirect()
                ->route('operator.containers')
                ->with('error', 'La cantidad supera la capacidad disponible del contenedor.')
                ->withInput();
        }

        DB::transaction(function () use ($contenedorModel, $cantidadKg, $capacidadKg, $cantidadActualKg): void {
            EntregasReciclaje::query()->create([
                'id_contenedor' => $contenedorModel->id_contenedor,
                'ciudadano_codigo' => null,
                'cantidad_kg' => $cantidadKg,
                'fecha' => now(),
            ]);

            $nuevoTotalKg = $cantidadActualKg + $cantidadKg;
            $contenedorModel->porcentaje_llenado = $capacidadKg > 0
                ? round(($nuevoTotalKg / $capacidadKg) * 100, 2)
                : 0;
            $contenedorModel->save();
        });

        return redirect()
            ->route('operator.containers')
            ->with('status', 'Entrega de material registrada correctamente.');
    })->name('operator.containers.deliveries.store');
});

// ---------------------------------------------------------------------------
// Rutas para la página de inicio del administrador
// ---------------------------------------------------------------------------
Route::middleware('role:1')->group(function () {
    Route::get('/admin/home', function () {
        return view('admin.home');
    })->name('home-admin');

    // ---------------------------------------------------------------------------
    // Rutas para la página de gestión de usuarios (Rol administrador)
    // ---------------------------------------------------------------------------
    Route::get('/admin/users', function () {
        return view('admin.users-management');
    })->name('admin.users');

    // ---------------------------------------------------------------------------
    // Rutas para la página de gestión de reportes (Rol administrador)
    // ---------------------------------------------------------------------------
    Route::get('/admin/reports', function () {
        return view('admin.reports-stats');
    })->name('admin.reports');

    // ---------------------------------------------------------------------------
    // Rutas para la página de configuración del sistema (Rol administrador)
    // ---------------------------------------------------------------------------
    Route::get('/admin/system-settings', function () {
        return view('admin.system-settings');
    })->name('admin.system-settings');

    // ---------------------------------------------------------------------------
    // Rutas para ver perfil de usuario (Rol administrador)
    // ---------------------------------------------------------------------------
    Route::get('/admin/user-profile', function () {
        return view('admin.profile');
    })->name('admin.user-profile');
});

// ---------------------------------------------------------------------------
// Rutas para la página de inicio (Rol auditor)
// ---------------------------------------------------------------------------
Route::middleware('role:5')->group(function () {
    Route::get('/auditor/home', function () {
        return view('auditor.home');
    })->name('home-auditor');

    // ---------------------------------------------------------------------------
    // Rutas para la página del perfil del auditor
    // ---------------------------------------------------------------------------
    Route::get('/auditor/profile', function () {
        return view('auditor.profile');
    })->name('profile-auditor');
});

// ---------------------------------------------------------------------------
// Rutas para la página de inicio (Rol coordinador de rutas)
// ---------------------------------------------------------------------------
Route::middleware('role:2')->group(function () {
    Route::get('/coordinator/home', function () {
        return view('coordinator.home');
    })->name('home-coordinator');

    // ---------------------------------------------------------------------------
    // Rutas para la página de incidentes del coordinador de rutas
    // ---------------------------------------------------------------------------
    Route::get('/coordinator/incidents', function () {
        $denuncias = Denuncias::query()
            ->orderByDesc('fecha')
            ->orderByDesc('id_denuncia')
            ->get();

        $estadosDenuncia = [
            'recibida' => 'Recibida',
            'en_revision' => 'En revisión',
            'asignada' => 'Asignada',
            'en_atencion' => 'En atención',
            'atendida' => 'Atendida',
            'cerrada' => 'Cerrada',
        ];

        return view('coordinator.incidents', [
            'denuncias' => $denuncias,
            'estadosDenuncia' => $estadosDenuncia,
        ]);
    })->name('coordinator.incidents');

    Route::post('/coordinator/incidents/{denuncia}/status', function (Request $request, int $denuncia) {
        $estadosPermitidos = [
            'recibida',
            'en_revision',
            'asignada',
            'en_atencion',
            'atendida',
            'cerrada',
        ];

        $normalizeStatusKey = function ($value): string {
            $normalized = mb_strtolower(trim((string) $value), 'UTF-8');

            return match ($normalized) {
                'recibida' => 'recibida',
                'en revision', 'en_revision', 'en revisión' => 'en_revision',
                'asignada', 'asingada' => 'asignada',
                'en atencion', 'en_atencion', 'en atención' => 'en_atencion',
                'atendida' => 'atendida',
                'cerrada' => 'cerrada',
                default => 'recibida',
            };
        };

        $validated = $request->validate([
            'estado' => ['required', 'string', Rule::in($estadosPermitidos)],
        ]);

        $denunciaModel = Denuncias::query()->findOrFail($denuncia);

        $estadoActual = $normalizeStatusKey($denunciaModel->estado);
        $indiceActual = array_search($estadoActual, $estadosPermitidos, true);
        $indiceNuevo = array_search($validated['estado'], $estadosPermitidos, true);

        if ($indiceActual !== false && $indiceNuevo !== false && $indiceNuevo < $indiceActual) {
            return redirect()
                ->route('coordinator.incidents')
                ->withErrors(['estado' => 'No es posible regresar la denuncia a un estado anterior.']);
        }

        DB::transaction(function () use ($denunciaModel, $validated): void {
            $denunciaModel->estado = $validated['estado'];
            $denunciaModel->save();

            HistorialEstadoDenuncia::query()->create([
                'id_denuncia' => $denunciaModel->id_denuncia,
                'estado' => $validated['estado'],
                'fecha' => now(),
                'id_usuario' => auth()->id(),
            ]);
        });

        return redirect()
            ->route('coordinator.incidents')
            ->with('status', 'Estado de la denuncia actualizado correctamente.');
    })->name('coordinator.incidents.status.update');

    // ---------------------------------------------------------------------------
    // Rutas para la página de planificación de rutas del coordinador de rutas
    // ---------------------------------------------------------------------------
    Route::get('/coordinator/routes', function () {
        return view('coordinator.routes');
    })->name('coordinator.routes');

    // ---------------------------------------------------------------------------
    // Rutas para la página de perfil del coordinador de rutas
    // ---------------------------------------------------------------------------
    Route::get('/coordinator/profile', function () {
        return view('coordinator.profile');
    })->name('coordinator.profile');

    // ---------------------------------------------------------------------------
    // Rutas para la gestión de camiones (Rol coordinador de rutas)
    // ---------------------------------------------------------------------------
    Route::get('/coordinator/trucks', function () {
        return view('coordinator.trucks');
    })->name('coordinator.trucks');

    // ---------------------------------------------------------------------------
    // Rutas para la gestión de proceso de recolección
    Route::get('/coordinator/collection-process', function () {
        return view('coordinator.collection-process');
    })->name('coordinator.collection-process');
});

// ---------------------------------------------------------------------------
// Rutas para páginas de error personalizadas
// ---------------------------------------------------------------------------
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});

