<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntregasReciclaje extends Model
{
    use HasFactory;

    protected $table = 'entregas_reciclaje';

    protected $primaryKey = 'id_entrega';

    public $timestamps = false;

    protected $fillable = [
        'id_contenedor',
        'ciudadano_codigo',
        'cantidad_kg',
        'fecha',
    ];

    protected $casts = [
        'id_contenedor' => 'integer',
        'cantidad_kg' => 'decimal:2',
        'fecha' => 'datetime',
    ];

    public function contenedor(): BelongsTo
    {
        return $this->belongsTo(Contenedores::class, 'id_contenedor', 'id_contenedor');
    }
}
