@extends('areaAdministrativa.sidebar')

@section('title', 'Gerenciamento de Usuários')

@section('content')
    <h1>Bem vindo Página de Usuários</h1>
    <a href="{{ route('user.create') }}">cadastrar</a>
@endsection