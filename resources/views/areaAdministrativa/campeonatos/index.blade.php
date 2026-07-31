@extends('areaAdministrativa.sidebar')

@section('title', 'Gerenciamento de Campeonatos')

@section('content')
    <h1>Bem vindo Página de campeoantos</h1>
     <a href="{{ route('campeonatos.create') }}">cadastrar</a>
@endsection