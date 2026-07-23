<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class ReclamoNota extends Model
{
    use LogsActivity;

    protected $table = 'reclamos_notas';

    protected $fillable = [
        'postulante_id',
        'examen_id',
        'descripcion',
        'archivo_adjunto',
        'estado',
        'respuesta_docente',
        'docente_id',
        'nota_anterior',
        'nota_nueva',
    ];

    public function postulante()
    {
        return $this->belongsTo(Postulante::class, 'postulante_id');
    }

    public function examen()
    {
        return $this->belongsTo(Examen::class, 'examen_id');
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }
}
