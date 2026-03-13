<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Contenedores;
use App\Models\EntregasReciclaje;
use App\Models\VaciadoContenedores;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ContainerManagementController extends Controller
{
    public function index(): View
    {
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
    }

    public function storeEmptyRequest(int $contenedor): RedirectResponse
    {
        $contenedorModel = $this->findAuthorizedContainer($contenedor);

        DB::transaction(function () use ($contenedorModel): void {
            $porcentajeLlenado = (float) $contenedorModel->porcentaje_llenado;
            $capacidadKg = (float) $contenedorModel->capacidad_kg;
            $cantidadRetirada = round(($capacidadKg * $porcentajeLlenado) / 100, 2);

            VaciadoContenedores::query()->create([
                'id_contenedor' => $contenedorModel->id_contenedor,
                'fecha' => now(),
                'cantidad_retirada_kg' => $cantidadRetirada,
            ]);

            $contenedorModel->update([
                'porcentaje_llenado' => 0,
            ]);
        });

        return redirect()
            ->route('operator.containers')
            ->with('status', 'Solicitud de vaciado registrada correctamente.');
    }

    public function storeDelivery(Request $request, int $contenedor): RedirectResponse
    {
        $validated = $request->validate([
            'cantidad_kg' => ['required', 'numeric', 'gt:0'],
        ], [
            'cantidad_kg.required' => 'Debes ingresar una cantidad en kilogramos.',
            'cantidad_kg.numeric' => 'La cantidad debe ser numérica.',
            'cantidad_kg.gt' => 'La cantidad debe ser mayor que 0.',
        ]);

        $contenedorModel = $this->findAuthorizedContainer($contenedor);

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
            $contenedorModel->update([
                'porcentaje_llenado' => $capacidadKg > 0
                    ? round(($nuevoTotalKg / $capacidadKg) * 100, 2)
                    : 0,
            ]);
        });

        return redirect()
            ->route('operator.containers')
            ->with('status', 'Entrega de material registrada correctamente.');
    }

    private function findAuthorizedContainer(int $contenedor): Contenedores
    {
        $usuarioId = auth()->id();

        return Contenedores::query()
            ->where('id_contenedor', $contenedor)
            ->whereHas('puntoVerde', function ($query) use ($usuarioId): void {
                $query->where('id_encargado', $usuarioId);
            })
            ->firstOrFail();
    }
}
