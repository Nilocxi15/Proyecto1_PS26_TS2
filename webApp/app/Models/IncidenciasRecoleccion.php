<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidenciasRecoleccion extends Model
{
    use HasFactory;

    protected $table = 'incidencias_recoleccion';

    protected $primaryKey = 'id_incidencia';

    public $timestamps = false;

    protected $fillable = [
        'id_programacion',
        'descripcion',
        'fecha',
    ];

    protected $casts = [
        'id_programacion' => 'integer',
        'fecha' => 'datetime',
    ];

    public function programacion(): BelongsTo
    {
        return $this->belongsTo(RutasProgramadas::class, 'id_programacion', 'id_programacion');
    }
}
