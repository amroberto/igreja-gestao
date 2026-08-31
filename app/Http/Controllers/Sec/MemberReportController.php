<?php

namespace App\Http\Controllers\Sec;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Exports\MembersExport;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class MemberReportController extends Controller
{
    /**
     * Exibe a página principal de relatórios com o formulário e a listagem.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Traz apenas as comunidades vinculadas ao usuário logado na secretaria
        $communities = $user->communities()->orderBy('name')->get();
        $allowedCommunityIds = $communities->pluck('id')->toArray();

        // 2. Constrói a consulta filtrada
        $query = $this->buildQuery($request, $allowedCommunityIds);

        // 3. Só executa a paginação se o formulário for submetido
        $members = $request->has('search')
            ? $query->paginate(20)
            : null;

        return view('sec.members.reports.index', compact('communities', 'members'));
    }

    /**
     * Exporta o relatório filtrado para PDF.
     */
    public function exportPdf(Request $request)
    {
        $user = auth()->user();

        // Obtém as comunidades que o usuário pode acessar
        $allowedCommunityIds = $user->communities()->pluck('communities.id')->toArray();

        // Executa a busca com os mesmos filtros
        $members = $this->buildQuery($request, $allowedCommunityIds)->get();

        // Carrega a view e define o papel como A4 Paisagem (ideal para relatórios com muitas colunas)
        $pdf = Pdf::loadView('sec.members.reports.pdf', compact('members'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('relatorio-membros-' . date('Y-m-d_H-i') . '.pdf');
    }

    /**
     * Exporta o relatório filtrado para Excel (XLSX).
     */
    public function exportExcel(Request $request)
    {
        return Excel::download(
            new MembersExport($request->all()),
            'relatorio-membros-' . date('Y-m-d_H-i') . '.xlsx'
        );
    }

    /**
     * Método auxiliar privado para reaproveitar os filtros e regras de segurança.
     */
    private function buildQuery(Request $request, array $allowedCommunityIds)
    {
        $query = Member::query();

        // Restringe estritamente às comunidades com permissão do usuário
        $query->whereIn('community_id', $allowedCommunityIds);

        // Filtro por comunidade específica selecionada
        if ($request->filled('community_id')) {
            $query->where('community_id', $request->community_id);
        }

        // Filtro por termo de busca (Primeiro/Último Nome, CPF, E-mail)
        if ($request->filled('search_term')) {
            $term = $request->search_term;
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('middle_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('cpf', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        // Filtro por Status (StatusMemberEnum)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por Contribuinte (Booleano)
        if ($request->has('is_contributor') && $request->is_contributor !== '') {
            $query->where('is_contributor', $request->is_contributor);
        }

        // Filtro por Meio de Pagamento
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filtro por Meio de Entrega
        if ($request->filled('delivery_method')) {
            $query->where('delivery_method', $request->delivery_method);
        }

        // Ordenação padrão pelas colunas físicas existentes na tabela
        return $query->orderBy('first_name')->orderBy('last_name');
    }
}
