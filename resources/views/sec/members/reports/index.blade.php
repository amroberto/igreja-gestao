@extends('adminlte::page')

@section('title', 'Relatório de Membros')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Relatório de Membros</h1>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Card de Filtros -->
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filtros de Pesquisa</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('sec.reports.members.index') }}" method="GET" id="report-filter-form">
                    <input type="hidden" name="search" value="1">

                    <div class="row">
                        <!-- Busca por Nome / CPF / E-mail -->
                        <div class="col-md-4 form-group">
                            <label for="search_term">Buscar por Nome, CPF ou E-mail</label>
                            <input type="text" name="search_term" id="search_term" class="form-control" 
                                   placeholder="Digite para pesquisar..." value="{{ request('search_term') }}">
                        </div>

                        <!-- Filtro por Comunidade (Apenas autorizadas ao usuário do painel SEC) -->
                        <div class="col-md-4 form-group">
                            <label for="community_id">Comunidade</label>
                            <select name="community_id" id="community_id" class="form-control select2">
                                <option value="">Todas as Comunidades Permitidas</option>
                                @foreach($communities as $community)
                                    <option value="{{ $community->id }}" {{ request('community_id') == $community->id ? 'selected' : '' }}>
                                        {{ $community->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filtro por StatusMemberEnum -->
                        <div class="col-md-4 form-group">
                            <label for="status">Status do Membro</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">Todos os Status</option>
                                @foreach(App\Enums\StatusMemberEnum::cases() as $statusEnum)
                                    <option value="{{ $statusEnum->value }}" {{ request('status') === $statusEnum->value ? 'selected' : '' }}>
                                        {{ $statusEnum->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Filtro por Contribuinte -->
                        <div class="col-md-3 form-group">
                            <label for="is_contributor">Contribuinte?</label>
                            <select name="is_contributor" id="is_contributor" class="form-control">
                                <option value="">Todos</option>
                                <option value="1" {{ request('is_contributor') === '1' ? 'selected' : '' }}>Sim</option>
                                <option value="0" {{ request('is_contributor') === '0' ? 'selected' : '' }}>Não</option>
                            </select>
                        </div>

                        <!-- Filtro por PaymentMethodEnum -->
                        <div class="col-md-3 form-group">
                            <label for="payment_method">Forma de Pagamento</label>
                            <select name="payment_method" id="payment_method" class="form-control">
                                <option value="">Todas as Formas</option>
                                @foreach(App\Enums\PaymentMethodEnum::cases() as $paymentEnum)
                                    <option value="{{ $paymentEnum->value }}" {{ request('payment_method') === $paymentEnum->value ? 'selected' : '' }}>
                                        {{ $paymentEnum->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filtro por DeliveryMethodEnum -->
                        <div class="col-md-3 form-group">
                            <label for="delivery_method">Forma de Entrega</label>
                            <select name="delivery_method" id="delivery_method" class="form-control">
                                <option value="">Todas as Formas</option>
                                @foreach(App\Enums\DeliveryMethodEnum::cases() as $deliveryEnum)
                                    <option value="{{ $deliveryEnum->value }}" {{ request('delivery_method') === $deliveryEnum->value ? 'selected' : '' }}>
                                        {{ $deliveryEnum->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Botões de Ação do Filtro -->
                        <div class="col-md-3 form-group d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-search mr-1"></i> Filtrar
                            </button>
                            <a href="{{ route('sec.reports.members.index') }}" class="btn btn-default">
                                <i class="fas fa-undo mr-1"></i> Limpar
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabela de Resultados e Botões de Exportação -->
        @if($members)
            <div class="card card-outline card-primary">
                <div class="card-header d-flex p-0">
                    <h3 class="card-title p-3">
                        <i class="fas fa-list mr-1"></i> Resultados Encontrados
                        <span class="badge badge-info ml-2">{{ $members->total() }} registros</span>
                    </h3>
                    
                    <!-- Botões de Exportação -->
                    <div class="ml-auto p-2">
                        <a href="{{ route('sec.reports.members.excel', request()->all()) }}" class="btn btn-success btn-sm mr-1" target="_blank">
                            <i class="fas fa-file-excel mr-1"></i> Exportar para Excel
                        </a>
                        <a href="{{ route('sec.reports.members.pdf', request()->all()) }}" class="btn btn-danger btn-sm" target="_blank">
                            <i class="fas fa-file-pdf mr-1"></i> Exportar para PDF
                        </a>
                    </div>
                </div>

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped text-nowrap">
                        <thead>
                            <tr>
                                <th>Cód.</th>
                                <th>Nome</th>
                                <th>CPF</th>
                                <th>Comunidade</th>
                                <th>Status</th>
                                <th>Contribuinte</th>
                                <th>Pagamento</th>
                                <th>Entrega</th>
                                <th>Contato</th>
                                <th>Cidade/UF</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($members as $member)
                                <tr>
                                    <td>{{ str_pad($member->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td><strong>{{ $member->name }}</strong></td>
                                    <td>{{ $member->cpf ?? 'N/I' }}</td>
                                    <td>{{ $member->community?->name ?? 'N/A' }}</td>
                                    
                                    <!-- Status com Badge -->
                                    <td>
                                        @if($member->status instanceof \App\Enums\StatusMemberEnum)
                                            <span class="badge badge-{{ $member->status->color() }}">
                                                {{ $member->status->label() }}
                                            </span>
                                        @elseif($statusEnum = \App\Enums\StatusMemberEnum::tryFrom($member->status))
                                            <span class="badge badge-{{ $statusEnum->color() }}">
                                                {{ $statusEnum->label() }}
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">{{ $member->status ?? 'N/I' }}</span>
                                        @endif
                                    </td>

                                    <!-- Contribuinte -->
                                    <td>
                                        @if($member->is_contributor)
                                            <span class="badge badge-info">Sim</span>
                                        @else
                                            <span class="badge badge-secondary">Não</span>
                                        @endif
                                    </td>

                                    <!-- Forma de Pagamento -->
                                    <td>
                                        @if($member->payment_method instanceof \App\Enums\PaymentMethodEnum)
                                            {{ $member->payment_method->label() }}
                                        @elseif($paymentEnum = \App\Enums\PaymentMethodEnum::tryFrom($member->payment_method))
                                            {{ $paymentEnum->label() }}
                                        @else
                                            {{ $member->payment_method ?? 'N/I' }}
                                        @endif
                                    </td>

                                    <!-- Forma de Entrega -->
                                    <td>
                                        @if($member->delivery_method instanceof \App\Enums\DeliveryMethodEnum)
                                            <span class="badge badge-{{ $member->delivery_method->badgeColor() }}">
                                                <i class="{{ $member->delivery_method->iconClass() }} mr-1"></i>
                                                {{ $member->delivery_method->label() }}
                                            </span>
                                        @elseif($deliveryEnum = \App\Enums\DeliveryMethodEnum::tryFrom($member->delivery_method))
                                            <span class="badge badge-{{ $deliveryEnum->badgeColor() }}">
                                                <i class="{{ $deliveryEnum->iconClass() }} mr-1"></i>
                                                {{ $deliveryEnum->label() }}
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">{{ $member->delivery_method ?? 'N/I' }}</span>
                                        @endif
                                    </td>

                                    <td>
                                        <small><i class="fas fa-phone mr-1"></i>{{ $member->cellphone ?? $member->phone ?? 'N/I' }}</small>
                                    </td>
                                    <td>{{ $member->city ?? 'N/I' }}/{{ $member->state ?? '' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">
                                        <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
                                        Nenhum membro encontrado com os filtros selecionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{ $members->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        @else
            <div class="callout callout-info">
                <h5><i class="fas fa-info-circle"></i> Instruções:</h5>
                <p>Selecione os filtros acima e clique em <strong>Filtrar</strong> para visualizar a lista e habilitar as opções de exportação em Excel e PDF.</p>
            </div>
        @endif
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            if ($.fn.select2) {
                $('.select2').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            }
        });
    </script>
@stop