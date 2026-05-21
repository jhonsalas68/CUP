<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gestion extends Model
{
    use SoftDeletes;

    protected $table = 'gestiones';

    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'activo',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
    ];

    public function scopeActiva($query)
    {
        return $query->where('activo', true);
    }

    public function cupos()
    {
        return $this->hasMany(Cupo::class, 'gestion_id');
    }

    public function postulantes()
    {
        return $this->hasMany(Postulante::class, 'gestion_id');
    }

    public function grupos()
    {
        return $this->hasMany(Grupo::class, 'gestion_id');
    }

    public function examenes()
    {
        return $this->hasMany(Examen::class, 'gestion_id');
    }
}
