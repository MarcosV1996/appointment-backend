<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <nav>
        <ul>
            <li><a href="{{ route('home')}}">Página Inicial</a></li>
            <li><a href="{{ route('agendamentos') }}">Agendamentos</a></li>
            <li><a href="{{ route('relatorios') }}">Relatórios</a></li>
            <li><a href="{{ route('about') }}">Sobre</a></li>
        </ul>
    </nav>

    <div class="container">
        @yield('content')
    </div>

    <footer class="footer">
        <p>&copy; 2024 Albergue. Todos os direitos reservados.</p>
        <p><a href="{{ route('about') }}">Sobre</a> | <a href="{{ route('contato') }}">Contato</a> | <a href="{{ route('privacidade') }}">Política de Privacidade</a></p>
    </footer>
</body>
</html>
