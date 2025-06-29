<div style="text-align: center; margin-bottom: 15px;">
    @if($parochialUnion && $parochialUnion->logo && file_exists(public_path('storage/' . $parochialUnion->logo)))
        <img src="{{ asset('storage/' . $parochialUnion->logo) }}" alt="Logo {{ $parochialUnion->trade_name }}" style="max-height: 50px; margin-bottom: 8px;">
    @else
        <p>Logo não encontrado</p>
    @endif
    <h2 style="font-size: 14pt; margin: 5px 0;">{{ $parochialUnion->name ?? 'União Paroquial' }}</h2>
    <p style="font-size: 9pt; margin: 0;">
        {{ $parochialUnion->address ?? 'Endereço não informado' }}<br>
        {{ $parochialUnion->city ?? '' }} - {{ $parochialUnion->state ?? '' }}<br>
        CNPJ: {{ formatCnpj($parochialUnion->cnpj) ?? 'Não informado' }}
    </p>
</div>