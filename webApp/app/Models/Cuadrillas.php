<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cuadrillas extends Model
{
    use HasFactory;

    protected $table = 'cuadrillas';

    protected $primaryKey = 'id_cuadrilla';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
    ];

    public function integrantes(): HasMany
    {
        return $this->hasMany(CuadrillaIntegrantes::class, 'id_cuadrilla', 'id_cuadrilla');
    }

    public function asignacionesDenuncia(): HasMany
    {
        return $this->hasMany(AsignacionDenuncia::class, 'id_cuadrilla', 'id_cuadrilla');
    }
}
