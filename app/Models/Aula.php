<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

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
