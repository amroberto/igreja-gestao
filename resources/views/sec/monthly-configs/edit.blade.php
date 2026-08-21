@extends('adminlte::page')

@section('title', __('Editar Configuração - Carta de Contribuição Mensal'))

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-12">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('sec.dashboard') }}"><i class="fas fa-tachometer-alt mr-1"></i>{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('sec.monthly-configs.index') }}"><i class="fas fa-file-alt mr-1"></i>{{ __('Config. Carta de Contrib. Mensais') }}</a></li>
                <li class="breadcrumb-item active"><i class="fas fa-edit mr-1"></i>{{ __('Editar') }}</li>
            </ol>
        </div>
    </div>
</div>
@endsection

@section('content')

@if (session('success'))
<x-adminlte-alert theme="success" title="{{ __('Sucesso') }}" icon="fas fa-check-circle" dismissable>
    {{ session('success') }}
</x-adminlte-alert>
@endif

@if ($errors->any())
<x-adminlte-alert theme="danger" title="{{ __('Erros de Validação') }}" icon="fas fa-exclamation-triangle" dismissable>
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</x-adminlte-alert>
@endif

<x-adminlte-card title="{{ __('Editar Carta de Contribuição Mensal') }}" theme="primary" icon="fas fa-file-alt" collapsible>

    <form method="POST" action="{{ route('sec.monthly-configs.update', $monthlyConfig->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row mb-4">
            <div class="col-md-6">
                <x-adminlte-select name="community_id" id="community_id" label="{{ __('Comunidade') }}" fgroup-class="has-feedback" error-key="community_id" igroup-size="sm" required>
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-building"></i></div>
                    </x-slot>
                    <option value="">{{ __('Selecione uma Comunidade') }}</option>
                    @foreach($communities as $community)
                    <option value="{{ $community->id }}" {{ old('community_id', $monthlyConfig->community_id) == $community->id ? 'selected' : '' }}>
                        {{ $community->name }}
                    </option>
                    @endforeach
                </x-adminlte-select>
            </div>
            <div class="col-md-6">
                <x-adminlte-input name="reference_date" label="{{ __('Mês de Referência (mês/ano)') }}" type="month" value="{{ old('reference_date', \Carbon\Carbon::parse($monthlyConfig->reference_date)->format('Y-m')) }}" fgroup-class="has-feedback" error-key="reference_date" igroup-size="sm" required>
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-calendar-alt"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <x-adminlte-input name="motto" label="{{ __('Lema Bíblico') }}" value="{{ old('motto', $monthlyConfig->motto) }}" fgroup-class="has-feedback" error-key="motto" igroup-size="sm" required>
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-bible"></i></div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>

        <!-- Seletor do Tipo de Conteúdo -->
        <div class="row mb-4">
            <div class="col-md-12">
                <label class="font-weight-bold">{{ __('Tipo de Conteúdo para Observações') }}</label>
                <div class="d-flex align-items-center">
                    <div class="custom-control custom-radio mr-4">
                        <input class="custom-control-input" type="radio" id="type_text" name="content_type" value="text" {{ old('content_type', $monthlyConfig->content_type ?? 'text') == 'text' ? 'checked' : '' }}>
                        <label for="type_text" class="custom-control-label">{{ __('Texto Personalizado') }}</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="type_image" name="content_type" value="image" {{ old('content_type', $monthlyConfig->content_type) == 'image' ? 'checked' : '' }}>
                        <label for="type_image" class="custom-control-label">{{ __('Imagem / Card Promocional') }}</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bloco de Texto -->
        <div class="row mb-4" id="text_block">
            <div class="col-md-12">
                <x-adminlte-textarea name="observations" label="{{ __('Observações') }}" fgroup-class="has-feedback" error-key="observations" rows="4">
                    {{ old('observations', $monthlyConfig->observations) }}
                </x-adminlte-textarea>
            </div>
        </div>

        <!-- Bloco de Upload de Imagem -->
        <div class="row mb-4" id="image_block" style="display: none;">
            <div class="col-md-12">
                @if($monthlyConfig->image_path)
                    <div class="p-3 mb-3 border rounded bg-light">
                        <label class="d-block text-muted mb-2">{{ __('Imagem Atualmente Cadastrada:') }}</label>
                        <img src="{{ asset('storage/' . $monthlyConfig->image_path) }}" alt="Preview" class="img-thumbnail shadow-sm" style="max-height: 180px;">
                    </div>
                @endif

                <x-adminlte-input-file name="image_file" id="image_file" label="{{ __('Substituir Imagem / Card (Deixe em branco para manter a atual)') }}" placeholder="{{ __('Escolha um novo arquivo...') }}" fgroup-class="has-feedback" error-key="image_file">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-upload"></i></div>
                    </x-slot>
                </x-adminlte-input-file>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <x-adminlte-button type="submit" theme="primary" label="{{ __('Salvar Alterações') }}" icon="fas fa-save" class="btn-lg mr-2" />
                <a href="{{ route('sec.monthly-configs.index') }}" class="btn btn-secondary btn-lg">
                    <i class="fas fa-arrow-left"></i> {{ __('Voltar') }}
                </a>
            </div>
        </div>
    </form>
</x-adminlte-card>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        function toggleContentType() {
            let selectedType = $('input[name="content_type"]:checked').val();
            if (selectedType === 'image') {
                $('#text_block').hide();
                $('#image_block').show();
            } else {
                $('#image_block').hide();
                $('#text_block').show();
            }
        }

        toggleContentType();
        $('input[name="content_type"]').on('change', toggleContentType);
    });
</script>
@endsection