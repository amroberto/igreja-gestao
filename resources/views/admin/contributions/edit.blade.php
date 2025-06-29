@extends('adminlte::page')

@section('title', 'Editar Contribuição')

@section('content_header')
    <h1>Editar Contribuição</h1>
@stop

@section('content')
    <div class="container-fluid">
        <x-adminlte-card title="Atualizar Contribuição" theme="primary" collapsible>
            <form method="POST" action="{{ route('admin.contributions.update', $contribution->id) }}">
                @csrf
                @method('PUT')

                <!-- Membro -->
                <div class="form-group mb-3">
                    <x-adminlte-select name="member_id" label="Membro" required>
                        <option value="">Selecione um membro</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" {{ old('member_id', $contribution->member_id) == $member->id ? 'selected' : '' }}>
                                {{ $member->name }}
                            </option>
                        @endforeach
                    </x-adminlte-select>
                    @error('member_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Valor -->
                <div class="form-group mb-3">
                    <x-adminlte-input name="amount" type="number" step="0.01" label="Valor" value="{{ old('amount', $contribution->amount) }}" required />
                    @error('amount')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Data Inicial -->
                <div class="form-group mb-3">
                    <x-adminlte-input name="start_date" type="month" label="Mês/Ano Inicial" value="{{ old('start_date', $contribution->start_date->format('Y-m')) }}" required />
                    @error('start_date')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Data Final -->
                <div class="form-group mb-3">
                    <x-adminlte-input name="end_date" type="month" label="Mês/Ano Final (opcional)" value="{{ old('end_date', $contribution->end_date ? $contribution->end_date->format('Y-m') : '') }}" />
                    @error('end_date')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Descrição -->
                <div class="form-group mb-3">
                    <x-adminlte-textarea name="description" label="Descrição">{{ old('description', $contribution->description) }}</x-adminlte-textarea>
                    @error('description')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <x-adminlte-button type="submit" label="Salvar" theme="primary" icon="fas fa-save" />
            </form>
        </x-adminlte-card>
    </div>
@stop