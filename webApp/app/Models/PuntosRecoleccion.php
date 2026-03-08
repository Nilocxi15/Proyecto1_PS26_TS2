<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PuntosRecoleccion extends Model
{
    use HasFactory;

    protected $table = 'puntos_recoleccion';

    protected $primaryKey = 'id_punto';

    public $timestamps = false;

    protected $fillable = [
        'id_programacion',
        'latitud',
        'longitud',
        'basura_estimada_kg',
        'basura_real_kg',
    ];

    protected $casts = [
        'id_programacion' => 'integer',
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'basura_estimada_kg' => 'decimal:2',
        'basura_real_kg' => 'decimal:2',
    ];

    public function programacion(): BelongsTo
    {
        return $this->belongsTo(RutasProgramadas::class, 'id_programacion', 'id_programacion');
    }
}
