<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuarios extends Authenticatable
{
    use HasFactory;

    protected $table = 'usuarios';

    protected $primaryKey = 'id_usuario';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'password_hash',
        'foto_perfil',
        'id_role',
        'estado',
        'fecha_creacion',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'id_role' => 'integer',
        'fecha_creacion' => 'datetime',
    ];

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function getAuthIdentifierName(): string
    {
        return $this->primaryKey;
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Roles::class, 'id_role', 'id_role');
    }

    public function auditorias(): HasMany
    {
        return $this->hasMany(Auditoria::class, 'id_usuario', 'id_usuario');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificaciones::class, 'id_usuario', 'id_usuario');
    }

    public function camionesConducidos(): HasMany
    {
        return $this->hasMany(Camiones::class, 'id_conductor', 'id_usuario');
    }

    public function puntosVerdesEncargados(): HasMany
    {
        return $this->hasMany(PuntosVerdes::class, 'id_encargado', 'id_usuario');
    }

    public function historialEstadosDenuncia(): HasMany
    {
        return $this->hasMany(HistorialEstadoDenuncia::class, 'id_usuario', 'id_usuario');
    }

    public function cuadrillaIntegrantes(): HasMany
    {
        return $this->hasMany(CuadrillaIntegrantes::class, 'id_usuario', 'id_usuario');
    }
}
