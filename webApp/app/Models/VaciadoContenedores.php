<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VaciadoContenedores extends Model
{
    use HasFactory;

    protected $table = 'vaciado_contenedores';

    protected $primaryKey = 'id_vaciado';

    public $timestamps = false;

    protected $fillable = [
        'id_contenedor',
        'fecha',
        'cantidad_retirada_kg',
    ];

    protected $casts = [
        'id_contenedor' => 'integer',
        'fecha' => 'datetime',
        'cantidad_retirada_kg' => 'decimal:2',
    ];

    public function contenedor(): BelongsTo
    {
        return $this->belongsTo(Contenedores::class, 'id_contenedor', 'id_contenedor');
    }
}
