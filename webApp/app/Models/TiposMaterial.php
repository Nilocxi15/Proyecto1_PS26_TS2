<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TiposMaterial extends Model
{
    use HasFactory;

    protected $table = 'tipos_material';

    protected $primaryKey = 'id_material';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    public function contenedores(): HasMany
    {
        return $this->hasMany(Contenedores::class, 'id_material', 'id_material');
    }
}
