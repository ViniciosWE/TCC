<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampeonatoController;
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



/*Rotas de login e Logout*/
Route::post('/Login', [AuthController::class, 'login'])->name('LoginSubmit');

Route::post('/Logout', [AuthController::class, 'logout'])->name('Logout')->middleware('auth');
