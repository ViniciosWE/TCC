<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participante extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'funcao',
        'nome',
        'cpf',
        'status',
    ];


    /* Um participante pode possuir vários contratos com equipes */
    public function contratos()
    {
        return $this->hasMany(Contrato::class);
    }


    /* Um participante pode possuir vários eventos registrados em partidas */
    public function eventosPartidas()
    {
        return $this->hasMany(EventoPartida::class);
    }
}
