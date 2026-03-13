<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Roles;
use App\Models\Usuarios;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $roleId = $request->query('role');
        $status = trim((string) $request->query('status', ''));

        $users = Usuarios::query()
            ->with('rol:id_role,nombre')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery->where('nombre', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('telefono', 'like', "%{$search}%");
                });
            })
            ->when(is_numeric($roleId) && (int) $roleId > 0, function ($query) use ($roleId): void {
                $query->where('id_role', (int) $roleId);
            })
            ->when(in_array($status, ['activo', 'inactivo'], true), function ($query) use ($status): void {
                $query->where('estado', $status);
            })
            ->orderByDesc('id_usuario')
            ->paginate(10)
            ->withQueryString();

        $roles = Roles::query()
            ->orderBy('nombre')
            ->get(['id_role', 'nombre']);

        return view('admin.users-management', [
            'users' => $users,
            'roles' => $roles,
            'filters' => [
                'search' => $search,
                'role' => is_numeric($roleId) ? (int) $roleId : null,
                'status' => $status,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:usuarios,email'],
            'telefono' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'id_role' => ['required', 'integer', Rule::exists('roles', 'id_role')],
            'estado' => ['required', 'string', Rule::in(['activo', 'inactivo'])],
        ]);

        Usuarios::query()->create([
            'nombre' => $validated['nombre'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'],
            'password_hash' => Hash::make($validated['password']),
            'id_role' => (int) $validated['id_role'],
            'estado' => $validated['estado'],
            'fecha_creacion' => now(),
        ]);

        return redirect()
            ->route('admin.users')
            ->with('status', 'Usuario creado correctamente.');
    }

    public function update(Request $request, Usuarios $usuario): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('usuarios', 'email')->ignore($usuario->id_usuario, 'id_usuario'),
            ],
            'telefono' => ['required', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'id_role' => ['required', 'integer', Rule::exists('roles', 'id_role')],
            'estado' => ['required', 'string', Rule::in(['activo', 'inactivo'])],
        ]);

        if (Auth::id() === $usuario->id_usuario && $validated['estado'] === 'inactivo') {
            return redirect()
                ->route('admin.users')
                ->with('error', 'No puedes inactivar tu propio usuario.');
        }

        $dataToUpdate = [
            'nombre' => $validated['nombre'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'],
            'id_role' => (int) $validated['id_role'],
            'estado' => $validated['estado'],
        ];

        if (!empty($validated['password'])) {
            $dataToUpdate['password_hash'] = Hash::make($validated['password']);
        }

        $usuario->update($dataToUpdate);

        return redirect()
            ->route('admin.users')
            ->with('status', 'Usuario actualizado correctamente.');
    }

    public function updateStatus(Request $request, Usuarios $usuario): RedirectResponse
    {
        $validated = $request->validate([
            'estado' => ['required', 'string', Rule::in(['activo', 'inactivo'])],
        ]);

        if (Auth::id() === $usuario->id_usuario && $validated['estado'] === 'inactivo') {
            return redirect()
                ->route('admin.users')
                ->with('error', 'No puedes inactivar tu propio usuario.');
        }

        $usuario->update([
            'estado' => $validated['estado'],
        ]);

        return redirect()
            ->route('admin.users')
            ->with('status', 'Estado de usuario actualizado correctamente.');
    }

    public function destroy(Usuarios $usuario): RedirectResponse
    {
        if (Auth::id() === $usuario->id_usuario) {
            return redirect()
                ->route('admin.users')
                ->with('error', 'No puedes eliminar tu propio usuario.');
        }

        try {
            $usuario->delete();
        } catch (QueryException) {
            return redirect()
                ->route('admin.users')
                ->with('error', 'No se puede eliminar el usuario porque tiene registros relacionados.');
        }

        return redirect()
            ->route('admin.users')
            ->with('status', 'Usuario eliminado correctamente.');
    }
}
