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

/*Rotas Administrativas*/
Route::get('/Dashboard', function (){
    return view('areaAdministrativa.dashboard');
})->name('Dashboard')->middleware('auth');

Route::resource('campeonatos', CampeonatoController::class)->middleware('auth');

Route::resource('contratos', ContratoController::class)->middleware('auth');

Route::resource('equipes', EquipeController::class)->middleware('auth');

Route::resource('eventoPartidas', EventoPartidaController::class)->middleware('auth');

Route::resource('inscricoes', InscricaoController::class)->middleware('auth');

Route::resource('noticas', NoticiaController::class)->middleware('auth');

Route::resource('participantes', ParticipanteController::class)->middleware('auth');

Route::resource('partidas', PartidaController::class)->middleware('auth');

Route::resource('user', UserController::class)->middleware('auth');

/*Rotas de login e Logout*/
Route::post('/Login', [AuthController::class, 'login'])->name('LoginSubmit');

Route::post('/Logout', [AuthController::class, 'logout'])->name('Logout')->middleware('auth');
