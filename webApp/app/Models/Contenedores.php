<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contenedores extends Model
{
    use HasFactory;

    protected $table = 'contenedores';

    protected $primaryKey = 'id_contenedor';

    public $timestamps = false;

    protected $fillable = [
        'id_punto_verde',
        'id_material',
        'capacidad_kg',
        'porcentaje_llenado',
    ];

    protected $casts = [
        'id_punto_verde' => 'integer',
        'id_material' => 'integer',
        'capacidad_kg' => 'decimal:2',
        'porcentaje_llenado' => 'decimal:2',
    ];

    public function puntoVerde(): BelongsTo
    {
        return $this->belongsTo(PuntosVerdes::class, 'id_punto_verde', 'id_punto_verde');
    }

    public function tipoMaterial(): BelongsTo
    {
        return $this->belongsTo(TiposMaterial::class, 'id_material', 'id_material');
    }

    public function historialLlenado(): HasMany
    {
        return $this->hasMany(HistorialLlenadoContenedor::class, 'id_contenedor', 'id_contenedor');
    }

    public function entregasReciclaje(): HasMany
    {
        return $this->hasMany(EntregasReciclaje::class, 'id_contenedor', 'id_contenedor');
    }

    public function vaciados(): HasMany
    {
        return $this->hasMany(VaciadoContenedores::class, 'id_contenedor', 'id_contenedor');
    }
}
