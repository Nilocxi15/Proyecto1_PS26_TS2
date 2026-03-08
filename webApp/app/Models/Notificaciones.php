<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificaciones extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

    protected $primaryKey = 'id_notificacion';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'mensaje',
        'tipo',
        'leida',
        'fecha',
    ];

    protected $casts = [
        'id_usuario' => 'integer',
        'leida' => 'boolean',
        'fecha' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuarios::class, 'id_usuario', 'id_usuario');
    }
}
