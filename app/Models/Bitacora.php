<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model
{
    protected $table = 'bitacora';

    public $timestamps = false; // We only have created_at which is set via db default

    protected $fillable = [
        'user_id',
        'action',
        'objeto',
        'descripcion',
        'payload',
        'ip_address',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
