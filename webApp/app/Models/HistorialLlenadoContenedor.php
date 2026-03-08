<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialLlenadoContenedor extends Model
{
    use HasFactory;

    protected $table = 'historial_llenado_contenedor';

    protected $primaryKey = 'id_historial';

    public $timestamps = false;

    protected $fillable = [
        'id_contenedor',
        'porcentaje',
        'fecha',
    ];

    protected $casts = [
        'id_contenedor' => 'integer',
        'porcentaje' => 'decimal:2',
        'fecha' => 'datetime',
    ];

    public function contenedor(): BelongsTo
    {
        return $this->belongsTo(Contenedores::class, 'id_contenedor', 'id_contenedor');
    }
}
