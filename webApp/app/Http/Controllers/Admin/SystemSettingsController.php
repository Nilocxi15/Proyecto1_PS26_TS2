<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PuntosVerdes;
use App\Models\Roles;
use App\Models\TiposMaterial;
use App\Models\Usuarios;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SystemSettingsController extends Controller
{
    // -----------------------------------------------------------------------
    // Index principal – carga todo lo necesario para la vista
    // -----------------------------------------------------------------------
    public function index(): View
    {
        $roles = Roles::query()->orderBy('nombre')->get();
        $materiales = TiposMaterial::query()->orderBy('nombre')->get();
        $puntosVerdes = PuntosVerdes::query()
            ->with('encargado:id_usuario,nombre')
            ->orderBy('nombre')
            ->get();
        $operadores = Usuarios::query()
            ->where('id_role', 3)
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get(['id_usuario', 'nombre', 'email']);

        return view('admin.system-settings', compact(
            'roles',
            'materiales',
            'puntosVerdes',
            'operadores',
        ));
    }

    // -----------------------------------------------------------------------
    // ROLES
    // -----------------------------------------------------------------------
    public function storeRole(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:60', 'unique:roles,nombre'],
        ]);

        Roles::query()->create(['nombre' => $validated['nombre']]);

        return redirect()->route('admin.system-settings')
            ->with('status', 'Rol creado correctamente.')
            ->with('active_tab', 'roles');
    }

    public function updateRole(Request $request, Roles $role): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => [
                'required', 'string', 'max:60',
                Rule::unique('roles', 'nombre')->ignore($role->id_role, 'id_role'),
            ],
        ]);

        $role->update(['nombre' => $validated['nombre']]);

        return redirect()->route('admin.system-settings')
            ->with('status', 'Rol actualizado correctamente.')
            ->with('active_tab', 'roles');
    }

    public function destroyRole(Roles $role): RedirectResponse
    {
        try {
            $role->delete();
        } catch (QueryException) {
            return redirect()->route('admin.system-settings')
                ->with('error', 'No se puede eliminar el rol porque tiene usuarios asignados.')
                ->with('active_tab', 'roles');
        }

        return redirect()->route('admin.system-settings')
            ->with('status', 'Rol eliminado correctamente.')
            ->with('active_tab', 'roles');
    }

    // -----------------------------------------------------------------------
    // TIPOS DE MATERIAL
    // -----------------------------------------------------------------------
    public function storeMaterial(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:80', 'unique:tipos_material,nombre'],
        ]);

        TiposMaterial::query()->create(['nombre' => $validated['nombre']]);

        return redirect()->route('admin.system-settings')
            ->with('status', 'Tipo de material creado correctamente.')
            ->with('active_tab', 'materiales');
    }

    public function updateMaterial(Request $request, TiposMaterial $material): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => [
                'required', 'string', 'max:80',
                Rule::unique('tipos_material', 'nombre')->ignore($material->id_material, 'id_material'),
            ],
        ]);

        $material->update(['nombre' => $validated['nombre']]);

        return redirect()->route('admin.system-settings')
            ->with('status', 'Tipo de material actualizado correctamente.')
            ->with('active_tab', 'materiales');
    }

    public function destroyMaterial(TiposMaterial $material): RedirectResponse
    {
        try {
            $material->delete();
        } catch (QueryException) {
            return redirect()->route('admin.system-settings')
                ->with('error', 'No se puede eliminar el tipo de material porque tiene contenedores asociados.')
                ->with('active_tab', 'materiales');
        }

        return redirect()->route('admin.system-settings')
            ->with('status', 'Tipo de material eliminado correctamente.')
            ->with('active_tab', 'materiales');
    }

    // -----------------------------------------------------------------------
    // PUNTOS VERDES
    // -----------------------------------------------------------------------
    public function storePuntoVerde(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre'       => ['required', 'string', 'max:120'],
            'direccion'    => ['required', 'string', 'max:255'],
            'latitud'      => ['required', 'numeric', 'between:-90,90'],
            'longitud'     => ['required', 'numeric', 'between:-180,180'],
            'capacidad_m3' => ['required', 'numeric', 'gt:0'],
            'horario'      => ['nullable', 'string', 'max:120'],
            'id_encargado' => ['nullable', 'integer', Rule::exists('usuarios', 'id_usuario')],
        ]);

        if (!empty($validated['id_encargado'])) {
            $this->assertIsOperator((int) $validated['id_encargado']);
        }

        PuntosVerdes::query()->create($validated);

        return redirect()->route('admin.system-settings')
            ->with('status', 'Punto verde creado correctamente.')
            ->with('active_tab', 'puntos');
    }

    public function updatePuntoVerde(Request $request, PuntosVerdes $puntoVerde): RedirectResponse
    {
        $validated = $request->validate([
            'nombre'       => ['required', 'string', 'max:120'],
            'direccion'    => ['required', 'string', 'max:255'],
            'latitud'      => ['required', 'numeric', 'between:-90,90'],
            'longitud'     => ['required', 'numeric', 'between:-180,180'],
            'capacidad_m3' => ['required', 'numeric', 'gt:0'],
            'horario'      => ['nullable', 'string', 'max:120'],
            'id_encargado' => ['nullable', 'integer', Rule::exists('usuarios', 'id_usuario')],
        ]);

        if (!empty($validated['id_encargado'])) {
            $this->assertIsOperator((int) $validated['id_encargado']);
        }

        $puntoVerde->update($validated);

        return redirect()->route('admin.system-settings')
            ->with('status', 'Punto verde actualizado correctamente.')
            ->with('active_tab', 'puntos');
    }

    public function destroyPuntoVerde(PuntosVerdes $puntoVerde): RedirectResponse
    {
        try {
            $puntoVerde->delete();
        } catch (QueryException) {
            return redirect()->route('admin.system-settings')
                ->with('error', 'No se puede eliminar el punto verde porque tiene contenedores asociados.')
                ->with('active_tab', 'puntos');
        }

        return redirect()->route('admin.system-settings')
            ->with('status', 'Punto verde eliminado correctamente.')
            ->with('active_tab', 'puntos');
    }

    private function assertIsOperator(int $idUsuario): void
    {
        $esOperador = Usuarios::query()
            ->where('id_usuario', $idUsuario)
            ->where('id_role', 3)
            ->exists();

        if (!$esOperador) {
            abort(422, 'El encargado debe tener el rol de Operador de Punto Verde.');
        }
    }
}
