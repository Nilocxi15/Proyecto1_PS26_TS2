<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Camiones;
use App\Models\PuntosRecoleccion;
use App\Models\Rutas;
use App\Models\RutasProgramadas;
use App\Models\Usuarios;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TruckManagementController extends Controller
{
    private const ALLOWED_STATES = ['operativo', 'mantenimiento', 'fuera_servicio'];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $estado = trim((string) $request->query('estado', ''));
        $conductorId = $request->query('id_conductor');

        $assignmentSearch = trim((string) $request->query('prog_q', ''));
        $assignmentRouteId = $request->query('prog_ruta');
        $assignmentTruckId = $request->query('prog_camion');

        $conductores = $this->getDriversForCombobox();
        $rutas = Rutas::query()
            ->with([
                'zona:id_zona,nombre,tipo',
                'coordenadas' => function ($query): void {
                    $query->orderBy('orden');
                },
            ])
            ->orderBy('nombre')
            ->get(['id_ruta', 'nombre', 'id_zona', 'lat_inicio', 'lon_inicio', 'lat_fin', 'lon_fin']);

        $camionesCatalogo = Camiones::query()
            ->orderBy('placa')
            ->orderBy('id_camion')
            ->get(['id_camion', 'placa', 'capacidad_toneladas']);

        $camiones = Camiones::query()
            ->with(['conductor:id_usuario,nombre,email'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery->where('placa', 'like', "%{$search}%")
                        ->orWhere('capacidad_toneladas', 'like', "%{$search}%")
                        ->orWhereHas('conductor', function ($driverQuery) use ($search): void {
                            $driverQuery->where('nombre', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($estado, self::ALLOWED_STATES, true), function ($query) use ($estado): void {
                $query->where('estado', $estado);
            })
            ->when(is_numeric($conductorId) && (int) $conductorId > 0, function ($query) use ($conductorId): void {
                $query->where('id_conductor', (int) $conductorId);
            })
            ->orderBy('placa')
            ->orderBy('id_camion')
            ->paginate(10, ['*'], 'trucks_page')
            ->withQueryString();

        $programaciones = RutasProgramadas::query()
            ->with([
                'ruta:id_ruta,nombre,id_zona,lat_inicio,lon_inicio,lat_fin,lon_fin',
                'ruta.zona:id_zona,nombre,tipo',
                'ruta.coordenadas' => function ($query): void {
                    $query->orderBy('orden');
                },
                'camion:id_camion,placa,capacidad_toneladas',
                'puntosRecoleccion:id_punto,id_programacion,latitud,longitud,basura_estimada_kg',
            ])
            ->withCount('puntosRecoleccion')
            ->withSum('puntosRecoleccion as total_basura_estimada_kg', 'basura_estimada_kg')
            ->where('estado', 'programada')
            ->when($assignmentSearch !== '', function ($query) use ($assignmentSearch): void {
                $query->where(function ($innerQuery) use ($assignmentSearch): void {
                    $innerQuery->where('fecha', 'like', "%{$assignmentSearch}%")
                        ->orWhereHas('ruta', function ($routeQuery) use ($assignmentSearch): void {
                            $routeQuery->where('nombre', 'like', "%{$assignmentSearch}%");
                        })
                        ->orWhereHas('camion', function ($truckQuery) use ($assignmentSearch): void {
                            $truckQuery->where('placa', 'like', "%{$assignmentSearch}%");
                        });
                });
            })
            ->when(is_numeric($assignmentRouteId) && (int) $assignmentRouteId > 0, function ($query) use ($assignmentRouteId): void {
                $query->where('id_ruta', (int) $assignmentRouteId);
            })
            ->when(is_numeric($assignmentTruckId) && (int) $assignmentTruckId > 0, function ($query) use ($assignmentTruckId): void {
                $query->where('id_camion', (int) $assignmentTruckId);
            })
            ->orderByDesc('fecha')
            ->orderByDesc('id_programacion')
            ->paginate(10, ['*'], 'assignments_page')
            ->withQueryString();

        $assignmentMapData = collect($programaciones->items())
            ->map(function ($programacion): array {
                $points = $programacion->puntosRecoleccion
                    ->map(fn ($punto): array => [
                        'lat' => (float) $punto->latitud,
                        'lng' => (float) $punto->longitud,
                        'basura_kg' => (float) $punto->basura_estimada_kg,
                    ])
                    ->values()
                    ->all();

                $trajectory = collect(optional($programacion->ruta)->coordenadas ?? [])
                    ->map(fn ($coord): array => [
                        'lat' => (float) $coord->latitud,
                        'lng' => (float) $coord->longitud,
                    ])
                    ->values()
                    ->all();

                if (count($trajectory) < 2 && $programacion->ruta) {
                    $route = $programacion->ruta;

                    if ($route->lat_inicio && $route->lon_inicio && $route->lat_fin && $route->lon_fin) {
                        $trajectory = [
                            ['lat' => (float) $route->lat_inicio, 'lng' => (float) $route->lon_inicio],
                            ['lat' => (float) $route->lat_fin, 'lng' => (float) $route->lon_fin],
                        ];
                    }
                }

                return [
                    'id_programacion' => $programacion->id_programacion,
                    'ruta' => $programacion->ruta->nombre ?? 'Ruta',
                    'camion' => $programacion->camion->placa ?? 'Camión',
                    'trayectoria' => $trajectory,
                    'puntos' => $points,
                ];
            })
            ->values()
            ->all();

        return view('coordinator.trucks', [
            'camiones' => $camiones,
            'camionesCatalogo' => $camionesCatalogo,
            'conductores' => $conductores,
            'rutas' => $rutas,
            'programaciones' => $programaciones,
            'assignmentMapData' => $assignmentMapData,
            'allowedStates' => self::ALLOWED_STATES,
            'filters' => [
                'q' => $search,
                'estado' => $estado,
                'id_conductor' => is_numeric($conductorId) ? (int) $conductorId : null,
                'prog_q' => $assignmentSearch,
                'prog_ruta' => is_numeric($assignmentRouteId) ? (int) $assignmentRouteId : null,
                'prog_camion' => is_numeric($assignmentTruckId) ? (int) $assignmentTruckId : null,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'placa' => ['required', 'string', 'max:20', 'unique:camiones,placa'],
            'capacidad_toneladas' => ['required', 'numeric', 'gt:0'],
            'estado' => ['required', 'string', Rule::in(self::ALLOWED_STATES)],
            'id_conductor' => ['nullable', 'integer', Rule::exists('usuarios', 'id_usuario')],
        ]);

        if (!$this->isValidDriverId($validated['id_conductor'] ?? null)) {
            return redirect()
                ->route('coordinator.trucks')
                ->withErrors(['id_conductor' => 'El usuario seleccionado no tiene rol de conductor.'])
                ->withInput();
        }

        Camiones::query()->create([
            'placa' => mb_strtoupper(trim($validated['placa']), 'UTF-8'),
            'capacidad_toneladas' => round((float) $validated['capacidad_toneladas'], 2),
            'estado' => $validated['estado'],
            'id_conductor' => $validated['id_conductor'] ?? null,
        ]);

        return redirect()
            ->route('coordinator.trucks')
            ->with('status', 'Camión agregado correctamente.');
    }

    public function updateDriver(Request $request, int $camion): RedirectResponse
    {
        $validated = $request->validate([
            'id_conductor' => ['nullable', 'integer', Rule::exists('usuarios', 'id_usuario')],
        ]);

        if (!$this->isValidDriverId($validated['id_conductor'] ?? null)) {
            return redirect()
                ->route('coordinator.trucks')
                ->withErrors(['id_conductor' => 'El usuario seleccionado no tiene rol de conductor.'])
                ->withInput();
        }

        $camionModel = Camiones::query()->findOrFail($camion);
        $camionModel->id_conductor = $validated['id_conductor'] ?? null;
        $camionModel->save();

        return redirect()
            ->route('coordinator.trucks')
            ->with('status', 'Conductor actualizado correctamente.');
    }

    public function updateState(Request $request, int $camion): RedirectResponse
    {
        $validated = $request->validate([
            'estado' => ['required', 'string', Rule::in(self::ALLOWED_STATES)],
        ]);

        DB::table('camiones')
            ->where('id_camion', $camion)
            ->update(['estado' => $validated['estado']]);

        return redirect()
            ->route('coordinator.trucks')
            ->with('status', 'Estado del camión actualizado correctamente.');
    }

    public function assignRoute(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_ruta' => ['required', 'integer', Rule::exists('rutas', 'id_ruta')],
            'id_camion' => ['required', 'integer', Rule::exists('camiones', 'id_camion')],
            'fecha' => ['required', 'date'],
        ]);

        $ruta = Rutas::query()
            ->with([
                'zona:id_zona,nombre,tipo',
                'coordenadas' => function ($query): void {
                    $query->orderBy('orden');
                },
            ])
            ->findOrFail((int) $validated['id_ruta']);

        $camion = Camiones::query()->findOrFail((int) $validated['id_camion']);
        $fecha = CarbonImmutable::parse($validated['fecha']);

        if ($camion->estado !== 'operativo' || $camion->id_conductor === null) {
            return redirect()
                ->route('coordinator.trucks')
                ->with('error', 'Solo se pueden asignar camiones operativos y con conductor asignado.')
                ->withInput();
        }

        $routePoints = $this->resolveRoutePoints($ruta);
        if (count($routePoints) < 2) {
            return redirect()
                ->route('coordinator.trucks')
                ->with('error', 'La ruta seleccionada no tiene un trazado válido para generar puntos de recolección.');
        }

        $pointCount = random_int(15, 30);
        $generatedPoints = $this->generatePointsAlongRoute($routePoints, $pointCount);

        $densityFactor = $this->densityFactor((string) optional($ruta->zona)->tipo);
        $weekendFactor = $fecha->isWeekend() ? 1.2 : 1.0;
        $historyFactor = $this->historyFactor($ruta->id_ruta, $pointCount);

        $pointsWithGarbage = collect($generatedPoints)
            ->map(function (array $point) use ($densityFactor, $weekendFactor, $historyFactor): array {
                $baseKg = random_int(50, 500);
                $estimatedKg = round($baseKg * $densityFactor * $weekendFactor * $historyFactor, 2);

                return [
                    'lat' => round((float) $point['lat'], 7),
                    'lng' => round((float) $point['lng'], 7),
                    'kg' => max($estimatedKg, 1),
                ];
            })
            ->values();

        $totalEstimatedKg = (float) $pointsWithGarbage->sum('kg');
        $capacityKg = (float) $camion->capacidad_toneladas * 1000;

        if ($totalEstimatedKg > $capacityKg) {
            return redirect()
                ->route('coordinator.trucks')
                ->with('error', 'La capacidad del camión no es suficiente para la basura estimada de esta ruta.')
                ->withInput();
        }

        DB::transaction(function () use ($validated, $pointsWithGarbage): void {
            $programacion = RutasProgramadas::query()->create([
                'id_ruta' => (int) $validated['id_ruta'],
                'id_camion' => (int) $validated['id_camion'],
                'fecha' => $validated['fecha'],
                'estado' => 'programada',
                'hora_inicio' => null,
                'hora_fin' => null,
                'basura_recolectada_ton' => null,
                'observaciones' => null,
            ]);

            foreach ($pointsWithGarbage as $point) {
                PuntosRecoleccion::query()->create([
                    'id_programacion' => $programacion->id_programacion,
                    'latitud' => $point['lat'],
                    'longitud' => $point['lng'],
                    'basura_estimada_kg' => $point['kg'],
                    'basura_real_kg' => null,
                ]);
            }
        });

        return redirect()
            ->route('coordinator.trucks')
            ->with('status', 'Ruta asignada al camión correctamente en estado programada.');
    }

    public function destroy(int $camion): RedirectResponse
    {
        $camionModel = Camiones::query()->findOrFail($camion);

        try {
            $camionModel->delete();
        } catch (QueryException $exception) {
            return redirect()
                ->route('coordinator.trucks')
                ->with('error', 'No se puede eliminar el camión porque tiene registros asociados.');
        }

        return redirect()
            ->route('coordinator.trucks')
            ->with('status', 'Camión eliminado correctamente.');
    }

    private function getDriversForCombobox()
    {
        return Usuarios::query()
            ->whereHas('rol', function ($query): void {
                $query->whereRaw('LOWER(nombre) = ?', ['conductor']);
            })
            ->orderBy('nombre')
            ->get(['id_usuario', 'nombre', 'email']);
    }

    private function isValidDriverId(?int $driverId): bool
    {
        if ($driverId === null) {
            return true;
        }

        return Usuarios::query()
            ->where('id_usuario', $driverId)
            ->whereHas('rol', function ($query): void {
                $query->whereRaw('LOWER(nombre) = ?', ['conductor']);
            })
            ->exists();
    }

    private function densityFactor(string $zoneType): float
    {
        $normalized = mb_strtolower(trim($zoneType), 'UTF-8');

        return match ($normalized) {
            'industrial' => 1.25,
            'comercial' => 1.15,
            'residencial' => 1.0,
            default => 1.05,
        };
    }

    private function historyFactor(int $routeId, int $pointCount): float
    {
        $historicalTotals = RutasProgramadas::query()
            ->where('id_ruta', $routeId)
            ->withSum('puntosRecoleccion as total_basura_estimada_kg', 'basura_estimada_kg')
            ->limit(25)
            ->get()
            ->pluck('total_basura_estimada_kg')
            ->filter(fn ($value) => is_numeric($value) && (float) $value > 0)
            ->map(fn ($value) => (float) $value);

        if ($historicalTotals->isEmpty()) {
            return 1.0;
        }

        $averageHistoricalKg = (float) $historicalTotals->avg();
        $baselineKg = max($pointCount * 275, 1);
        $factor = $averageHistoricalKg / $baselineKg;

        return min(max($factor, 0.7), 1.3);
    }

    private function resolveRoutePoints(Rutas $ruta): array
    {
        $points = $ruta->coordenadas
            ->map(fn ($coord): array => [
                'lat' => (float) $coord->latitud,
                'lng' => (float) $coord->longitud,
            ])
            ->values()
            ->all();

        if (count($points) < 2 && $ruta->lat_inicio && $ruta->lon_inicio && $ruta->lat_fin && $ruta->lon_fin) {
            $points = [
                ['lat' => (float) $ruta->lat_inicio, 'lng' => (float) $ruta->lon_inicio],
                ['lat' => (float) $ruta->lat_fin, 'lng' => (float) $ruta->lon_fin],
            ];
        }

        return $points;
    }

    private function generatePointsAlongRoute(array $routePoints, int $pointCount): array
    {
        $maxSegmentIndex = count($routePoints) - 2;
        $generated = [];

        for ($index = 0; $index < $pointCount; $index++) {
            $segment = $maxSegmentIndex > 0
                ? random_int(0, $maxSegmentIndex)
                : 0;

            $start = $routePoints[$segment];
            $end = $routePoints[$segment + 1];
            $t = mt_rand() / mt_getrandmax();

            $generated[] = [
                'lat' => $start['lat'] + (($end['lat'] - $start['lat']) * $t),
                'lng' => $start['lng'] + (($end['lng'] - $start['lng']) * $t),
            ];
        }

        return $generated;
    }
}
