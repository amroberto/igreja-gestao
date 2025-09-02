<?php

namespace App\Http\Controllers\Sec;

use Carbon\Carbon;
use App\Models\Community;
use Illuminate\Http\Request;
use App\Models\MonthlyConfig;
use App\Models\ParochialUnion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MonthlyConfigController extends Controller
{
public function index()
    {
        $user = Auth::user();
        $communityIds = $this->getUserCommunityIds($user);
        Log::debug('Index: User community IDs', ['user_id' => $user->id, 'ids' => $communityIds]);
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
        Log::debug('Create: User communities', [
            'user_id' => $user->id,
            'communities' => $communities->toArray(),
            'community_id' => $user->community_id
        ]);
        if ($communities->isEmpty()) {
            return redirect()->route('sec.monthly-configs.index')
                ->with('error', 'Nenhuma comunidade associada ao seu usuário. Entre em contato com o administrador.');
        }
        return view('sec.monthly-configs.create', compact('communities'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $communityIds = $this->getUserCommunityIds($user);
        Log::debug('Store: Validating community_id', [
            'user_id' => $user->id,
            'submitted' => $request->community_id,
            'allowed_ids' => $communityIds
        ]);

        $request->validate([
            'community_id' => [
                'required',
                'exists:communities,id',
                function ($attribute, $value, $fail) use ($communityIds) {
                    if (!in_array((string)$value, array_map('strval', $communityIds))) {
                        $fail('O community_id selecionado não está associado ao seu usuário.');
                    }
                },
            ],
            'reference_date' => 'required|date_format:Y-m',
            'motto' => 'required|string|max:255',
            'observations' => 'required|string',
        ]);

        $referenceDate = Carbon::createFromFormat('Y-m', $request->reference_date)->startOfMonth();

        $config = MonthlyConfig::create([
            'community_id' => $request->community_id,
            'reference_date' => $referenceDate->toDateString(),
            'motto' => $request->motto,
            'observations' => $request->observations,
        ]);

        Log::info('MonthlyConfig created', [
            'id' => $config->id,
            'community_id' => $request->community_id,
            'reference_date' => $referenceDate->toDateString(),
            'user_id' => $user->id
        ]);

        return redirect()->route('sec.monthly-configs.index')
            ->with('success', 'Configuração criada com sucesso!');
    }

    public function edit(MonthlyConfig $monthlyConfig)
    {
        $user = Auth::user();
        $communityIds = $this->getUserCommunityIds($user);
        Log::debug('Edit: Checking monthlyConfig', [
            'user_id' => $user->id,
            'monthly_config_id' => $monthlyConfig->id,
            'community_id' => $monthlyConfig->community_id,
            'allowed_ids' => $communityIds
        ]);

        if (!in_array((string)$monthlyConfig->community_id, array_map('strval', $communityIds))) {
            return redirect()->route('sec.monthly-configs.index')
                ->with('error', 'Você não tem permissão para editar esta configuração.');
        }

        $communities = $user->communities;
        if ($communities->isEmpty() && $user->community) {
            $communities = collect([$user->community]);
        }
        return view('sec.monthly-configs.edit', compact('monthlyConfig', 'communities'));
    }

    public function update(Request $request, MonthlyConfig $monthlyConfig)
    {
        $user = Auth::user();
        $communityIds = $this->getUserCommunityIds($user);
        Log::debug('Update: Validating community_id', [
            'user_id' => $user->id,
            'submitted' => $request->community_id,
            'allowed_ids' => $communityIds,
            'monthly_config_id' => $monthlyConfig->id
        ]);

        if (!in_array((string)$monthlyConfig->community_id, array_map('strval', $communityIds))) {
            return redirect()->route('sec.monthly-configs.index')
                ->with('error', 'Você não tem permissão para editar esta configuração.');
        }

        $request->validate([
            'community_id' => [
                'required',
                'exists:communities,id',
                function ($attribute, $value, $fail) use ($communityIds) {
                    if (!in_array((string)$value, array_map('strval', $communityIds))) {
                        $fail('O community_id selecionado não está associado ao seu usuário.');
                    }
                },
            ],
            'reference_date' => 'required|date_format:Y-m',
            'motto' => 'required|string|max:255',
            'observations' => 'required|string|max:1000',
        ]);

        $referenceDate = Carbon::createFromFormat('Y-m', $request->reference_date)->startOfMonth();

        // Limpar HTML indesejado de observations
        $request->merge([
            'observations' => preg_replace('/required="required" rows="\d+" >/', '', $request->observations),
        ]);

        $monthlyConfig->update([
            'community_id' => $request->community_id,
            'reference_date' => $referenceDate->toDateString(),
            'motto' => $request->motto,
            'observations' => $request->observations,
        ]);

        Log::info('MonthlyConfig updated', [
            'id' => $monthlyConfig->id,
            'community_id' => $request->community_id,
            'reference_date' => $referenceDate->toDateString(),
            'user_id' => $user->id
        ]);

        return redirect()->route('sec.monthly-configs.index')
            ->with('success', 'Configuração atualizada com sucesso!');
    }

    public function destroy(MonthlyConfig $monthlyConfig)
    {
        $user = Auth::user();
        $communityIds = $this->getUserCommunityIds($user);
        Log::debug('Destroy: Checking monthlyConfig', [
            'user_id' => $user->id,
            'monthly_config_id' => $monthlyConfig->id,
            'community_id' => $monthlyConfig->community_id,
            'allowed_ids' => $communityIds
        ]);

        if (!in_array((string)$monthlyConfig->community_id, array_map('strval', $communityIds))) {
            return redirect()->route('sec.monthly-configs.index')
                ->with('error', 'Você não tem permissão para excluir esta configuração.');
        }

        if ($monthlyConfig->letters()->exists()) {
            return redirect()->route('sec.monthly-configs.index')
                ->with('error', 'Não é possível excluir esta configuração pois já possui cartas associadas.');
        }

        $monthlyConfig->delete();

        Log::info('MonthlyConfig deleted', [
            'id' => $monthlyConfig->id,
            'community_id' => $monthlyConfig->community_id,
            'user_id' => $user->id
        ]);

        return redirect()->route('sec.monthly-configs.index')
            ->with('success', 'Configuração excluída com sucesso!');
    }

    public function show(MonthlyConfig $monthlyConfig)
    {
        $user = Auth::user();
        $communityIds = $this->getUserCommunityIds($user);
        Log::debug('Show: Checking monthlyConfig', [
            'user_id' => $user->id,
            'monthly_config_id' => $monthlyConfig->id,
            'community_id' => $monthlyConfig->community_id,
            'allowed_ids' => $communityIds
        ]);

        if (!in_array((string)$monthlyConfig->community_id, array_map('strval', $communityIds))) {
            return redirect()->route('sec.monthly-configs.index')
                ->with('error', 'Você não tem permissão para visualizar esta configuração.');
        }

        return view('sec.monthly-configs.show', compact('monthlyConfig'));
    }

    public function preview(MonthlyConfig $monthlyConfig)
    {
        $user = Auth::user();
        $communityIds = $this->getUserCommunityIds($user);
        Log::debug('Preview: Checking monthlyConfig', [
            'user_id' => $user->id,
            'monthly_config_id' => $monthlyConfig->id,
            'community_id' => $monthlyConfig->community_id,
            'allowed_ids' => $communityIds
        ]);

        if (!in_array((string)$monthlyConfig->community_id, array_map('strval', $communityIds))) {
            return redirect()->route('sec.monthly-configs.index')
                ->with('error', 'Você não tem permissão para visualizar esta configuração.');
        }

        // Carregar união paroquial
        $parochialUnion = $monthlyConfig->community->parochialUnion ?? ParochialUnion::first();
        if (!$parochialUnion) {
            return redirect()->route('sec.monthly-configs.index')
                ->with('error', 'Nenhuma união paroquial encontrada. Entre em contato com o administrador.');
        }

        // Carregar paróquia
        $parish = $monthlyConfig->community->parish ?? null;
        if (!$parish) {
            return redirect()->route('sec.monthly-configs.index')
                ->with('error', 'Nenhuma paróquia associada à comunidade encontrada. Entre em contato com o administrador.');
        }

        // Dados fictícios para membro
        $member = (object) [
            'name' => 'Alexandre Martins Roberto',
            'cpf' => '142.033.968-08',
            'address' => 'Rua Segundo-Tenente Gildo Zanim Pistolato, 83, 07115-006 - Guarulhos/SP',
        ];

        // Dados fictícios para contribuições
        $contributions = collect([
            (object) ['date' => \Carbon\Carbon::parse('2025-01-15'), 'reference' => '01/2025', 'value' => 30.00],
            (object) ['date' => \Carbon\Carbon::parse('2024-12-16'), 'reference' => '12/2024', 'value' => 30.00],
            (object) ['date' => \Carbon\Carbon::parse('2024-11-18'), 'reference' => '11/2024', 'value' => 30.00],
            (object) ['date' => \Carbon\Carbon::parse('2024-10-15'), 'reference' => '10/2024', 'value' => 30.00],
            (object) ['date' => \Carbon\Carbon::parse('2024-09-16'), 'reference' => '09/2024', 'value' => 30.00],
            (object) ['date' => \Carbon\Carbon::parse('2024-08-14'), 'reference' => '08/2024', 'value' => 30.00],
        ]);

        // Processar imagens em observations
        $monthlyConfig->observations = preg_replace_callback('/<img src="([^"]+)"/', function ($match) {
            $url = $match[1];
            if (strpos($url, 'data:image') === 0) {
                $filename = 'image_' . time() . '.png';
                Storage::disk('public')->put($filename, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $url)));
                return '<img src="' . asset('storage/' . $filename) . '"';
            }
            return $match[0];
        }, $monthlyConfig->observations);

        try {
            $pdf = Pdf::loadView('sec.monthly-configs.pdf', compact('config', 'parochialUnion', 'parish', 'member', 'contributions'))
                ->setPaper('a4');

            Log::info('PDF preview generated for MonthlyConfig', [
                'monthly_config_id' => $monthlyConfig->id,
                'user_id' => $user->id
            ]);

            return $pdf->stream("config_preview_{$monthlyConfig->reference_date->format('m_Y')}.pdf");
        } catch (\Exception $e) {
            Log::error('Failed to generate PDF preview', [
                'monthly_config_id' => $monthlyConfig->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('sec.monthly-configs.index')
                ->with('error', 'Erro ao gerar o PDF: ' . $e->getMessage());
        }
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $path = $request->file('file')->store('public/monthly_configs');
        $url = asset('storage/' . str_replace('public/', '', $path));

        return response()->json(['url' => $url]);
    }

    protected function getUserCommunityIds($user): array
    {
        $communityIds = $user->communities()->pluck('communities.id')->toArray();
        if (empty($communityIds) && $user->community_id) {
            $communityIds = [$user->community_id];
        }
        return $communityIds;
    }
}
