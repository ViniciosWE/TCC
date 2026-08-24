<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use App\Models\Equipe;
use App\Models\Participante;
use App\Models\Partida;

class DashboardController extends Controller
{
    public function index()
    {
        $campeonatosEmAndamento = Campeonato::where('status', 'EM_ANDAMENTO')->get();
        $totalCampeonatosEmAndamento = $campeonatosEmAndamento->count();
        $campeonatosEmAndamentoIds = $campeonatosEmAndamento->pluck('id');
        $partidasHoje = Partida::whereIn('campeonato_id', $campeonatosEmAndamentoIds)->whereDate('data_hora', today())->get();
        $equipesCadastradas = Equipe::count();
        $participantesCadastrados = Participante::count();
        return view('areaAdministrativa.dashboard', compact('campeonatosEmAndamento','totalCampeonatosEmAndamento','partidasHoje','equipesCadastradas','participantesCadastrados'));
    }
}
