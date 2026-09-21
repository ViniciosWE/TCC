<?php

namespace App\Http\Controllers;

use App\Models\Campeonato;
use App\Models\Equipe;
use App\Models\EventoPartida;
use App\Models\Participante;
use App\Models\Partida;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsuarios = User::count(); // quantidade total de usuários cadastrados
        $campeonatosEmAndamento = Campeonato::where('status', 'EM_ANDAMENTO')->get(); // busca os campeonatos que estão em andamento
        $totalCampeonatosEmAndamento = $campeonatosEmAndamento->count(); // conta os campeonatos em andamento
        $campeonatosEmAndamentoIds = $campeonatosEmAndamento->pluck('id'); // pega os IDs dos campeonatos em andamento
        $equipesCadastradas = Equipe::count(); // total de equipes cadastradas
        $participantesCadastrados = Participante::count(); // total de participantes cadastrados
        $partidasHoje = Partida::with(['mandante', 'visitante', 'campeonato'])->whereDate('data_hora', today())->orderBy('data_hora')->get();//busca as partidas de hoje
        // Calcula os pênaltis de desempate de cada partida
        foreach ($partidasHoje as $partida) {
            $penaltis = EventoPartida::where('partida_id', $partida->id)->where('tipo', 'PENALTI_CONVERTIDO_DESEMPATE')->with('participante')->get();
            $penaltisMandante = 0;
            $penaltisVisitante = 0;
            foreach ($penaltis as $penalti) {
                $contrato = $penalti->participante->contratos()->where('status', 'ATIVO')->whereIn('equipe_id', [$partida->mandante_id, $partida->visitante_id])->first();
                if (!$contrato) {
                    continue;
                }
                if ($contrato->equipe_id == $partida->mandante_id) {
                    $penaltisMandante++;
                } else {
                    $penaltisVisitante++;
                }
            }
            // Adiciona os pênaltis à partida para exibição no dashboard
            $partida->penaltisMandante = $penaltisMandante;
            $partida->penaltisVisitante = $penaltisVisitante;
        }
        //envia tudo para o dashboard
        return view('areaAdministrativa.dashboard', compact('campeonatosEmAndamento', 'totalCampeonatosEmAndamento', 'partidasHoje', 'equipesCadastradas', 'participantesCadastrados', 'totalUsuarios'));
    }
}
