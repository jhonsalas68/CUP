<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    use LogsActivity;

    protected $table = 'aulas';

    protected $fillable = [
        'nombre',
        'capacidad',
        'ubicacion',
    ];

    public function horarios()
    {
        return $this->hasMany(Horario::class, 'aula_id');
    }
}
