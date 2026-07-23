<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventoPartida extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'participante_id',
        'partida_id',
        'tipo',
        'tempo',
    ];

    /* Um evento pertence ao usuário que registrou */
    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    /* Um evento pertence ao participante envolvido */
    public function participante()
    {
        return $this->belongsTo(Participante::class);
    }

    /* Um evento pertence a uma partida */
    public function partida()
    {
        return $this->belongsTo(Partida::class);
    }
}
