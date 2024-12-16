@extends('layouts.app')

@section('title', 'welcome')

@section('content')
@csrf
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <h1>Bem-vindo à Página welcome</h1>
    <p>Conteúdo da página welcome vai aqui.</p>
@endsection
