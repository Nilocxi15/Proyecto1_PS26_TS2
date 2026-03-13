<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Denuncias;
use App\Models\HistorialEstadoDenuncia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IncidentManagementController extends Controller
{
    private const ALLOWED_STATES = [
        'recibida',
        'en_revision',
        'asignada',
        'en_atencion',
        'atendida',
        'cerrada',
    ];

    public function index(): View
    {
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
    }

    public function updateStatus(Request $request, int $denuncia): RedirectResponse
    {
        $validated = $request->validate([
            'estado' => ['required', 'string', Rule::in(self::ALLOWED_STATES)],
        ]);

        $denunciaModel = Denuncias::query()->findOrFail($denuncia);

        $estadoActual = $this->normalizeStatusKey($denunciaModel->estado);
        $indiceActual = array_search($estadoActual, self::ALLOWED_STATES, true);
        $indiceNuevo = array_search($validated['estado'], self::ALLOWED_STATES, true);

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
    }

    private function normalizeStatusKey(?string $value): string
    {
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
    }
}
