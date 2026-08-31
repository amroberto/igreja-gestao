<?php

namespace App\Exports;

use App\Models\Member;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class MembersExport implements FromView
{
    protected array $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function view(): View
    {
        $user = auth()->user();

        // Traz as comunidades permitidas ao usuário da secretaria
        $allowedCommunityIds = $user->communities()->pluck('communities.id')->toArray();

        $query = Member::query();

        // Restringe estritamente às comunidades permitidas
        $query->whereIn('community_id', $allowedCommunityIds);

        // Filtro por comunidade específica selecionada
        if (!empty($this->filters['community_id'])) {
            $query->where('community_id', $this->filters['community_id']);
        }

        // Filtro por termo de busca (Nomes, CPF, E-mail)
        if (!empty($this->filters['search_term'])) {
            $term = $this->filters['search_term'];
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                  ->orWhere('middle_name', 'like', "%{$term}%")
                  ->orWhere('last_name', 'like', "%{$term}%")
                  ->orWhere('cpf', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            });
        }

        // Filtro por status
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        // Filtro por contribuinte
        if (isset($this->filters['is_contributor']) && $this->filters['is_contributor'] !== '') {
            $query->where('is_contributor', $this->filters['is_contributor']);
        }

        // Filtro por meio de pagamento
        if (!empty($this->filters['payment_method'])) {
            $query->where('payment_method', $this->filters['payment_method']);
        }

        // Filtro por meio de entrega
        if (!empty($this->filters['delivery_method'])) {
            $query->where('delivery_method', $this->filters['delivery_method']);
        }

        // CORREÇÃO: Substituído orderBy('name') pelas colunas reais
        $members = $query->orderBy('first_name')
                         ->orderBy('last_name')
                         ->get();

        return view('sec.members.reports.excel', compact('members'));
    }
}