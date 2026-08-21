<?php

namespace App\Http\Controllers\Sec;

use App\Helpers\PixHelper;
use App\Models\Contribution;
use App\Models\MonthlyConfig;
use App\Models\ParochialUnion;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
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
        $validated = $request->validate([
            'community_id'   => 'required|exists:communities,id',
            'reference_date' => 'required|date_format:Y-m',
            'motto'          => 'required|string|max:255',
            'content_type'   => 'required|in:text,image',
            'observations'   => 'required_if:content_type,text|nullable|string',
            'image_file'     => 'required_if:content_type,image|nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $validated['reference_date'] = $validated['reference_date'] . '-01';

        // Upload de imagem caso o tipo selecionado seja 'image'
        if ($request->content_type === 'image' && $request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('monthly_configs', 'public');
            $validated['image_path'] = $path;
            $validated['observations'] = null; // Limpa texto se houver
        }

        MonthlyConfig::create($validated);

        return redirect()->route('sec.monthly-configs.index')
            ->with('success', __('Configuração criada com sucesso!'));
    }

    public function edit($id)
    {
        $monthlyConfig = MonthlyConfig::findOrFail($id);
        $communities = Community::orderBy('name')->get();

        return view('sec.monthly_configs.edit', compact('monthlyConfig', 'communities'));
    }

    public function update(Request $request, $id)
    {
        $monthlyConfig = MonthlyConfig::findOrFail($id);

        $validated = $request->validate([
            'community_id'   => 'required|exists:communities,id',
            'reference_date' => 'required|date_format:Y-m',
            'motto'          => 'required|string|max:255',
            'content_type'   => 'required|in:text,image',
            'observations'   => 'required_if:content_type,text|nullable|string',
            'image_file'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $validated['reference_date'] = $validated['reference_date'] . '-01';

        if ($request->content_type === 'image') {
            if ($request->hasFile('image_file')) {
                // Remove a imagem antiga se existir
                if ($monthlyConfig->image_path && Storage::disk('public')->exists($monthlyConfig->image_path)) {
                    Storage::disk('public')->delete($monthlyConfig->image_path);
                }
                $validated['image_path'] = $request->file('image_file')->store('monthly_configs', 'public');
            }
            $validated['observations'] = null;
        } else {
            // Se trocou para texto, exclui imagem salva anteriormente se houver
            if ($monthlyConfig->image_path && Storage::disk('public')->exists($monthlyConfig->image_path)) {
                Storage::disk('public')->delete($monthlyConfig->image_path);
            }
            $validated['image_path'] = null;
        }

        $monthlyConfig->update($validated);

        return redirect()->route('sec.monthly-configs.index')
            ->with('success', __('Configuração atualizada com sucesso!'));
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


        $observationsHtml = $config->observations;

        if ($observationsHtml) {
            // Busca todas as tags <img> dentro do HTML de observações
            $observationsHtml = preg_replace_callback('/<img[^>]+src=["\']([^"\']+)["\']/i', function ($matches) {
                $src = $matches[1];

                // Se já for Base64, mantém como está
                if (str_starts_with($src, 'data:image')) {
                    return $matches[0];
                }

                // Se for uma URL completa ou caminho relativo com /storage/
                $cleanPath = preg_replace('#^https?://[^/]+/#', '', $src); // Remove domínio se houver
                $cleanPath = ltrim(preg_replace('#^storage/#', '', ltrim($cleanPath, '/')), '/');

                $filePath = public_path('storage/' . $cleanPath);

                // Se o arquivo físico existir no servidor, converte para Base64
                if (File::exists($filePath)) {
                    $type = pathinfo($filePath, PATHINFO_EXTENSION);
                    $data = File::get($filePath);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

                    return str_replace($src, $base64, $matches[0]);
                }

                return $matches[0];
            }, $observationsHtml);
        }

        // Número de documento simplificado para o preview (ex: 000001)
        $documentNumber = '000001';

        // TXID do Pix mantendo a relação Membro/Documento no Bacen (ex: ME000129DOC000001)
        $memberIdFormatted = str_pad($member->id ?? 1, 6, '0', STR_PAD_LEFT);
        $txid = "ME{$memberIdFormatted}DOC{$documentNumber}";

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
                'documentNumber',
                'qrCodeBase64',
                'observationsHtml' // <--- PASSE A VARIÁVEL TRATADA
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
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:1024',
        ]);

        if ($request->hasFile('file')) {
            // Salva a imagem no disco público (storage/app/public/uploads)
            $path = $request->file('file')->store('uploads', 'public');

            // Retorna a URL acessível publicamente
            return response()->json([
                'url' => asset('storage/' . $path)
            ]);
        }

        return response()->json(['message' => 'Nenhum arquivo enviado.'], 400);
    }
}
