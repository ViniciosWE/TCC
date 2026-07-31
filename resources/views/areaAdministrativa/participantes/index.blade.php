@extends('areaAdministrativa.sidebar')

@section('title', 'Gerenciar Participantes')

@section('content')
    <h1>Bem vindo Página de Participantes</h1>
    <a href="{{ route('participantes.create') }}">cadastrar</a>
@endsection