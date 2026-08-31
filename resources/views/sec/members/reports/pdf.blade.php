<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Membros</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
            color: #333;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>

    <h2>Relatório de Membros</h2>

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>CPF</th>
                <th>E-mail</th>
                <th>Telefone</th>
                <th>Cidade/UF</th>
                <th>Comunidade</th>
                <th>Forma PGTO</th>
                <th>Contribuinte</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($members as $member)
                <tr>
                    <td>{{ $member->full_name ?? $member->first_name . ' ' . $member->last_name }}</td>
                    <td>{{ formatCpf($member->cpf) ?? 'N/I' }}</td>
                    <td>{{ $member->email ?? 'N/I' }}</td>
                    <td>{{ formatPhone($member->cellphone) ?? formatPhone($member->phone) ?? 'N/I' }}</td>
                    <td>{{ $member->city ?? 'N/I' }}/{{ $member->state ?? '' }}</td>
                    <td>{{ $member->community?->name ?? 'N/I' }}</td>
                    <!-- Uso correto do label do Enum -->
                    <td>{{ $member->payment_method?->label() ?? $member->payment_method ?? 'N/I' }}</td>
                    <td class="text-center">{{ $member->is_contributor ? 'Sim' : 'Não' }}</td>
                    <td class="text-center">{{ $member->status?->value ?? $member->status ?? 'N/I' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Nenhum membro encontrado com os filtros aplicados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>