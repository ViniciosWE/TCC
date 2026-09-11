<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampeonatoController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EquipeController;
use App\Http\Controllers\EventoPartidaController;
use App\Http\Controllers\InscricaoController;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\ParticipanteController;
use App\Http\Controllers\PartidaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
/*Rotas públicas*/
Route::get('/', function () {
    return view('PaginaInicial');
})->name('PaginaInicial');

Route::get('/PaginaCampeonatos', function () {
    return view('PaginaCampeonatos');
})->name('PaginaCampeonatos');

Route::get('/PaginaNoticias', function () {
    return view('PaginaNoticias');
})->name('PaginaNoticias');

//rota de login ele verifica se o usuário esta logado, se ele esta logado entra no dashboard direto.
Route::get('/login', function () {
    if (Auth::check()) {
        return redirect()->route('Dashboard');
    }
    return view('login');
})->name('Login');

/* Rotas Administrativas */
Route::middleware(['auth', 'tipo:ADMINISTRADOR'])->group(function () {
    Route::resource('campeonatos', CampeonatoController::class);
    Route::resource('contratos', ContratoController::class);
    Route::resource('equipes', EquipeController::class);
    Route::resource('eventoPartidas', EventoPartidaController::class);
    Route::resource('inscricoes', InscricaoController::class)->parameters(['inscricoes' => 'inscricao']);
    Route::resource('noticias', NoticiaController::class);
    Route::resource('participantes', ParticipanteController::class);
    Route::resource('partidas', PartidaController::class);
    Route::get('inscricoes/{inscricao}/comprovante', [InscricaoController::class, 'comprovante'])->name('inscricoes.comprovante');
    Route::post('campeonatos/{campeonato}/gerar-confrontos', [PartidaController::class, 'gerarConfrontos'])->name('campeonatos.gerarConfrontos');
    Route::patch('/partidas/{partida}/finalizar', [PartidaController::class, 'finalizar'])->name('partidas.finalizar');
    Route::patch('/partidas/{partida}/wo', [PartidaController::class, 'wo'])->name('partidas.wo');
    Route::get('/partidas/{partida}/sumula', [PartidaController::class, 'sumula'])->name('partidas.sumula');
});

/* Rotas exclusivas do Super Administrador */
Route::middleware(['auth', 'tipo:SUPER_ADMINISTRADOR'])->group(function () {
    Route::resource('user', UserController::class);
});

/* Rotas compartilhadas entre Administrador e Super Administrador */
Route::middleware(['auth', 'tipo:ADMINISTRADOR,SUPER_ADMINISTRADOR'])->group(function () {
    Route::get('/Dashboard', [DashboardController::class, 'index'])->name('Dashboard');
    Route::get('/perfil', [UserController::class, 'perfil'])->name('user.perfil');
    Route::put('/perfil', [UserController::class, 'atualizarPerfil'])->name('user.atualizarPerfil');
});

/*Rotas de login e Logout*/
Route::post('/Login', [AuthController::class, 'login'])->name('LoginSubmit');
Route::post('/Logout', [AuthController::class, 'logout'])->name('Logout')->middleware('auth');
