<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config('app.name', 'União Paroquial') }}</title>

    <!-- Fontes e Ícones -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100 pt-12">

    <!-- Barra Superior -->
    <div class="bg-blue-100 py-2 text-center text-xs text-blue-800 fixed w-full top-0 z-50 shadow-sm">
        <div class="flex items-center justify-center gap-8">
            <img src="{{ asset('images/larachurch.png') }}" alt="Logo LaraChurch" class="h-8 w-auto" />
            <span>Sistema desenvolvido por LaraChurch © {{ date('Y') }}</span>
        </div>
    </div>

    <!-- Container principal -->
    <div class="min-h-screen flex items-center justify-center px-4 sm:px-6 py-12 bg-gradient-to-b from-blue-900 via-blue-800 to-orange-200">

        <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-8 sm:p-10">

            <!-- Cabeçalho com logo -->
            <div class="text-center mb-8">
                @php
                    use Illuminate\Support\Facades\Storage;
                    $paroquialUnion = \App\Models\ParochialUnion::first();
                    $logoUrl = $paroquialUnion && $paroquialUnion->logo
                        ? Storage::url($paroquialUnion->logo)
                        : null;

                    $filePath = $logoUrl
                        ? public_path(str_replace('/storage', 'storage', parse_url($logoUrl, PHP_URL_PATH)))
                        : null;

                    if (!$logoUrl || !file_exists($filePath)) {
                        $logoUrl = asset('images/larachurch.png');
                    }
                @endphp
                <div class="flex justify-center mb-4">
                    <img src="{{ $logoUrl }}" alt="Logo União Paroquial" class="h-20 w-auto" />
                </div>
                <h2 class="text-2xl font-bold text-gray-900">{{ __('Faça seu login na União Paroquial') }}</h2>
                <p class="mt-2 text-sm text-gray-600">{{ __('Bem-vindo!') }}</p>
            </div>

            <!-- Status da Sessão -->
            <x-auth-session-status class="mb-6" :status="session('status')" />

            <!-- Formulário de Login -->
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Campo Email -->
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-blue-500 text-sm"></i>
                        </div>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            class="pl-10 w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 transition"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                        />
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-1 text-red-600 text-xs" />
                </div>

                <!-- Campo Senha -->
                <div>
                    <x-input-label for="password" :value="__('Senha')" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-blue-500 text-sm"></i>
                        </div>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            class="pl-10 w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 transition"
                            required
                            autocomplete="current-password"
                        />
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1 text-red-600 text-xs" />
                </div>

                <!-- Checkbox -->
                <div class="block">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                        <span class="ms-2 text-sm text-gray-600">{{ __('Lembrar de mim') }}</span>
                    </label>
                </div>

                <!-- Ações -->
                <div class="flex items-center justify-between mt-6">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Esqueceu sua senha?') }}
                        </a>
                    @endif

                    <x-primary-button class="ms-3 bg-gradient-to-r from-blue-900 to-orange-500 hover:from-blue-700 hover:to-orange-600 text-white px-6 py-2.5 rounded-lg shadow transition">
                        {{ __('Entrar') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
