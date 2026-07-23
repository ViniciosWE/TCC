<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Noticia extends Model
{
    use HasFactory;

    protected $fillable = [
        'campeonato_id',
        'user_id',
        'titulo',
        'descricao',
        'imagem',
    ];

    /* Uma notícia pertence ao usuário que publicou */
    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    /* Uma notícia pode pertencer a um campeonato */
    public function campeonato()
    {
        return $this->belongsTo(Campeonato::class);
    }
}
