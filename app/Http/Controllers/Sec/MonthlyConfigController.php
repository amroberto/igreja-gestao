<?php

namespace App\Http\Controllers\Sec;

use App\Models\Contribution;
use App\Models\MonthlyConfig;
use App\Models\ParochialUnion;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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

    public function preview(MonthlyConfig $monthlyConfig)
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
                ->with(
                    'error',
                    'Não há membros cadastrados nesta comunidade para gerar a visualização.'
                );
        }

        $config = $monthlyConfig;

        $contributions = Contribution::query()
            ->where('member_id', $member->id)
            ->where('community_id', $monthlyConfig->community_id)
            ->where('status', 'confirmed')
            ->orderByDesc('contribution_date')
            ->limit(12)
            ->get();

        $config->observations = preg_replace_callback(
            '/<img src="([^"]+)"/',
            function ($match) {
                $url = $match[1];

                if (str_starts_with($url, 'data:image')) {
                    $filename = 'monthly_configs/image_' . time() . '.png';

                    Storage::disk('public')->put(
                        $filename,
                        base64_decode(
                            preg_replace('#^data:image/\w+;base64,#i', '', $url)
                        )
                    );

                    return '<img src="' . asset('storage/' . $filename) . '"';
                }

                return $match[0];
            },
            $config->observations
        );

        try {
            $pdf = Pdf::loadView(
                'sec.monthly-configs.pdf',
                compact(
                    'config',
                    'parochialUnion',
                    'parish',
                    'member',
                    'contributions'
                )
            )->setPaper('a4');

            return $pdf->stream(
                "config_preview_{$config->reference_date->format('m_Y')}.pdf"
            );
        } catch (\Exception $e) {
            Log::error('Falha ao gerar preview do PDF', [
                'monthly_config_id' => $config->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('sec.monthly-configs.index')
                ->with('error', 'Erro ao gerar o PDF: ' . $e->getMessage());
        }
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

    protected function getUserCommunityIds(User $user): array
    {
        $communityIds = $user->communities()
            ->pluck('communities.id')
            ->toArray();

        if (empty($communityIds) && $user->community_id) {
            $communityIds = [$user->community_id];
        }

        return $communityIds;
    }
}