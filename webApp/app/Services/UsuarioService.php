<?php

namespace App\Services;

use App\Models\Usuarios;
use App\Models\Roles;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;

class UsuarioService
{
    /**
     * Registrar un nuevo ciudadano
     *
     * @param array $datos
     * @return Usuarios
     * @throws Exception
     */
    public function registrarCiudadano(array $datos): Usuarios
    {
        DB::beginTransaction();

        try {
            // Obtener el id del rol 'ciudadano'
            $rolCiudadano = Roles::where('nombre', 'ciudadano')->first();
            
            if (!$rolCiudadano) {
                throw new Exception('El rol de ciudadano no existe en el sistema');
            }

            // Crear el usuario
            $usuario = Usuarios::create([
                'nombre' => $datos['nombre'],
                'email' => $datos['email'],
                'telefono' => $datos['telefono'],
                'password_hash' => Hash::make($datos['password']),
                'id_role' => $rolCiudadano->id_role,
                'estado' => 'activo',
                'fecha_creacion' => now(),
            ]);

            DB::commit();

            return $usuario;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Verificar si un email ya está registrado
     * Nota: Es una verificación "extra" porque en la db ya hay restricción de unicidad
     * @param string $email
     * @return bool
     */
    public function emailExiste(string $email): bool
    {
        return Usuarios::where('email', $email)->exists();
    }

    /**
     * Obtener usuario por email
     *
     * @param string $email
     * @return Usuarios|null
     */
    public function obtenerPorEmail(string $email): ?Usuarios
    {
        return Usuarios::where('email', $email)->first();
    }
}
