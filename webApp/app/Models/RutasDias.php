<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RutasDias extends Model
{
    use HasFactory;

    protected $table = 'rutas_dias';

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_ruta',
        'id_dia',
    ];

    protected $casts = [
        'id_ruta' => 'integer',
        'id_dia' => 'integer',
    ];

    public function ruta(): BelongsTo
    {
        return $this->belongsTo(Rutas::class, 'id_ruta', 'id_ruta');
    }

    public function dia(): BelongsTo
    {
        return $this->belongsTo(DiasSemana::class, 'id_dia', 'id_dia');
    }
}
