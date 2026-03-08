<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auditoria extends Model
{
    use HasFactory;

    protected $table = 'auditoria';

    protected $primaryKey = 'id_auditoria';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'accion',
        'tabla_afectada',
        'id_registro',
        'fecha',
        'detalle',
    ];

    protected $casts = [
        'id_usuario' => 'integer',
        'id_registro' => 'integer',
        'fecha' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuarios::class, 'id_usuario', 'id_usuario');
    }
}
