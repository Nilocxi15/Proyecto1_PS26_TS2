<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rutas extends Model
{
    use HasFactory;

    protected $table = 'rutas';

    protected $primaryKey = 'id_ruta';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'id_zona',
        'lat_inicio',
        'lon_inicio',
        'lat_fin',
        'lon_fin',
        'distancia_km',
        'horario_inicio',
        'horario_fin',
        'tipo_residuo',
    ];

    protected $casts = [
        'id_zona' => 'integer',
        'lat_inicio' => 'decimal:7',
        'lon_inicio' => 'decimal:7',
        'lat_fin' => 'decimal:7',
        'lon_fin' => 'decimal:7',
        'distancia_km' => 'decimal:2',
    ];

    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zonas::class, 'id_zona', 'id_zona');
    }

    public function dias(): BelongsToMany
    {
        return $this->belongsToMany(DiasSemana::class, 'rutas_dias', 'id_ruta', 'id_dia');
    }

    public function coordenadas(): HasMany
    {
        return $this->hasMany(CoordenadasRuta::class, 'id_ruta', 'id_ruta');
    }

    public function programaciones(): HasMany
    {
        return $this->hasMany(RutasProgramadas::class, 'id_ruta', 'id_ruta');
    }
}
