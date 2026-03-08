<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Roles extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $primaryKey = 'id_role';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuarios::class, 'id_role', 'id_role');
    }
}
