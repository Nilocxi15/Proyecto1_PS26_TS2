<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zonas extends Model
{
    use HasFactory;

    protected $table = 'zonas';

    protected $primaryKey = 'id_zona';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'tipo',
        'latitud',
        'longitud',
    ];

    protected $casts = [
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
    ];

    public function rutas(): HasMany
    {
        return $this->hasMany(Rutas::class, 'id_zona', 'id_zona');
    }
}
