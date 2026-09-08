<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inscricao extends Model
{
    use HasFactory;

    protected $table = 'inscricoes';

    protected $fillable = [
        'campeonato_id',
        'user_id',
        'equipe_id',
        'status',
    ];


    /* Uma inscrição pertence a um campeonato */
    public function campeonato()
    {
        return $this->belongsTo(Campeonato::class);
    }


    /* Uma inscrição pertence ao usuário que realizou */
    public function usuario()
    {
        return $this->belongsTo(User::class);
    }


    /* Uma inscrição pertence a uma equipe */
    public function equipe()
    {
        return $this->belongsTo(Equipe::class);
    }
}
