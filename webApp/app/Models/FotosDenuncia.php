<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FotosDenuncia extends Model
{
    use HasFactory;

    protected $table = 'fotos_denuncia';

    protected $primaryKey = 'id_foto';

    public $timestamps = false;

    protected $fillable = [
        'id_denuncia',
        'tipo',
        'ruta_archivo',
        'fecha',
    ];

    protected $casts = [
        'id_denuncia' => 'integer',
        'fecha' => 'datetime',
    ];

    public function denuncia(): BelongsTo
    {
        return $this->belongsTo(Denuncias::class, 'id_denuncia', 'id_denuncia');
    }
}
