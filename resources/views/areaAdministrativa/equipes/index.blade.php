@extends('areaAdministrativa.sidebar')

@section('title', 'Gerenciar Equipes')

@section('content')
    <h1>Bem vindo Página de equipes</h1>
    <a href="{{ route('equipes.create') }}">cadastrar</a>
@endsection