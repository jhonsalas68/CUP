<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'titulo',
        'mensaje',
        'leido',
        'created_at',
    ];

    protected $casts = [
        'leido' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function enviar($userId, $titulo, $mensaje)
    {
        return self::create([
            'user_id' => $userId,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'leido' => false,
        ]);
    }
}
