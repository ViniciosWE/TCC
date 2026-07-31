<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampeonatoController;
use App\Http\Controllers\ContratoController;
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

Route::get('/Login', function () {
    return view('login');
})->name('Login');

/* Rotas Administrativas */
Route::middleware(['auth', 'tipo:ADMINISTRADOR'])->group(function () {
    Route::resource('campeonatos', CampeonatoController::class);
    Route::resource('contratos', ContratoController::class);
    Route::resource('equipes', EquipeController::class);
    Route::resource('eventoPartidas', EventoPartidaController::class);
    Route::resource('inscricoes', InscricaoController::class);
    Route::resource('noticas', NoticiaController::class);
    Route::resource('participantes', ParticipanteController::class);
    Route::resource('partidas', PartidaController::class);
});

/* Rotas exclusivas do Super Administrador */
Route::middleware(['auth', 'tipo:SUPER_ADMINISTRADOR'])->group(function () {
    Route::resource('user', UserController::class)->except(['edit', 'update']);
});

/* Rotas compartilhadas entre Administrador e Super Administrador */
Route::middleware(['auth', 'tipo:ADMINISTRADOR,SUPER_ADMINISTRADOR'])->group(function () {
    Route::get('/Dashboard', function () {
        return view('areaAdministrativa.dashboard');
    })->name('Dashboard');
    Route::get('user/{user}/edit', [UserController::class, 'edit'])->name('user.edit');
    Route::put('user/{user}', [UserController::class, 'update'])->name('user.update');
});

/*Rotas de login e Logout*/
Route::post('/Login', [AuthController::class, 'login'])->name('LoginSubmit');
Route::post('/Logout', [AuthController::class, 'logout'])->name('Logout')->middleware('auth');
