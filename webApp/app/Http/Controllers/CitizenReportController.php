<?php

namespace App\Http\Controllers;

use App\Models\Denuncias;
use App\Models\HistorialEstadoDenuncia;
use App\Services\CloudinaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CitizenReportController extends Controller
{
    public function __construct(private readonly CloudinaryService $cloudinaryService)
    {
    }

    public function index(): View
    {
        $usuario = Auth::user();

        $denuncias = Denuncias::query()
            ->where(function ($query) use ($usuario): void {
                $query->where('email', $usuario->email);

                if (!empty($usuario->telefono)) {
                    $query->orWhere('telefono', $usuario->telefono);
                }
            })
            ->orderByDesc('fecha')
            ->orderByDesc('id_denuncia')
            ->get()
            ->each(function ($denuncia): void {
                if (is_string($denuncia->fecha)) {
                    $denuncia->fecha = Carbon::parse($denuncia->fecha);
                }
            });

        return view('citizen.reports', compact('denuncias'));
    }

    public function store(): RedirectResponse
    {
        $usuario = Auth::user();

        $validated = request()->validate([
            'ubicacion' => ['required', 'string', 'max:255'],
            'descripcion' => ['required', 'string', 'max:1500'],
            'tamano' => ['required', 'in:Pequeno,Mediano,Grande'],
            'fecha' => ['required', 'date'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180'],
            'foto' => ['nullable', 'image', 'max:5120'],
        ], [
            'tamano.in' => 'El tamano debe ser Pequeno, Mediano o Grande.',
        ]);

        $fotoPath = null;
        $cloudinaryPublicId = null;
        $isLocalFallback = false;

        if (request()->hasFile('foto')) {
            try {
                $upload = $this->cloudinaryService->uploadImage(request()->file('foto'), 'denuncias');
                $fotoPath = $upload['url'];
                $cloudinaryPublicId = $upload['public_id'];
            } catch (\Throwable $exception) {
                Log::warning('Cloudinary upload failed, using local storage fallback.', [
                    'message' => $exception->getMessage(),
                ]);

                $fotoPath = request()->file('foto')->store('denuncias', 'public');
                $isLocalFallback = true;
            }
        }

        try {
            DB::beginTransaction();

            $denuncia = Denuncias::query()->create([
                'nombre_denunciante' => $usuario->nombre,
                'telefono' => $usuario->telefono,
                'email' => $usuario->email,
                'descripcion' => $validated['descripcion'] . ' | Ubicacion: ' . $validated['ubicacion'],
                'latitud' => $validated['latitud'] ?? null,
                'longitud' => $validated['longitud'] ?? null,
                'tamano' => $validated['tamano'],
                'foto' => $fotoPath,
                'fecha' => $validated['fecha'],
                'estado' => 'Recibida',
            ]);

            HistorialEstadoDenuncia::query()->create([
                'id_denuncia' => $denuncia->id_denuncia,
                'estado' => 'Recibida',
                'fecha' => now(),
                'id_usuario' => $usuario->id_usuario,
            ]);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();

            if ($cloudinaryPublicId) {
                $this->cloudinaryService->deleteImage($cloudinaryPublicId);
            }

            if ($isLocalFallback && $fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }

            return back()
                ->withInput()
                ->with('error', 'No se pudo registrar la denuncia. Intenta nuevamente.');
        }

        return redirect()
            ->route('report-citizen')
            ->with('success', 'Denuncia registrada exitosamente.');
    }
}
