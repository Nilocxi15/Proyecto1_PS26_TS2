<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Denuncias extends Model
{
    use HasFactory;

    protected $table = 'denuncias';

    protected $primaryKey = 'id_denuncia';

    public $timestamps = false;

    protected $fillable = [
        'nombre_denunciante',
        'telefono',
        'email',
        'descripcion',
        'latitud',
        'longitud',
        'tamano',
        'foto',
        'fecha',
        'estado',
    ];

    protected $casts = [
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'fecha' => 'datetime',
    ];

    public function historialEstados(): HasMany
    {
        return $this->hasMany(HistorialEstadoDenuncia::class, 'id_denuncia', 'id_denuncia');
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(FotosDenuncia::class, 'id_denuncia', 'id_denuncia');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionDenuncia::class, 'id_denuncia', 'id_denuncia');
    }
}
