<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\IncidenciasRecoleccion;
use App\Models\RutasProgramadas;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CollectionProcessController extends Controller
{
    private const ALLOWED_STATES = [
        'programada',
        'en_proceso',
        'completada',
        'incompleta',
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $state = trim((string) $request->query('estado', ''));
        $truckId = $request->query('id_camion');
        $dateFrom = trim((string) $request->query('fecha_desde', ''));
        $dateTo = trim((string) $request->query('fecha_hasta', ''));

        $programaciones = RutasProgramadas::query()
            ->with([
                'ruta:id_ruta,nombre',
                'camion:id_camion,placa,capacidad_toneladas',
            ])
            ->withCount('incidencias')
            ->withSum('puntosRecoleccion as total_basura_estimada_kg', 'basura_estimada_kg')
            ->withSum('puntosRecoleccion as total_basura_real_kg', 'basura_real_kg')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery->where('fecha', 'like', "%{$search}%")
                        ->orWhere('observaciones', 'like', "%{$search}%")
                        ->orWhereHas('ruta', function ($routeQuery) use ($search): void {
                            $routeQuery->where('nombre', 'like', "%{$search}%");
                        })
                        ->orWhereHas('camion', function ($truckQuery) use ($search): void {
                            $truckQuery->where('placa', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($state, self::ALLOWED_STATES, true), function ($query) use ($state): void {
                $query->where('estado', $state);
            })
            ->when(is_numeric($truckId) && (int) $truckId > 0, function ($query) use ($truckId): void {
                $query->where('id_camion', (int) $truckId);
            })
            ->when($dateFrom !== '', function ($query) use ($dateFrom): void {
                $query->whereDate('fecha', '>=', $dateFrom);
            })
            ->when($dateTo !== '', function ($query) use ($dateTo): void {
                $query->whereDate('fecha', '<=', $dateTo);
            })
            ->orderByDesc('fecha')
            ->orderByDesc('id_programacion')
            ->paginate(10)
            ->withQueryString();

        $camiones = RutasProgramadas::query()
            ->join('camiones', 'camiones.id_camion', '=', 'rutas_programadas.id_camion')
            ->select('camiones.id_camion', 'camiones.placa')
            ->distinct()
            ->orderBy('camiones.placa')
            ->get();

        $incQ = trim((string) $request->query('inc_q', ''));
        $incProgId = $request->query('inc_id_prog');
        $incDateFrom = trim((string) $request->query('inc_fecha_desde', ''));
        $incDateTo = trim((string) $request->query('inc_fecha_hasta', ''));

        $incidencias = IncidenciasRecoleccion::query()
            ->with([
                'programacion:id_programacion,id_ruta,id_camion,fecha',
                'programacion.ruta:id_ruta,nombre',
                'programacion.camion:id_camion,placa',
            ])
            ->when($incQ !== '', function ($query) use ($incQ): void {
                $query->where('descripcion', 'like', "%{$incQ}%");
            })
            ->when(is_numeric($incProgId) && (int) $incProgId > 0, function ($query) use ($incProgId): void {
                $query->where('id_programacion', (int) $incProgId);
            })
            ->when($incDateFrom !== '', function ($query) use ($incDateFrom): void {
                $query->whereDate('fecha', '>=', $incDateFrom);
            })
            ->when($incDateTo !== '', function ($query) use ($incDateTo): void {
                $query->whereDate('fecha', '<=', $incDateTo);
            })
            ->orderByDesc('fecha')
            ->orderByDesc('id_incidencia')
            ->paginate(10, ['*'], 'inc_page')
            ->withQueryString();

        $programacionesList = RutasProgramadas::query()
            ->with('ruta:id_ruta,nombre')
            ->select('id_programacion', 'id_ruta', 'fecha')
            ->orderByDesc('fecha')
            ->orderByDesc('id_programacion')
            ->get();

        return view('coordinator.collection-process', [
            'programaciones' => $programaciones,
            'camiones' => $camiones,
            'allowedStates' => self::ALLOWED_STATES,
            'filters' => [
                'q' => $search,
                'estado' => $state,
                'id_camion' => is_numeric($truckId) ? (int) $truckId : null,
                'fecha_desde' => $dateFrom,
                'fecha_hasta' => $dateTo,
            ],
            'incidencias' => $incidencias,
            'incidenciasFilters' => [
                'inc_q' => $incQ,
                'inc_id_prog' => is_numeric($incProgId) ? (int) $incProgId : null,
                'inc_fecha_desde' => $incDateFrom,
                'inc_fecha_hasta' => $incDateTo,
            ],
            'programacionesList' => $programacionesList,
        ]);
    }

    public function updateState(Request $request, int $programacion): RedirectResponse
    {
        $validated = $request->validate([
            'estado' => ['required', 'string', Rule::in(self::ALLOWED_STATES)],
        ]);

        $model = RutasProgramadas::query()->findOrFail($programacion);
        $currentState = $this->normalizeState((string) $model->estado);
        $newState = $this->normalizeState($validated['estado']);

        if (!$this->canTransition($currentState, $newState)) {
            return redirect()
                ->route('coordinator.collection-process')
                ->with('error', 'Transición de estado no permitida.');
        }

        $updateData = [
            'estado' => $newState,
        ];

        if ($newState === 'en_proceso' && $model->hora_inicio === null) {
            $updateData['hora_inicio'] = now();
        }

        if (in_array($newState, ['completada', 'incompleta'], true) && $model->hora_fin === null) {
            $updateData['hora_fin'] = now();
        }

        $model->update($updateData);

        return redirect()
            ->route('coordinator.collection-process')
            ->with('status', 'Estado actualizado correctamente.');
    }

    public function setStartTime(int $programacion): RedirectResponse
    {
        $model = RutasProgramadas::query()->findOrFail($programacion);

        if ($model->hora_inicio !== null) {
            return redirect()
                ->route('coordinator.collection-process')
                ->with('error', 'La hora de inicio ya fue registrada y no puede modificarse.');
        }

        $model->hora_inicio = now();

        if ($this->normalizeState((string) $model->estado) === 'programada') {
            $model->estado = 'en_proceso';
        }

        $model->save();

        return redirect()
            ->route('coordinator.collection-process')
            ->with('status', 'Hora de inicio registrada correctamente.');
    }

    public function setEndTime(int $programacion): RedirectResponse
    {
        $model = RutasProgramadas::query()->findOrFail($programacion);

        if ($model->hora_fin !== null) {
            return redirect()
                ->route('coordinator.collection-process')
                ->with('error', 'La hora de fin ya fue registrada y no puede modificarse.');
        }

        if ($model->hora_inicio === null) {
            return redirect()
                ->route('coordinator.collection-process')
                ->with('error', 'Debes registrar primero la hora de inicio.');
        }

        $model->hora_fin = now();
        $model->save();

        return redirect()
            ->route('coordinator.collection-process')
            ->with('status', 'Hora de fin registrada correctamente.');
    }

    public function setCollectedWaste(Request $request, int $programacion): RedirectResponse
    {
        $validated = $request->validate([
            'basura_recolectada_ton' => ['required', 'numeric', 'gt:0'],
        ]);

        $model = RutasProgramadas::query()
            ->with('puntosRecoleccion')
            ->findOrFail($programacion);

        if ($model->basura_recolectada_ton !== null) {
            return redirect()
                ->route('coordinator.collection-process')
                ->with('error', 'La basura recolectada ya fue registrada y no puede modificarse.');
        }

        $totalRealKg = round((float) $validated['basura_recolectada_ton'] * 1000, 2);

        DB::transaction(function () use ($model, $totalRealKg, $validated): void {
            $model->basura_recolectada_ton = round((float) $validated['basura_recolectada_ton'], 2);
            $model->save();

            $points = $model->puntosRecoleccion->values();
            $pointCount = $points->count();

            if ($pointCount === 0) {
                return;
            }

            $estimatedTotal = (float) $points->sum(fn ($point) => (float) $point->basura_estimada_kg);

            if ($estimatedTotal <= 0) {
                $equalKg = round($totalRealKg / $pointCount, 2);
                $assigned = 0.0;

                foreach ($points as $index => $point) {
                    $value = $index === $pointCount - 1
                        ? round($totalRealKg - $assigned, 2)
                        : $equalKg;

                    $point->update([
                        'basura_real_kg' => max($value, 0),
                    ]);

                    $assigned += $value;
                }

                return;
            }

            $assigned = 0.0;

            foreach ($points as $index => $point) {
                $value = $index === $pointCount - 1
                    ? round($totalRealKg - $assigned, 2)
                    : round(((float) $point->basura_estimada_kg / $estimatedTotal) * $totalRealKg, 2);

                $point->update([
                    'basura_real_kg' => max($value, 0),
                ]);

                $assigned += $value;
            }
        });

        return redirect()
            ->route('coordinator.collection-process')
            ->with('status', 'Basura recolectada registrada correctamente.');
    }

    public function setObservations(Request $request, int $programacion): RedirectResponse
    {
        $validated = $request->validate([
            'observaciones' => ['required', 'string', 'max:1000'],
        ]);

        $model = RutasProgramadas::query()->findOrFail($programacion);

        if ($model->observaciones !== null && trim((string) $model->observaciones) !== '') {
            return redirect()
                ->route('coordinator.collection-process')
                ->with('error', 'Las observaciones ya fueron registradas y no pueden modificarse.');
        }

        $model->observaciones = trim($validated['observaciones']);
        $model->save();

        return redirect()
            ->route('coordinator.collection-process')
            ->with('status', 'Observaciones registradas correctamente.');
    }

    public function storeIncident(Request $request, int $programacion): RedirectResponse
    {
        $validated = $request->validate([
            'descripcion' => ['required', 'string', 'max:1000'],
        ]);

        RutasProgramadas::query()->findOrFail($programacion);

        IncidenciasRecoleccion::query()->create([
            'id_programacion' => $programacion,
            'descripcion' => trim($validated['descripcion']),
            'fecha' => CarbonImmutable::now(),
        ]);

        return redirect()
            ->route('coordinator.collection-process')
            ->with('status', 'Incidencia registrada correctamente.');
    }

    private function normalizeState(string $state): string
    {
        $normalized = mb_strtolower(trim($state), 'UTF-8');

        return match ($normalized) {
            'programada' => 'programada',
            'en proceso', 'en_proceso' => 'en_proceso',
            'completada' => 'completada',
            'incompleta' => 'incompleta',
            default => 'programada',
        };
    }

    private function canTransition(string $currentState, string $newState): bool
    {
        if ($currentState === $newState) {
            return true;
        }

        return match ($currentState) {
            'programada' => $newState === 'en_proceso',
            'en_proceso' => in_array($newState, ['completada', 'incompleta'], true),
            'completada', 'incompleta' => false,
            default => false,
        };
    }
}
