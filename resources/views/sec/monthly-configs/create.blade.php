@extends('adminlte::page')

@section('title', __('Criar Configuração - Carta de Contribuição Mensal'))

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-12">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('sec.dashboard') }}"><i class="fas fa-tachometer-alt mr-1"></i>{{ __('Dashboard') }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('sec.monthly-configs.index') }}"><i class="fas fa-file-alt mr-1"></i>{{ __('Config. Carta de Contrib. Mensais') }}</a></li>
                <li class="breadcrumb-item active"><i class="fas fa-plus mr-1"></i>{{ __('Criar') }}</li>
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

<x-adminlte-card title="{{ __('Configurar Carta de Contribuição Mensal') }}" theme="primary" icon="fas fa-file-alt" collapsible>

    <form method="POST" action="{{ route('sec.monthly-configs.store') }}">
        @csrf
        <div class="row mb-4">
            <div class="col-md-6">
                <x-adminlte-select name="community_id" label="{{ __('Comunidade') }}" fgroup-class="has-feedback" error-key="community_id" igroup-size="sm" required>
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-building"></i>
                        </div>
                    </x-slot>
                    <option value="">{{ __('Selecione uma Comunidade') }}</option>
                    @foreach($communities as $community)
                    <option value="{{ $community->id }}" {{ old('community_id', request()->input('community_id')) == $community->id ? 'selected' : '' }}>
                        {{ $community->name }}
                    </option>
                    @endforeach
                </x-adminlte-select>
            </div>
            <div class="col-md-6">
                <x-adminlte-input name="reference_date" label="{{ __('Mês de Referência (mês/ano)') }}" type="month" value="{{ old('reference_date', request()->input('reference_date', now()->format('Y-m'))) }}" fgroup-class="has-feedback" error-key="reference_date" igroup-size="sm" required>
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-12">
                <x-adminlte-input name="motto" label="{{ __('Lema Bíblico') }}" value="{{ old('motto', 'Tu me farás ver os caminhos da vida. Sl 16.11') }}" fgroup-class="has-feedback" error-key="motto" igroup-size="sm" required>
                    <x-slot name="prependSlot">
                        <div class="input-group-text">
                            <i class="fas fa-bible"></i>
                        </div>
                    </x-slot>
                </x-adminlte-input>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-12">
                <x-adminlte-text-editor name="observations" label="Observações" maxlength="1500" value="{{ old('observations', 'A contribuição proporcional é minha oferta de gratidão à igreja.') }}" required rows=5 id="observations">
                    {{ old('observations', 'A contribuição proporcional é minha oferta de gratidão à igreja.') }}
                </x-adminlte-text-editor>
                @error('observations')
                <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <x-adminlte-button type="submit" theme="primary" label="{{ __('Salvar') }}" icon="fas fa-save" class="btn-lg mr-2" />
                <x-adminlte-button theme="secondary" label="{{ __('Voltar') }}" icon="fas fa-arrow-left" class="btn-lg" onclick="window.location='{{ route('sec.monthly-configs.index') }}'" />
            </div>
        </div>
    </form>
</x-adminlte-card>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css">
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script>
        $(document).ready(function() {
            $('#observations').summernote({
                height: 500,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'hr']],
                    ['view', ['fullscreen', 'codeview']],
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        let formData = new FormData();
                        formData.append('file', files[0]);
                        $.ajax({
                            url: '{{ route('sec.monthly-configs.upload-image') }}',
                            method: 'POST',
                            data: formData,
                            contentType: false,
                            processData: false,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                $('#observations').summernote('insertImage', response.url);
                            },
                            error: function(xhr) {
                                alert('Erro ao fazer upload da imagem: ' + (xhr.responseJSON?.message || 'Erro desconhecido'));
                            }
                        });
                    }
                }
            });

            // Debug form submission
            $('#monthlyConfigForm').on('submit', function() {
                console.log('Form submitted with community_id:', $('#community_id').val());
                console.log('Form data:', $(this).serialize());
            });
        });
    </script>
@endsection
