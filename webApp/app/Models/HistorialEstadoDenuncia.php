<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialEstadoDenuncia extends Model
{
    use HasFactory;

    protected $table = 'historial_estado_denuncia';

    protected $primaryKey = 'id_historial';

    public $timestamps = false;

    protected $fillable = [
        'id_denuncia',
        'estado',
        'fecha',
        'id_usuario',
    ];

    protected $casts = [
        'id_denuncia' => 'integer',
        'id_usuario' => 'integer',
        'fecha' => 'datetime',
    ];

    public function denuncia(): BelongsTo
    {
        return $this->belongsTo(Denuncias::class, 'id_denuncia', 'id_denuncia');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuarios::class, 'id_usuario', 'id_usuario');
    }
}
