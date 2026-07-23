<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrato extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipe_id',
        'participante_id',
        'status',
    ];

    /* Um contrato pertence a uma equipe */
    public function equipe()
    {
        return $this->belongsTo(Equipe::class);
    }

    /* Um contrato pertence a um participante */
    public function participante()
    {
        return $this->belongsTo(Participante::class);
    }
}
