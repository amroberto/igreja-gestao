<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'União Paroquial') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100 pt-10">

    <!-- Barra Superior Fixa -->
    <div class="bg-blue-100 py-2 text-center text-xs text-blue-800 fixed w-full top-0 z-50 shadow-sm">
        <div class="flex items-center justify-center gap-8">
            <img src="{{ asset('images/larachurch.png') }}" alt="Logo LaraChurch" class="h-8 w-auto" />
            <span>Sistema desenvolvido por LaraChurch © {{ date('Y') }}</span>
        </div>
    </div>

    <!-- Conteúdo Centralizado -->
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-b from-blue-900 via-blue-800 to-orange-200 px-4 py-20">

        <!-- Formulário Central -->
        <div class="w-full max-w-xl bg-white rounded-2xl shadow-2xl p-8 mt-20">

            <!-- Logo -->
            <div class="text-center mb-6">
                @php
                    use Illuminate\Support\Facades\Storage;
                    $paroquialUnion = \App\Models\ParochialUnion::first();
                    $logoUrl = $paroquialUnion && $paroquialUnion->logo ? Storage::url($paroquialUnion->logo) : null;
                    $filePath = public_path(str_replace('/storage', 'storage', parse_url($logoUrl ?? '', PHP_URL_PATH)));
                    if (!$logoUrl || !file_exists($filePath)) {
                        $logoUrl = asset('images/larachurch.png');
                    }
                @endphp
                <div class="flex justify-center mb-4">
                    <img src="{{ $logoUrl }}" alt="Logo União Paroquial" class="h-20 w-auto" />
                </div>
                <h2 class="text-2xl font-bold text-gray-900">{{ __('Cadastre-se na União Paroquial') }}</h2>
                <p class="mt-2 text-sm text-gray-600">{{ __('Junte-se à nossa igreja!') }}</p>
            </div>

            <!-- Erros -->
            @if (session('error'))
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 p-4 rounded-lg text-sm">
                    <strong>Erro ao realizar o cadastro:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Formulário -->
            <form method="POST" action="{{ route('register') }}" id="register-form" class="space-y-5">
                @csrf

                <!-- Nome -->
                <div>
                    <x-input-label for="name" :value="__('Nome')" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zM3 17a7 7 0 0114 0H3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <x-text-input id="name" name="name" type="text" class="pl-10 w-full" :value="old('name')" required autofocus />
                    </div>
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <!-- Email -->
                <div>
                    <x-input-label for="email" :value="__('E-mail')" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 5.5l8 4.5 8-4.5v9a2 2 0 01-2 2H4a2 2 0 01-2-2v-9z" />
                                <path d="M18 5.5L10 10 2 5.5" />
                            </svg>
                        </div>
                        <x-text-input id="email" name="email" type="email" class="pl-10 w-full" :value="old('email')" required />
                    </div>
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <!-- CPF -->
                <div>
                    <x-input-label for="cpf" :value="__('CPF')" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2a1 1 0 00-1 1v1H7a2 2 0 00-2 2v10a2 2 0 002 2h6a2 2 0 002-2V6a2 2 0 00-2-2h-2V3a1 1 0 00-1-1z" />
                            </svg>
                        </div>
                        <x-text-input id="cpf" name="cpf" type="text" class="pl-10 w-full" :value="old('cpf')" required />
                    </div>
                    <x-input-error :messages="$errors->get('cpf')" />
                    <span id="cpf-error" class="hidden text-red-600 text-xs mt-1"></span>
                </div>

                <!-- Comunidade -->
                <div>
                    <x-input-label for="community_id" :value="__('Comunidade')" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                            </svg>
                        </div>
                        <select id="community_id" name="community_id" class="pl-10 w-full rounded border-gray-300 bg-gray-50">
                            <option value="">{{ __('Selecione uma comunidade') }}</option>
                            @foreach (\App\Models\Community::where('id', '!=', 1)->get() as $community)
                                <option value="{{ $community->id }}" {{ old('community_id') == $community->id ? 'selected' : '' }}>{{ $community->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-input-error :messages="$errors->get('community_id')" />
                </div>

                <!-- Senha -->
                <div>
                    <x-input-label for="password" :value="__('Senha')" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" />
                            </svg>
                        </div>
                        <x-text-input id="password" name="password" type="password" class="pl-10 w-full" required autocomplete="new-password" />
                    </div>
                    <x-input-error :messages="$errors->get('password')" />
                </div>

                <!-- Confirmar Senha -->
                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirmar Senha')" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" />
                            </svg>
                        </div>
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="pl-10 w-full" required autocomplete="new-password" />
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" />
                </div>

                <!-- Hidden user type -->
                <input type="hidden" name="user_type" value="member">

                <!-- Ações -->
                <div class="flex items-center justify-between mt-6">
                    <a class="text-sm text-blue-600 hover:underline" href="{{ route('login') }}">
                        {{ __('Já possui uma conta?') }}
                    </a>
                    <x-primary-button class="bg-gradient-to-r from-blue-900 to-orange-500 hover:from-blue-700 hover:to-orange-600 text-white px-5 py-2.5 rounded-lg shadow">
                        {{ __('Cadastrar') }}
                    </x-primary-button>
                </div>
            </form>

            <!-- Scripts -->
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
            <script>
                $(function () {
                    $('#cpf').mask('000.000.000-00');

                    function validarCPF(cpf) {
                        cpf = cpf.replace(/[^\d]+/g, '');
                        if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false;
                        let soma = 0;
                        for (let i = 0; i < 9; i++) soma += parseInt(cpf[i]) * (10 - i);
                        let digito1 = (soma * 10) % 11;
                        if (digito1 === 10 || digito1 === 11) digito1 = 0;
                        if (digito1 !== parseInt(cpf[9])) return false;
                        soma = 0;
                        for (let i = 0; i < 10; i++) soma += parseInt(cpf[i]) * (11 - i);
                        let digito2 = (soma * 10) % 11;
                        if (digito2 === 10 || digito2 === 11) digito2 = 0;
                        return digito2 === parseInt(cpf[10]);
                    }

                    $('#cpf').on('blur', function () {
                        const cpf = $(this).val().replace(/\D/g, '');
                        const isValid = validarCPF(cpf);
                        const error = $('#cpf-error');
                        if (!isValid) {
                            $(this).addClass('border-red-500').removeClass('border-green-500');
                            error.text('CPF inválido.').removeClass('hidden');
                        } else {
                            $(this).removeClass('border-red-500').addClass('border-green-500');
                            error.addClass('hidden');
                        }
                    });

                    $('#register-form').on('submit', function (e) {
                        const cpf = $('#cpf').val().replace(/\D/g, '');
                        $('#cpf').val(cpf);
                        if (!validarCPF(cpf)) {
                            e.preventDefault();
                            $('#cpf').addClass('border-red-500');
                            $('#cpf-error').text('CPF inválido.').removeClass('hidden');
                        }
                    });
                });
            </script>
        </div>
    </div>
</body>
</html>
