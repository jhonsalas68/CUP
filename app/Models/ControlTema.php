<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class ControlTema extends Model
{
    use LogsActivity;

    protected $table = 'control_temas';

    protected $fillable = [
        'grupo_id',
        'fecha',
        'tema',
        'descripcion',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }
}
