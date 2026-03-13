<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\CoordenadasRuta;
use App\Models\DiasSemana;
use App\Models\Rutas;
use App\Models\RutasDias;
use App\Models\Zonas;
use App\Services\RoutePlannerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RouteManagementController extends Controller
{
    public function __construct(private readonly RoutePlannerService $routePlannerService)
    {
    }

    public function index(): View
    {
        $rutas = Rutas::query()
            ->with([
                'zona:id_zona,nombre',
                'dias:id_dia,nombre',
                'coordenadas' => function ($query): void {
                    $query->orderBy('orden');
                },
            ])
            ->orderBy('nombre')
            ->orderBy('id_ruta')
            ->paginate(10)
            ->withQueryString();

        $rutasMapa = Rutas::query()
            ->with([
                'coordenadas' => function ($query): void {
                    $query->orderBy('orden');
                },
            ])
            ->orderBy('nombre')
            ->orderBy('id_ruta')
            ->get();

        $zonas = Zonas::query()
            ->orderBy('nombre')
            ->get(['id_zona', 'nombre', 'latitud', 'longitud']);

        $diasSemana = DiasSemana::query()
            ->orderBy('id_dia')
            ->get(['id_dia', 'nombre']);

        return view('coordinator.routes', [
            'rutas' => $rutas,
            'rutasMapa' => $rutasMapa,
            'zonas' => $zonas,
            'diasSemana' => $diasSemana,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRoutePayload($request);
        $coordinates = $this->resolveCoordinates($validated['coordenadas_json']);

        if ($coordinates === null) {
            return $this->invalidCoordinatesResponse();
        }

        $start = $coordinates->first();
        $end = $coordinates->last();

        DB::transaction(function () use ($validated, $coordinates, $start, $end): void {
            $ruta = Rutas::query()->create([
                'nombre' => $validated['nombre'],
                'id_zona' => $validated['id_zona'] ?? null,
                'lat_inicio' => $start['lat'],
                'lon_inicio' => $start['lng'],
                'lat_fin' => $end['lat'],
                'lon_fin' => $end['lng'],
                'distancia_km' => $this->routePlannerService->calculateRouteDistance($coordinates->all()),
                'horario_inicio' => $validated['horario_inicio'],
                'horario_fin' => $validated['horario_fin'],
                'tipo_residuo' => $validated['tipo_residuo'],
            ]);

            foreach ($coordinates as $index => $point) {
                CoordenadasRuta::query()->create([
                    'id_ruta' => $ruta->id_ruta,
                    'orden' => $index + 1,
                    'latitud' => $point['lat'],
                    'longitud' => $point['lng'],
                ]);
            }

            $ruta->dias()->sync($validated['dias'] ?? []);
        });

        return redirect()
            ->route('coordinator.routes')
            ->with('status', 'Ruta creada correctamente.');
    }

    public function update(Request $request, int $ruta): RedirectResponse
    {
        $validated = $this->validateRoutePayload($request);
        $coordinates = $this->resolveCoordinates($validated['coordenadas_json']);

        if ($coordinates === null) {
            return $this->invalidCoordinatesResponse();
        }

        $start = $coordinates->first();
        $end = $coordinates->last();
        $rutaModel = Rutas::query()->findOrFail($ruta);

        DB::transaction(function () use ($rutaModel, $validated, $coordinates, $start, $end): void {
            $rutaModel->fill([
                'nombre' => $validated['nombre'],
                'id_zona' => $validated['id_zona'] ?? null,
                'lat_inicio' => $start['lat'],
                'lon_inicio' => $start['lng'],
                'lat_fin' => $end['lat'],
                'lon_fin' => $end['lng'],
                'distancia_km' => $this->routePlannerService->calculateRouteDistance($coordinates->all()),
                'horario_inicio' => $validated['horario_inicio'],
                'horario_fin' => $validated['horario_fin'],
                'tipo_residuo' => $validated['tipo_residuo'],
            ]);
            $rutaModel->save();

            CoordenadasRuta::query()
                ->where('id_ruta', $rutaModel->id_ruta)
                ->delete();

            foreach ($coordinates as $index => $point) {
                CoordenadasRuta::query()->create([
                    'id_ruta' => $rutaModel->id_ruta,
                    'orden' => $index + 1,
                    'latitud' => $point['lat'],
                    'longitud' => $point['lng'],
                ]);
            }

            $rutaModel->dias()->sync($validated['dias'] ?? []);
        });

        return redirect()
            ->route('coordinator.routes')
            ->with('status', 'Ruta modificada correctamente.');
    }

    public function destroy(int $ruta): RedirectResponse
    {
        $rutaModel = Rutas::query()->findOrFail($ruta);

        DB::transaction(function () use ($rutaModel): void {
            CoordenadasRuta::query()
                ->where('id_ruta', $rutaModel->id_ruta)
                ->delete();

            RutasDias::query()
                ->where('id_ruta', $rutaModel->id_ruta)
                ->delete();

            $rutaModel->delete();
        });

        return redirect()
            ->route('coordinator.routes')
            ->with('status', 'Ruta eliminada correctamente.');
    }

    public function storeZone(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'tipo' => ['required', 'string', 'max:50'],
            'latitud' => ['required', 'numeric', 'between:-90,90'],
            'longitud' => ['required', 'numeric', 'between:-180,180'],
        ]);

        Zonas::query()->create([
            'nombre' => $validated['nombre'],
            'tipo' => $validated['tipo'],
            'latitud' => round((float) $validated['latitud'], 7),
            'longitud' => round((float) $validated['longitud'], 7),
        ]);

        return redirect()
            ->route('coordinator.routes')
            ->with('status', 'Zona creada correctamente.');
    }

    private function validateRoutePayload(Request $request): array
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'id_zona' => ['nullable', 'integer', Rule::exists('zonas', 'id_zona')],
            'tipo_residuo' => ['required', 'string', 'max:50'],
            'horario_inicio' => ['required', 'string', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'],
            'horario_fin' => ['required', 'string', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'],
            'dias' => ['nullable', 'array'],
            'dias.*' => ['integer', Rule::exists('dias_semana', 'id_dia')],
            'coordenadas_json' => ['required', 'string'],
        ]);

        $validated['horario_inicio'] = $this->normalizeTimeValue($validated['horario_inicio']);
        $validated['horario_fin'] = $this->normalizeTimeValue($validated['horario_fin']);

        return $validated;
    }

    private function normalizeTimeValue(string $value): string
    {
        $parts = explode(':', $value);

        if (count($parts) < 2) {
            return $value;
        }

        return sprintf('%02d:%02d', (int) $parts[0], (int) $parts[1]);
    }

    private function resolveCoordinates(string $coordinatesJson): ?\Illuminate\Support\Collection
    {
        $decodedCoordinates = json_decode($coordinatesJson, true);

        if (!is_array($decodedCoordinates) || count($decodedCoordinates) < 2) {
            return null;
        }

        $coordinates = $this->routePlannerService->normalizeCoordinates($decodedCoordinates);

        return $coordinates->count() >= 2 ? $coordinates : null;
    }

    private function invalidCoordinatesResponse(): RedirectResponse
    {
        return redirect()
            ->route('coordinator.routes')
            ->withErrors(['coordenadas_json' => 'Debes definir al menos dos puntos válidos en el mapa para la ruta.'])
            ->withInput();
    }
}
