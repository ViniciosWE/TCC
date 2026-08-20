@extends('areaAdministrativa.sidebar')

@section('title', 'Gerenciamento de Eventos das Partidas')

@section('content')
    <h1>Bem vindo Página de Eventos das partidas</h1>
    <a href="{{ route('eventoPartidas.create') }}">criar</a>
@endsection