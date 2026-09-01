<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Contribution;
use App\Models\ParochialUnion;
use App\Models\MonthlyConfig;
use App\Enums\PaymentMethodEnum;
use App\Enums\DeliveryMethodEnum;
use App\Helpers\PixHelper;
use App\Services\Asaas\AsaasPaymentService;
use App\Services\Asaas\AsaasCustomerService;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class BatchContributionService
{
    protected AsaasPaymentService $asaasPaymentService;
    protected AsaasCustomerService $asaasCustomerService;

    public function __construct(
        AsaasPaymentService $asaasPaymentService,
        AsaasCustomerService $asaasCustomerService
    ) {
        $this->asaasPaymentService = $asaasPaymentService;
        $this->asaasCustomerService = $asaasCustomerService;
    }

    public function processBatch(int $communityId, int $month, int $year): string
    {
        set_time_limit(600);
        ini_set('memory_limit', '512M');

        $tempDir = storage_path('app/temp');
        File::ensureDirectoryExists($tempDir);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $referenceDate = Carbon::createFromDate($year, $month, 1)->format('Y-m-01');
        $monthlyConfig = MonthlyConfig::where('community_id', $communityId)
            ->whereDate('reference_date', $referenceDate)
            ->with('community.parish')
            ->first();

        $members = Member::where('community_id', $communityId)
            ->where('is_active', true)
            ->where('is_contributor', true)
            ->get();

        $generatedPdfs = [];

        foreach ($members as $member) {
            $contribution = Contribution::where('member_id', $member->id)
                ->whereBetween('start_date', [$startDate, $endDate])
                ->first();

            if (!$contribution) {
                $dueDate = Carbon::createFromDate($year, $month, $member->due_day ?? 5);

                $contribution = Contribution::create([
                    'member_id'           => $member->id,
                    'community_id'        => $communityId,
                    'financial_entity_id' => 1,
                    'contribution_date'   => $dueDate,
                    'start_date'          => $startDate,
                    'end_date'            => null,
                    'amount'              => $member->amount ?? 0.00,
                    'type'                => 'mensal',
                    'payment_method'      => $member->payment_method?->value ?? $member->payment_method,
                    'status'              => 'pending',
                    'is_paid'             => false,
                ]);
            }

            $paymentValue = strtolower(trim((string) ($member->payment_method?->value ?? $member->payment_method)));
            $isBankSlip = in_array($paymentValue, ['bank_slip', 'bankslip', 'boleto', 'boleto_bancario']);

            if ($isBankSlip) {
                if ($contribution->payment_method !== PaymentMethodEnum::BankSlip->value) {
                    $contribution->update(['payment_method' => PaymentMethodEnum::BankSlip->value]);
                }

                if (empty($contribution->asaas_boleto_url)) {
                    $this->gerarBoletoAsaas($contribution, $member);
                }

                $contribution = $contribution->fresh();
            }

            $deliveryValue = strtolower(trim((string) ($member->delivery_method?->value ?? $member->delivery_method)));
            $isPostalDelivery = in_array($deliveryValue, ['correio', 'correios', 'postal_mail', 'postal']);

            if ($isPostalDelivery || $isBankSlip) {
                $pdfPath = $this->gerarPdfIndividual($contribution, $member, $monthlyConfig);
                if ($pdfPath) {
                    $generatedPdfs[] = $pdfPath;
                }
            }
        }

        return $this->criarArquivoZip($generatedPdfs, $month, $year);
    }

    private function gerarBoletoAsaas(Contribution $contribution, Member $member): void
    {
        try {
            if (empty($member->member_asaas_id)) {
                $cpf = preg_replace('/[^0-9]/', '', $member->cpf ?? '');
                
                if (empty($cpf) || strlen($cpf) < 11) {
                    Log::warning("Impossível criar boleto no Asaas [Membro {$member->id}]: CPF em branco ou inválido.");
                    return;
                }

                $customerData = [
                    'name'     => $member->name,
                    'cpfCnpj'  => $cpf,
                    'email'    => $member->email ?? null,
                    'phone'    => preg_replace('/[^0-9]/', '', $member->mobile_phone ?? $member->phone ?? ''),
                ];

                $asaasCustomer = $this->asaasCustomerService->createCustomer($customerData);

                if (isset($asaasCustomer['id'])) {
                    $member->update(['member_asaas_id' => $asaasCustomer['id']]);
                    $member->refresh();
                } else {
                    Log::error("Falha ao criar cliente Asaas para membro {$member->id}");
                    return;
                }
            }

            $valor = (float) $contribution->amount;
            if ($valor < 5.00) {
                Log::warning("Contribuição ID {$contribution->id} possui valor R$ {$valor}, inferior ao mínimo do Asaas (R$ 5,00).");
                return;
            }

            $paymentData = [
                'customer'    => $member->member_asaas_id,
                'value'       => $valor,
                'description' => "Contribuição mensal - " . $member->name,
                'billingType' => 'BOLETO',
            ];

            $dueDateCarbon = $contribution->contribution_date 
                ? Carbon::parse($contribution->contribution_date)
                : now();

            if ($dueDateCarbon->isPast() || $dueDateCarbon->isToday()) {
                $dueDateCarbon = now()->addDays(3);
            }

            $dueDate = $dueDateCarbon->format('Y-m-d');

            $asaasResponse = $this->asaasPaymentService->createBoletoCharge($member, $paymentData, $dueDate);
            $boletoUrl = $asaasResponse['bankSlipUrl'] ?? $asaasResponse['invoiceUrl'] ?? null;

            if (!empty($asaasResponse['id'])) {
                $contribution->update([
                    'asaas_payment_id'  => $asaasResponse['id'] ?? null,
                    'asaas_boleto_url'  => $boletoUrl,
                    'asaas_pix_qr_code' => $asaasResponse['pixQrCode'] ?? $asaasResponse['qrCode'] ?? null,
                    'asaas_pix_payload' => $asaasResponse['encodedValue'] ?? $asaasResponse['payload'] ?? null,
                ]);

                Log::info("Boleto Asaas gerado para Membro {$member->id}. URL: {$boletoUrl}");
            } else {
                Log::error("Falha na geração do boleto Asaas [Membro {$member->id}]: " . json_encode($asaasResponse));
            }
        } catch (\Throwable $e) {
            Log::error("Exceção na chamada de boleto do Asaas [Membro {$member->id}]: " . $e->getMessage());
        }
    }

    private function gerarPdfIndividual(Contribution $contribution, Member $member, ?MonthlyConfig $monthlyConfig): ?string
    {
        $tempDir = storage_path('app/temp');
        File::ensureDirectoryExists($tempDir);

        // 1. Busca União Paroquial (Titular do Pix Estático)
        $parochialUnion = ParochialUnion::first();
        $parish = $monthlyConfig?->community?->parish ?? $member->community?->parish;
        $config = $monthlyConfig;

        $contributions = Contribution::query()
            ->where('member_id', $member->id)
            ->where('community_id', $contribution->community_id)
            ->where('status', 'confirmed')
            ->orderByDesc('contribution_date')
            ->limit(12)
            ->get();

        $observationsHtml = $config?->observations;
        if ($observationsHtml) {
            $observationsHtml = preg_replace_callback('/<img[^>]+src=["\']([^"\']+)["\']/i', function ($matches) {
                $src = $matches[1];
                if (str_starts_with($src, 'data:image')) {
                    return $matches[0];
                }
                $cleanPath = preg_replace('#^https?://[^/]+/#', '', $src);
                $cleanPath = ltrim(preg_replace('#^storage/#', '', ltrim($cleanPath, '/')), '/');
                $filePath = public_path('storage/' . $cleanPath);

                if (File::exists($filePath)) {
                    $type = pathinfo($filePath, PATHINFO_EXTENSION);
                    $data = File::get($filePath);
                    return str_replace($src, 'data:image/' . $type . ';base64,' . base64_encode($data), $matches[0]);
                }
                return $matches[0];
            }, $observationsHtml);
        }

        // Formatação dos Identificadores
        $documentNumber = str_pad($contribution->id, 6, '0', STR_PAD_LEFT);
        $memberIdFormatted = str_pad($member->id, 6, '0', STR_PAD_LEFT);
        $txid = "ME{$memberIdFormatted}DOC{$documentNumber}";

        // GERAÇÃO DO PIX ESTÁTICO DA UNIÃO PAROQUIAL
        if ($parochialUnion && !empty($parochialUnion->pix_key)) {
            $qrContent = PixHelper::generatePayload(
                pixKey: $parochialUnion->pix_key,
                receiverName: $parochialUnion->pix_receiver_name ?? $parochialUnion->name ?? 'UNIAO PAROQUIAL',
                city: $parochialUnion->pix_city ?? 'SAO PAULO',
                amount: null,
                txid: $txid,
                pixKeyType: $parochialUnion->pix_key_type ?? null
            );
        } else {
            $qrContent = "MEMBRO:{$memberIdFormatted}|DOC:{$documentNumber}";
        }

        $qrCode = new QrCode(
            data: $qrContent,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 200,
            margin: 8
        );

        $writer = new PngWriter();
        $qrCodeBase64 = $writer->write($qrCode)->getDataUri();

        // Renderização da Carta Informativa (PDF)
        $cartaPdf = Pdf::loadView('sec.monthly-configs.pdf', compact(
            'config',
            'parochialUnion',
            'parish',
            'member',
            'contribution',
            'contributions',
            'documentNumber',
            'qrCodeBase64',
            'observationsHtml'
        ))->setPaper('a4')->setOption(['isRemoteEnabled' => true]);

        $tempCartaPath = "{$tempDir}/carta_{$contribution->id}.pdf";
        file_put_contents($tempCartaPath, $cartaPdf->output());

        unset($cartaPdf);
        gc_collect_cycles();

        $nomeMembro = $member->name ?? trim("{$member->first_name} {$member->last_name}");
        $nomeLimpo = preg_replace('/[^A-Za-z0-9_\-]/', '_', $nomeMembro);

        // --- MESCLAGEM DA PÁGINA 1 (CARTA) COM A PÁGINA 2 (BOLETO) ---
        if (!empty($contribution->asaas_boleto_url)) {
            Log::info("[TENTANDO MESCLAR] Baixando boleto da URL: {$contribution->asaas_boleto_url}");
            $tempBoletoPath = "{$tempDir}/boleto_{$contribution->id}.pdf";

            try {
                $response = Http::withOptions(['verify' => false, 'allow_redirects' => true])
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                    ->get($contribution->asaas_boleto_url);

                if ($response->successful() && str_starts_with($response->body(), '%PDF')) {
                    file_put_contents($tempBoletoPath, $response->body());

                    // Prefixo BOLETO_ para arquivos compostos (2 páginas)
                    $nomeArquivoFinal = "BOLETO_{$member->id}_{$nomeLimpo}.pdf";
                    $caminhoFinalAbsoluto = "{$tempDir}/{$nomeArquivoFinal}";

                    $mergedSuccess = $this->mesclarPdfsComFallback($tempCartaPath, $tempBoletoPath, $caminhoFinalAbsoluto);

                    @unlink($tempCartaPath);
                    @unlink($tempBoletoPath);

                    if ($mergedSuccess) {
                        Log::info("[SUCESSO] PDF mesclado (Carta + Boleto) para Contribuição ID {$contribution->id}");
                        return $caminhoFinalAbsoluto;
                    }
                } else {
                    Log::error("[ERRO BOLETO] Resposta inválida da URL do Asaas para contribuição ID {$contribution->id}");
                }
            } catch (\Throwable $e) {
                Log::error("[EXCEÇÃO MESCLAGEM] Contribuição ID {$contribution->id}: " . $e->getMessage());
            }
        }

        // Prefixo RECIBO_ para arquivos simples de página única
        $nomeArquivoFinal = "RECIBO_{$member->id}_{$nomeLimpo}.pdf";
        $caminhoFinalAbsoluto = "{$tempDir}/{$nomeArquivoFinal}";

        rename($tempCartaPath, $caminhoFinalAbsoluto);
        return $caminhoFinalAbsoluto;
    }

    private function mesclarPdfsComFallback(string $cartaPath, string $boletoPath, string $outputPath): bool
    {
        // 1. Tenta via pdfunite (Pacote nativo do Linux)
        exec("which pdfunite", $pdfUniteCheck);
        if (!empty($pdfUniteCheck[0])) {
            $cmd = sprintf("pdfunite %s %s %s", escapeshellarg($cartaPath), escapeshellarg($boletoPath), escapeshellarg($outputPath));
            exec($cmd, $output, $returnCode);
            if ($returnCode === 0 && file_exists($outputPath)) {
                return true;
            }
        }

        // 2. Tenta via pdftk (Pacote nativo do Linux)
        exec("which pdftk", $pdfTkCheck);
        if (!empty($pdfTkCheck[0])) {
            $cmd = sprintf("pdftk %s %s cat output %s", escapeshellarg($cartaPath), escapeshellarg($boletoPath), escapeshellarg($outputPath));
            exec($cmd, $output, $returnCode);
            if ($returnCode === 0 && file_exists($outputPath)) {
                return true;
            }
        }

        // 3. Tenta via classe FPDI caso esteja disponível no PHP
        if (class_exists('Setasign\Fpdi\Fpdi') || class_exists('Setasign\Fpdi\Fpdf\Fpdi')) {
            try {
                $fpdiClass = class_exists('Setasign\Fpdi\Fpdf\Fpdi') ? 'Setasign\Fpdi\Fpdf\Fpdi' : 'Setasign\Fpdi\Fpdi';
                $pdf = new $fpdiClass();

                foreach ([$cartaPath, $boletoPath] as $file) {
                    $count = $pdf->setSourceFile($file);
                    for ($i = 1; $i <= $count; $i++) {
                        $tpl = $pdf->importPage($i);
                        $pdf->AddPage();
                        $pdf->useTemplate($tpl);
                    }
                }
                $pdf->Output('F', $outputPath);
                return file_exists($outputPath);
            } catch (\Throwable $e) {
                Log::warning("Falha ao mesclar via FPDI no PHP: " . $e->getMessage());
            }
        }

        return false;
    }

    private function criarArquivoZip(array $pdfFiles, int $month, int $year): string
    {
        $tempDir = storage_path('app/temp');
        File::ensureDirectoryExists($tempDir);

        $zipName = "contribuicoes_correio_{$month}_{$year}_" . time() . ".zip";
        $zipPath = "{$tempDir}/{$zipName}";

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($pdfFiles as $filePath) {
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, basename($filePath));
                }
            }
            $zip->close();
        }

        foreach ($pdfFiles as $filePath) {
            @unlink($filePath);
        }

        return $zipPath;
    }
}