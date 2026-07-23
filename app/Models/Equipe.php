<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'escudo',
        'sigla',
        'status',
    ];


    /*Uma equipe pode ter vários contratos*/
    public function contratos()
    {
        return $this->hasMany(Contrato::class);
    }


    /*Partidas onde a equipe é mandante*/
    public function partidasMandante()
    {
        return $this->hasMany(Partida::class, 'mandante_id');
    }


    /*Partidas onde a equipe é visitante*/
    public function partidasVisitante()
    {
        return $this->hasMany(Partida::class, 'visitante_id');
    }
}
