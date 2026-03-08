<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Camiones extends Model
{
    use HasFactory;

    protected $table = 'camiones';

    protected $primaryKey = 'id_camion';

    public $timestamps = false;

    protected $fillable = [
        'placa',
        'capacidad_toneladas',
        'estado',
        'id_conductor',
    ];

    protected $casts = [
        'capacidad_toneladas' => 'decimal:2',
        'id_conductor' => 'integer',
    ];

    public function conductor(): BelongsTo
    {
        return $this->belongsTo(Usuarios::class, 'id_conductor', 'id_usuario');
    }

    public function rutasProgramadas(): HasMany
    {
        return $this->hasMany(RutasProgramadas::class, 'id_camion', 'id_camion');
    }
}
