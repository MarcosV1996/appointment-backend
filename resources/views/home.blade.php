@extends('layouts.app')

@section('title', 'Página Inicial')

@section('content')
@csrf
    <meta name="csrf-token" content="{{ csrf_token() }}">   
    <h1>Bem-vindo à Página Inicial</h1>
    <p>Conteúdo da página inicial vai aqui.</p>
@endsection
