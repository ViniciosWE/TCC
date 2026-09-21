<?php

namespace App\Http\Controllers;

use App\Models\Noticia;
use App\Models\Partida;
use Illuminate\Http\Request;

class PaginaInicialController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->get('data', now()->toDateString());
        $partidas = Partida::with(['mandante','visitante','campeonato' ])->whereDate('data_hora', $data)->orderBy('data_hora')->get()->groupBy('campeonato_id');
        $noticia = Noticia::latest()->first();
        return view('PaginaInicial', compact('partidas', 'noticia', 'data'));
    }
}