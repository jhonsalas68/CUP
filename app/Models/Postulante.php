<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Postulante extends Model
{
    use SoftDeletes;

    protected $table = 'postulantes';

    protected static function booted()
    {
        static::saving(function ($postulante) {
            if (empty($postulante->nombres_apellidos) && $postulante->user_id) {
                $postulante->nombres_apellidos = $postulante->user?->name;
            }
        });
    }

    protected $fillable = [
        'user_id',
        'nombres_apellidos',
        'ci',
        'telefono',
        'fecha_nacimiento',
        'sexo',
        'direccion',
        'colegio_procedencia',
        'ciudad',
        'carrera_primera_opcion_id',
        'carrera_segunda_opcion_id',
        'gestion_id',
        'estado_admision',
        'nota_final',
        'ci_vigente',
        'titulo_bachiller',
        'libreta_legalizada',
        'habilitado',
        'mensaje_documentos',
        'pago_realizado',
        'pago_matricula_realizado',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'nota_final' => 'float',
        'ci_vigente' => 'boolean',
        'titulo_bachiller' => 'boolean',
        'libreta_legalizada' => 'boolean',
        'habilitado' => 'boolean',
        'pago_realizado' => 'boolean',
        'pago_matricula_realizado' => 'boolean',
    ];

    public function scopeAdmitidos($query)
    {
        return $query->whereIn('estado_admision', ['admitido_primera_opcion', 'admitido_segunda_opcion']);
    }

    public function scopeAprobados($query)
    {
        return $query->where('nota_final', '>=', 60.00);
    }

    public function scopePendientes($query)
    {
        return $query->where('estado_admision', 'pendiente');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function carreraPrimeraOpn()
    {
        return $this->belongsTo(Carrera::class, 'carrera_primera_opcion_id');
    }

    public function carreraSegundaOpn()
    {
        return $this->belongsTo(Carrera::class, 'carrera_segunda_opcion_id');
    }

    public function gestion()
    {
        return $this->belongsTo(Gestion::class, 'gestion_id');
    }

    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'postulante_grupo', 'postulante_id', 'grupo_id')
                    ->withTimestamps();
    }

    public function notas()
    {
        return $this->hasMany(Nota::class, 'postulante_id');
    }
}
