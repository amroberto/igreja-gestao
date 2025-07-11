@extends('layouts.app')

@section('title', 'Boleto Bancário')

@section('content')
<div class="container py-4">
    <h4>Boleto Bancário Gerado</h4>
    <p>Segue o boleto para pagamento.</p>

    @if(!empty($boletoUrl))
        <div class="mb-3">
            <a href="{{ $boletoUrl }}" target="_blank" class="btn btn-primary">
                Abrir Boleto em nova aba
            </a>
        </div>

        <iframe src="{{ $boletoUrl }}" style="width:100%; height:600px;" title="Boleto Bancário"></iframe>
    @else
        <div class="alert alert-danger">Não foi possível gerar o boleto.</div>
    @endif
</div>
@endsection
