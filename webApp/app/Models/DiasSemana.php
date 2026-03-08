<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiasSemana extends Model
{
    use HasFactory;

    protected $table = 'dias_semana';

    protected $primaryKey = 'id_dia';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    public function rutasDias(): HasMany
    {
        return $this->hasMany(RutasDias::class, 'id_dia', 'id_dia');
    }
}
