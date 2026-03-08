<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PuntosVerdes extends Model
{
    use HasFactory;

    protected $table = 'puntos_verdes';

    protected $primaryKey = 'id_punto_verde';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'direccion',
        'latitud',
        'longitud',
        'capacidad_m3',
        'horario',
        'id_encargado',
    ];

    protected $casts = [
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'capacidad_m3' => 'decimal:2',
        'id_encargado' => 'integer',
    ];

    public function encargado(): BelongsTo
    {
        return $this->belongsTo(Usuarios::class, 'id_encargado', 'id_usuario');
    }

    public function contenedores(): HasMany
    {
        return $this->hasMany(Contenedores::class, 'id_punto_verde', 'id_punto_verde');
    }
}
