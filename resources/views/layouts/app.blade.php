<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config('app.name', 'União Paroquial') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Ícones -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-boue-50">

    <!-- Barra superior discreta sem logo -->
    <div class="bg-blue-50 py-1 px-4 text-center text-xs text-gray-600 border-b border-blue-100 shadow-sm">
        Sistema desenvolvido por LaraChurch © {{ date('Y') }} - Todos os direitos reservados
    </div>

    <!-- Navegação (Jetstream/Breeze) -->
    @include('layouts.navigation')

    <!-- Cabeçalho da Página -->
    @isset($header)
    <header class="bg-white shadow-sm">
        <div class="container py-4">
            {{ $header }}
        </div>
    </header>
    @endisset

    <!-- Conteúdo -->
    <main class="container py-4">
        @yield('content')
    </main>

    <!-- Scripts globais -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js" crossorigin="anonymous"></script>
    @yield('scripts')

</body>
</html>
