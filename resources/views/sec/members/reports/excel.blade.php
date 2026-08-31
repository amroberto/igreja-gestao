<table>
    <thead>
        <tr>
            <th style="font-weight: bold; background-color: #D3D3D3;">Código</th>
            <th style="font-weight: bold; background-color: #D3D3D3;">Nome</th>
            <th style="font-weight: bold; background-color: #D3D3D3;">CPF</th>
            <th style="font-weight: bold; background-color: #D3D3D3;">Comunidade</th>
            <th style="font-weight: bold; background-color: #D3D3D3;">Contribuinte</th>
            <th style="font-weight: bold; background-color: #D3D3D3;">Status</th>
            <th style="font-weight: bold; background-color: #D3D3D3;">Forma Pagamento</th>
            <th style="font-weight: bold; background-color: #D3D3D3;">Forma Entrega</th>
            <th style="font-weight: bold; background-color: #D3D3D3;">Telefone/Celular</th>
            <th style="font-weight: bold; background-color: #D3D3D3;">E-mail</th>
            <th style="font-weight: bold; background-color: #D3D3D3;">Endereço</th>
            <th style="font-weight: bold; background-color: #D3D3D3;">Bairro</th>
            <th style="font-weight: bold; background-color: #D3D3D3;">Cidade/UF</th>
            <th style="font-weight: bold; background-color: #D3D3D3;">CEP</th>
        </tr>
    </thead>
    <tbody>
        @foreach($members as $member)
            <tr>
                <td>{{ str_pad($member->id, 6, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $member->name }}</td>
                <td>{{ formatCpf($member->cpf) }}</td>
                <td>{{ $member->community?->name ?? 'N/A' }}</td>
                <td>{{ $member->is_contributor ? 'Sim' : 'Não' }}</td>
                <td>{{ $member->is_active ? 'Ativo' : 'Inativo' }}</td>
                <td>{{ $member->payment_method?->label() ?? $member->payment_method }}</td>
                <td>{{ $member->delivery_method?->value ?? $member->delivery_method }}</td>
                <td>{{ formatPhone($member->cellphone) ?? formatPhone($member->phone) }}</td>
                <td>{{ $member->email }}</td>
                <td>{{ $member->address }}, {{ $member->number }} {{ $member->complement }}</td>
                <td>{{ $member->district }}</td>
                <td>{{ $member->city }}/{{ $member->state }}</td>
                <td>{{ formatCep($member->cep) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>