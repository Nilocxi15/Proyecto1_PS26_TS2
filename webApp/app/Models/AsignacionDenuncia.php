<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionDenuncia extends Model
{
    use HasFactory;

    protected $table = 'asignacion_denuncia';

    protected $primaryKey = 'id_asignacion';

    public $timestamps = false;

    protected $fillable = [
        'id_denuncia',
        'id_cuadrilla',
        'fecha_programada',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'recursos_estimados',
    ];

    protected $casts = [
        'id_denuncia' => 'integer',
        'id_cuadrilla' => 'integer',
        'fecha_programada' => 'date',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function denuncia(): BelongsTo
    {
        return $this->belongsTo(Denuncias::class, 'id_denuncia', 'id_denuncia');
    }

    public function cuadrilla(): BelongsTo
    {
        return $this->belongsTo(Cuadrillas::class, 'id_cuadrilla', 'id_cuadrilla');
    }
}
