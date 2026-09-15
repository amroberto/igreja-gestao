<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Contribution;
use App\Models\ParochialUnion;
use App\Models\MonthlyConfig;
use App\Enums\PaymentMethodEnum;
use App\Enums\DeliveryMethodEnum;
use App\Enums\StatusPaymentEnum;
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

        // 1. Busca TODOS os membros ativos e contribuintes da comunidade
        $members = Member::where('community_id', $communityId)
            ->where('is_active', true)
            ->where('is_contributor', true)
            ->get();

        $generatedPdfs = [];

        foreach ($members as $member) {
            // Garante/Cria a contribuição do mês para O MEMBRO (para qualquer método de entrega/pagamento)
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

            // Normalização do Método de Pagamento
            $memberPaymentMethod = $member->payment_method instanceof PaymentMethodEnum
                ? $member->payment_method->value
                : strtolower(trim((string) ($member->payment_method ?? '')));

            $isBankSlip = ($memberPaymentMethod === PaymentMethodEnum::BankSlip->value);

            // 2. Se for Boleto Bancário, gera cobrança no Asaas para TODOS os membros com essa opção
            if ($isBankSlip) {
                if ($contribution->payment_method !== PaymentMethodEnum::BankSlip->value) {
                    $contribution->update(['payment_method' => PaymentMethodEnum::BankSlip->value]);
                }

                // Verifica se JÁ EXISTE outra contribuição CONFIRMADA cobrindo qualquer parte do período
                $hasConfirmedContribution = Contribution::where('member_id', $member->id)
                    ->where('id', '!=', $contribution->id)
                    ->where('status', 'confirmed')
                    ->where(function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('start_date', [$startDate, $endDate])
                                ->orWhere(function ($query) use ($startDate, $endDate) {
                                    $query->whereNotNull('end_date')
                                            ->whereBetween('end_date', [$startDate, $endDate]);
                                })
                                ->orWhere(function ($query) use ($startDate, $endDate) {
                                    $query->where('start_date', '<=', $startDate)
                                            ->where(function ($sub) use ($endDate) {
                                                $sub->where('end_date', '>=', $endDate)
                                                    ->orWhereNull('end_date');
                                            });
                                });
                    })->exists();

                // SÓ gera o boleto no Asaas se NÃO houver contribuição confirmada no período
                if (!$hasConfirmedContribution) {
                    if(empty($contribution->asaas_payment_id)) {
                        $this->gerarBoletoAsaas($contribution, $member);
                    }
                }else{
                    Log::info("Membro {$member->id} já possui contribuição confirmada no período {$startDate->format('Y-m-d')} a {$endDate->format('Y-m-d')}. Boleto não será gerado.");
                }
                $contribution = $contribution->fresh();
            }

            // Normalização do Método de Entrega
            $memberDeliveryMethod = $member->delivery_method instanceof DeliveryMethodEnum 
                ? $member->delivery_method->value 
                : strtolower(trim((string) ($member->delivery_method ?? '')));

            $isPostalDelivery = ($memberDeliveryMethod === DeliveryMethodEnum::CORREIO->value);

            // 3. APENAS inclui no ZIP se a entrega for via CORREIO
            if ($isPostalDelivery) {
                // Passamos a informação se deve ou não anexar a folha do boleto
                $pdfPath = $this->gerarPdfIndividual($contribution, $member, $monthlyConfig, $isBankSlip);
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
                    'name'     => $member->name ?? trim("{$member->first_name} {$member->last_name}"),
                    'cpfCnpj'  => $cpf,
                    'email'    => $member->email ?? null,
                    'phone'    => preg_replace('/[^0-9]/', '', $member->cellphone ?? $member->phone ?? ''),
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
                'description' => "Contribuição mensal - " . ($member->name ?? trim("{$member->first_name} {$member->last_name}")),
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

    private function gerarPdfIndividual(Contribution $contribution, Member $member, ?MonthlyConfig $monthlyConfig, bool $isBankSlip = false): ?string
    {
        $tempDir = storage_path('app/temp');
        File::ensureDirectoryExists($tempDir);

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

        $documentNumber = str_pad($contribution->id, 6, '0', STR_PAD_LEFT);
        $memberIdFormatted = str_pad($member->id, 6, '0', STR_PAD_LEFT);
        $txid = "ME{$memberIdFormatted}DOC{$documentNumber}";

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

        // Renderização da Carta Informativa / Recibo (Página 1)
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

        // Checa se o boleto está PAGO
        $contributionStatus = $contribution->status instanceof StatusPaymentEnum
            ? $contribution->status->value
            : (string) ($contribution->status ?? '');

        $isPaid = (bool) $contribution->is_paid 
            || in_array(strtolower(trim($contributionStatus)), ['paid', 'confirmed', 'pago', 'confirmado']);


        // Define o intervalo de verificação com base na contribuição atual ou no mês de referência
        $startDate = $contribution->start_date 
            ? Carbon::parse($contribution->start_date)->startOfMonth() 
            : Carbon::parse($contribution->contribution_date)->startOfMonth();

        $endDate = $contribution->end_date 
            ? Carbon::parse($contribution->end_date)->endOfMonth() 
            : Carbon::parse($contribution->contribution_date)->endOfMonth();

        // Verifica se existe outra contribuição registrada/confirmada no período
        $hasContributionInPeriod = Contribution::where('member_id', $member->id)
            ->where('id', '!=', $contribution->id)
            ->where('status', 'confirmed')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhere(function ($query) use ($startDate, $endDate) {
                          $query->whereNotNull('end_date')
                          ->whereBetween('end_date', [$startDate, $endDate]);
                                })
                                ->orWhere(function ($query) use ($startDate, $endDate) {
                                    $query->where('start_date', '<=', $startDate)
                                          ->where(function ($sub) use ($endDate) {
                                              $sub->where('end_date', '>=', $endDate)
                                                  ->orWhereNull('end_date');
                                          });
                                });
            })->exists();

        // Se o boleto já está pago OU se já existe contribuição confirmada no período, cancela a anexação do boleto
        if($isPaid || $hasContributionInPeriod) {
            Log::info("[INFO] Contribuição ID {$contribution->id} já está PAGA ou existe outra contribuição confirmada no período. Boleto não será anexado.");
            $isBankSlip = false;
        }
    
        // --- MESCLAGEM COM PÁGINA 2 (BOLETO) SOMENTE SE FOR BOLETO E NÃO ESTIVER PAGO/CONFIRMADO ---
        if ($isBankSlip && !empty($contribution->asaas_boleto_url)) {
            Log::info("[TENTANDO MESCLAR] Baixando boleto pendente da URL: {$contribution->asaas_boleto_url}");
            $tempBoletoPath = "{$tempDir}/boleto_{$contribution->id}.pdf";

            try {
                $response = Http::withOptions(['verify' => false, 'allow_redirects' => true])
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                    ->get($contribution->asaas_boleto_url);

                if ($response->successful() && str_starts_with($response->body(), '%PDF')) {
                    file_put_contents($tempBoletoPath, $response->body());

                    $nomeArquivoFinal = "BOLETO_{$member->id}_{$nomeLimpo}.pdf";
                    $caminhoFinalAbsoluto = "{$tempDir}/{$nomeArquivoFinal}";

                    $mergedSuccess = $this->mesclarPdfsComFallback($tempCartaPath, $tempBoletoPath, $caminhoFinalAbsoluto);

                    @unlink($tempCartaPath);
                    @unlink($tempBoletoPath);

                    if ($mergedSuccess) {
                        Log::info("[SUCESSO] PDF mesclado (Carta + Boleto Pendente) para Contribuição ID {$contribution->id}");
                        return $caminhoFinalAbsoluto;
                    }
                } else {
                    Log::error("[ERRO BOLETO] Resposta inválida da URL do Asaas para contribuição ID {$contribution->id}");
                }
            } catch (\Throwable $e) {
                Log::error("[EXCEÇÃO MESCLAGEM] Contribuição ID {$contribution->id}: " . $e->getMessage());
            }
        }

        // Se o boleto já está PAGO ou é outro método de pagamento: gera apenas o PDF de 1 página (Carta/Recibo)
        $nomeArquivoFinal = "RECIBO_{$member->id}_{$nomeLimpo}.pdf";
        $caminhoFinalAbsoluto = "{$tempDir}/{$nomeArquivoFinal}";

        rename($tempCartaPath, $caminhoFinalAbsoluto);
        return $caminhoFinalAbsoluto;
    }

    private function mesclarPdfsComFallback(string $cartaPath, string $boletoPath, string $outputPath): bool
    {
        exec("which pdfunite", $pdfUniteCheck);
        if (!empty($pdfUniteCheck[0])) {
            $cmd = sprintf("pdfunite %s %s %s", escapeshellarg($cartaPath), escapeshellarg($boletoPath), escapeshellarg($outputPath));
            exec($cmd, $output, $returnCode);
            if ($returnCode === 0 && file_exists($outputPath)) {
                return true;
            }
        }

        exec("which pdftk", $pdfTkCheck);
        if (!empty($pdfTkCheck[0])) {
            $cmd = sprintf("pdftk %s %s cat output %s", escapeshellarg($cartaPath), escapeshellarg($boletoPath), escapeshellarg($outputPath));
            exec($cmd, $output, $returnCode);
            if ($returnCode === 0 && file_exists($outputPath)) {
                return true;
            }
        }

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