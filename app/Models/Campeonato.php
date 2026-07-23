<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campeonato extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'minimo_jogadores_equipes',
        'maximo_equipes',
        'nome',
        'categoria',
        'data_inicio',
        'data_fim',
        'status',
    ];

    /* Um campeonato pertence ao usuário que criou */
    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    /* Um campeonato pode possuir várias notícias */
    public function noticias()
    {
        return $this->hasMany(Noticia::class);
    }

    /* Um campeonato possui várias partidas */
    public function partidas()
    {
        return $this->hasMany(Partida::class);
    }

    /* Um campeonato possui várias inscrições de equipes */
    public function inscricoes()
    {
        return $this->hasMany(Inscricao::class);
    }
}
