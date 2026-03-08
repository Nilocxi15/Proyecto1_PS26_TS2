<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoordenadasRuta extends Model
{
    use HasFactory;

    protected $table = 'coordenadas_ruta';

    protected $primaryKey = 'id_coordenada';

    public $timestamps = false;

    protected $fillable = [
        'id_ruta',
        'orden',
        'latitud',
        'longitud',
    ];

    protected $casts = [
        'id_ruta' => 'integer',
        'orden' => 'integer',
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
    ];

    public function ruta(): BelongsTo
    {
        return $this->belongsTo(Rutas::class, 'id_ruta', 'id_ruta');
    }
}
