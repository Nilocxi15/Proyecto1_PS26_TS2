<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RutasProgramadas extends Model
{
    use HasFactory;

    protected $table = 'rutas_programadas';

    protected $primaryKey = 'id_programacion';

    public $timestamps = false;

    protected $fillable = [
        'id_ruta',
        'id_camion',
        'fecha',
        'estado',
        'hora_inicio',
        'hora_fin',
        'basura_recolectada_ton',
        'observaciones',
    ];

    protected $casts = [
        'id_ruta' => 'integer',
        'id_camion' => 'integer',
        'fecha' => 'date',
        'hora_inicio' => 'datetime',
        'hora_fin' => 'datetime',
        'basura_recolectada_ton' => 'decimal:2',
    ];

    public function ruta(): BelongsTo
    {
        return $this->belongsTo(Rutas::class, 'id_ruta', 'id_ruta');
    }

    public function camion(): BelongsTo
    {
        return $this->belongsTo(Camiones::class, 'id_camion', 'id_camion');
    }

    public function incidencias(): HasMany
    {
        return $this->hasMany(IncidenciasRecoleccion::class, 'id_programacion', 'id_programacion');
    }

    public function puntosRecoleccion(): HasMany
    {
        return $this->hasMany(PuntosRecoleccion::class, 'id_programacion', 'id_programacion');
    }
}
