<?php

namespace App\Http\Controllers\Sec;

use App\Helpers\PixHelper;
use App\Models\Contribution;
use App\Models\MonthlyConfig;
use App\Models\ParochialUnion;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MonthlyConfigController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $communityIds = $this->getUserCommunityIds($user);

        $configs = MonthlyConfig::whereIn('community_id', $communityIds)
            ->with('community')
            ->orderBy('reference_date', 'desc')
            ->get();

        return view('sec.monthly-configs.index', compact('configs'));
    }

    public function create()
    {
        $user = Auth::user();
        $communities = $user->communities;

        if ($communities->isEmpty() && $user->community) {
            $communities = collect([$user->community]);
        }

        if ($communities->isEmpty()) {
            return redirect()
                ->route('sec.monthly-configs.index')
                ->with('error', 'Nenhuma comunidade associada ao seu usuário.');
        }

        return view('sec.monthly-configs.create', compact('communities'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $communityIds = $this->getUserCommunityIds($user);

        $request->validate([
            'community_id' => [
                'required',
                'exists:communities,id',
                function ($attribute, $value, $fail) use ($communityIds) {
                    if (!in_array((string) $value, array_map('strval', $communityIds))) {
                        $fail('A comunidade selecionada não está associada ao seu usuário.');
                    }
                },
            ],
            'reference_date' => 'required|date_format:Y-m',
            'motto' => 'required|string|max:255',
            'observations' => 'required|string',
        ]);

        $referenceDate = Carbon::createFromFormat(
            'Y-m',
            $request->reference_date
        )->startOfMonth();

        MonthlyConfig::create([
            'community_id' => $request->community_id,
            'reference_date' => $referenceDate->toDateString(),
            'motto' => $request->motto,
            'observations' => $request->observations,
        ]);

        return redirect()
            ->route('sec.monthly-configs.index')
            ->with('success', 'Configuração criada com sucesso!');
    }

    public function edit(MonthlyConfig $monthlyConfig)
    {
        $user = Auth::user();
        $communityIds = $this->getUserCommunityIds($user);

        if (!in_array(
            (string) $monthlyConfig->community_id,
            array_map('strval', $communityIds)
        )) {
            return redirect()
                ->route('sec.monthly-configs.index')
                ->with('error', 'Você não tem permissão para editar esta configuração.');
        }

        $communities = $user->communities;

        if ($communities->isEmpty() && $user->community) {
            $communities = collect([$user->community]);
        }

        return view(
            'sec.monthly-configs.edit',
            compact('monthlyConfig', 'communities')
        );
    }

    public function update(Request $request, MonthlyConfig $monthlyConfig)
    {
        $user = Auth::user();
        $communityIds = $this->getUserCommunityIds($user);

        if (!in_array(
            (string) $monthlyConfig->community_id,
            array_map('strval', $communityIds)
        )) {
            return redirect()
                ->route('sec.monthly-configs.index')
                ->with('error', 'Você não tem permissão para editar esta configuração.');
        }

        $request->validate([
            'community_id' => [
                'required',
                'exists:communities,id',
                function ($attribute, $value, $fail) use ($communityIds) {
                    if (!in_array((string) $value, array_map('strval', $communityIds))) {
                        $fail('A comunidade selecionada não está associada ao seu usuário.');
                    }
                },
            ],
            'reference_date' => 'required|date_format:Y-m',
            'motto' => 'required|string|max:255',
            'observations' => 'required|string|max:1000',
        ]);

        $referenceDate = Carbon::createFromFormat(
            'Y-m',
            $request->reference_date
        )->startOfMonth();

        $monthlyConfig->update([
            'community_id' => $request->community_id,
            'reference_date' => $referenceDate->toDateString(),
            'motto' => $request->motto,
            'observations' => preg_replace(
                '/required="required" rows="\d+" >/',
                '',
                $request->observations
            ),
        ]);

        return redirect()
            ->route('sec.monthly-configs.index')
            ->with('success', 'Configuração atualizada com sucesso!');
    }

    public function destroy(MonthlyConfig $monthlyConfig)
    {
        $user = Auth::user();
        $communityIds = $this->getUserCommunityIds($user);

        if (!in_array(
            (string) $monthlyConfig->community_id,
            array_map('strval', $communityIds)
        )) {
            return redirect()
                ->route('sec.monthly-configs.index')
                ->with('error', 'Você não tem permissão para excluir esta configuração.');
        }

        if ($monthlyConfig->letters()->exists()) {
            return redirect()
                ->route('sec.monthly-configs.index')
                ->with(
                    'error',
                    'Não é possível excluir esta configuração pois já possui cartas associadas.'
                );
        }

        $monthlyConfig->delete();

        return redirect()
            ->route('sec.monthly-configs.index')
            ->with('success', 'Configuração excluída com sucesso!');
    }

    public function show(MonthlyConfig $monthlyConfig)
    {
        $user = Auth::user();
        $communityIds = $this->getUserCommunityIds($user);

        if (!in_array(
            (string) $monthlyConfig->community_id,
            array_map('strval', $communityIds)
        )) {
            return redirect()
                ->route('sec.monthly-configs.index')
                ->with('error', 'Você não tem permissão para visualizar esta configuração.');
        }

        return view('sec.monthly-configs.show', compact('monthlyConfig'));
    }

    /**
     * Exibe a pré-visualização em PDF do Informativo Mensal.
     */
    public function preview(MonthlyConfig $monthlyConfig)
    {
        $user = Auth::user();
        $communityIds = $this->getUserCommunityIds($user);

        if (!in_array((string) $monthlyConfig->community_id, array_map('strval', $communityIds))) {
            return redirect()
                ->route('sec.monthly-configs.index')
                ->with('error', 'Você não tem permissão para visualizar esta configuração.');
        }

        $monthlyConfig->load('community.parish');
        $parochialUnion = ParochialUnion::first();

        if (!$parochialUnion) {
            return redirect()
                ->route('sec.monthly-configs.index')
                ->with('error', 'Nenhuma união paroquial encontrada.');
        }

        $parish = $monthlyConfig->community->parish;
        if (!$parish) {
            return redirect()
                ->route('sec.monthly-configs.index')
                ->with('error', 'Nenhuma paróquia associada à comunidade foi encontrada.');
        }

        $member = $monthlyConfig->community
            ->members()
            ->orderBy('id')
            ->first();

        if (!$member) {
            return redirect()
                ->route('sec.monthly-configs.index')
                ->with('error', 'Não há membros cadastrados nesta comunidade para gerar a visualização.');
        }

        $config = $monthlyConfig;

        $contributions = Contribution::query()
            ->where('member_id', $member->id)
            ->where('community_id', $monthlyConfig->community_id)
            ->where('status', 'confirmed')
            ->orderByDesc('contribution_date')
            ->limit(12)
            ->get();

        // 1. FORMATAR AS VARIÁVEIS DE IDENTIFICAÇÃO E DOCUMENTO
        $memberIdFormatted = str_pad($member->id ?? 1, 6, '0', STR_PAD_LEFT);
        $configIdFormatted = str_pad($config->id ?? 1, 6, '0', STR_PAD_LEFT);

        $documentNumber = sprintf(
            'DOC-%02d-%s-%s',
            $config->community_id ?? 1,
            $config->reference_date ? $config->reference_date->format('Ym') : date('Ym'),
            $memberIdFormatted
        );

        // 2. IDENTIFICADOR ALFANUMÉRICO PARA O PIX (TXID BACEN)
        $txid = "ME{$memberIdFormatted}DOC{$configIdFormatted}";

        // 3. CONTEÚDO DO QR CODE
        if ($parish->pix_static_enabled && !empty($parish->pix_key)) {
            $qrContent = PixHelper::generatePayload(
                pixKey: $parish->pix_key,
                receiverName: $parish->pix_receiver_name ?? $parish->name,
                city: $parish->pix_city ?? 'SAO PAULO',
                amount: null,
                txid: $txid,
                pixKeyType: $parish->pix_key_type
            );
        } else {
            $qrContent = "PREVIEW | MEMBRO: {$memberIdFormatted} | DOC: {$documentNumber}";
        }

        // 4. GERAR O QR CODE EM BASE64
        $qrCode = new QrCode(
            data: $qrContent,
            size: 120,
            margin: 4
        );

        $writer = new PngWriter();
        $qrCodeBase64 = $writer->write($qrCode)->getDataUri();

        // 5. RENDERIZAÇÃO DO PDF (garantindo que $documentNumber está no compact)
        $pdf = Pdf::loadView(
            'sec.monthly-configs.pdf',
            compact(
                'config',
                'parochialUnion',
                'parish',
                'member',
                'contributions',
                'documentNumber',  // <--- VARIÁVEL REINCLUÍDA
                'qrCodeBase64'
            )
        )->setPaper('a4');

        return $pdf->stream("config_preview_{$config->reference_date->format('m_Y')}.pdf");
    }

    /**
     * Retorna os IDs das comunidades permitidas para o usuário logado.
     */
    private function getUserCommunityIds($user): array
    {
        if (method_exists($user, 'communities')) {
            return $user->communities()->pluck('communities.id')->toArray();
        }

        return $user->community_id ? [$user->community_id] : [];
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $path = $request->file('file')->store('public/monthly_configs');

        return response()->json([
            'url' => asset('storage/' . str_replace('public/', '', $path)),
        ]);
    }
}
