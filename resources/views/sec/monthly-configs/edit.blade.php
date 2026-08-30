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
                        <input class="custom-control-input" type="radio" id="type_text" name="content_type" value="text" {{ old('content_type', $monthlyConfig->card_image_path ? 'image' : 'text') == 'text' ? 'checked' : '' }}>
                        <label for="type_text" class="custom-control-label">{{ __('Texto Personalizado') }}</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="type_image" name="content_type" value="image" {{ old('content_type', $monthlyConfig->card_image_path ? 'image' : 'text') == 'image' ? 'checked' : '' }}>
                        <label for="type_image" class="custom-control-label">{{ __('Imagem / Card Promocional') }}</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bloco de Texto Formatável (Summernote) -->
        <div class="row mb-4" id="text_block">
            <div class="col-md-12">
                <label for="observations" class="font-weight-bold">{{ __('Observações') }}</label>
                <textarea name="observations" id="observations" class="form-control summernote">{{ old('observations', $monthlyConfig->observations) }}</textarea>
            </div>
        </div>

        <!-- Bloco de Upload de Imagem -->
        <div class="row mb-4" id="image_block" style="display: none;">
            <div class="col-md-12">
                <x-adminlte-input-file name="image_file" id="image_file" label="{{ __('Imagem ou Card (PNG, JPG, WEBP - máx 2MB)') }}" placeholder="{{ __('Escolha uma nova imagem para substituir...') }}" fgroup-class="has-feedback" error-key="image_file">
                    <x-slot name="prependSlot">
                        <div class="input-group-text"><i class="fas fa-image"></i></div>
                    </x-slot>
                </x-adminlte-input-file>

                @if($monthlyConfig->card_image_path)
                <div class="mt-2">
                    <label class="d-block">{{ __('Imagem Atual:') }}</label>
                    <img src="{{ Storage::url($monthlyConfig->card_image_path) }}" alt="Card Atual" class="img-thumbnail" style="max-height: 150px;">
                </div>
                @endif
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
<!-- CDN de suporte ao Summernote caso o plugin adminlte não esteja mapeado -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>

<script>
    $(document).ready(function() {
        // Inicializa o Editor Rich-Text com barra de ferramentas simplificada
        $('#observations').summernote({
            height: 150,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['misc', ['undo', 'redo']]
            ]
        });

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