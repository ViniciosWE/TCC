<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partida extends Model
{
    use HasFactory;

    protected $fillable = [
        'campeonato_id',
        'mandante_id',
        'visitante_id',
        'gols_mandante',
        'gols_visitante',
        'data_hora',
        'status',
    ];

    /* Uma partida pertence a um campeonato */
    public function campeonato()
    {
        return $this->belongsTo(Campeonato::class);
    }

    /* A partida possui uma equipe mandante */
    public function mandante()
    {
        return $this->belongsTo(Equipe::class, 'mandante_id');
    }

    /* A partida possui uma equipe visitante */
    public function visitante()
    {
        return $this->belongsTo(Equipe::class, 'visitante_id');
    }

    /* Uma partida pode possuir vários eventos */
    public function eventos()
    {
        return $this->hasMany(EventoPartida::class);
    }
}
