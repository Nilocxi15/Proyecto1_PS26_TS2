<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuadrillaIntegrantes extends Model
{
    use HasFactory;

    protected $table = 'cuadrilla_integrantes';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'id_cuadrilla',
        'id_usuario',
        'rol',
    ];

    protected $casts = [
        'id_cuadrilla' => 'integer',
        'id_usuario' => 'integer',
    ];

    public function cuadrilla(): BelongsTo
    {
        return $this->belongsTo(Cuadrillas::class, 'id_cuadrilla', 'id_cuadrilla');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuarios::class, 'id_usuario', 'id_usuario');
    }
}
