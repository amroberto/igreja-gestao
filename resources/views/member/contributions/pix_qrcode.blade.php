@extends('layouts.simple') {{-- Use um layout básico, sem menu, para popup --}}

@section('title', 'Código Pix')

@section('content')
<div class="container text-center py-4">
    <h4>Código Pix para pagamento</h4>
    <p class="mb-3">Use o QR Code abaixo para pagar via Pix</p>

    {{-- Supondo que $pixCode é uma string com o código Pix (string para leitura QR) --}}
    @if(!empty($pixCode))
        <div class="mb-4">
            {{-- Gerar QR code com o pacote simples-qrcode --}}
            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(250)->generate($pixCode) !!}
        </div>

        <p><small>Código Pix:</small><br> <code>{{ $pixCode }}</code></p>
    @else
        <div class="alert alert-danger">Não foi possível gerar o código Pix.</div>
    @endif

    <button class="btn btn-primary mt-3" onclick="window.close()">Fechar</button>
</div>
@endsection
