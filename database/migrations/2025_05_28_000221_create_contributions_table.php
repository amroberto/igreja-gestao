<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            $table->date('contribution_date'); // Data da contribuição
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->foreignId('financial_entity_id')->constrained()->onDelete('cascade');
            $table->foreignId('community_id')->constrained()->onDelete('cascade');
            $table->string('payment_method')->nullable(); // Método de pagamento (opcional)
            $table->string('status', 50)->default('pending');
            $table->decimal('amount', 10, 2);
            $table->date('start_date'); // Mês/ano inicial
            $table->date('end_date')->nullable(); // Mês/ano final (opcional)
            $table->string('description')->nullable();
            $table->text('asaas_boleto_url')->nullable(); // URL do boleto gerado no Asaas
            $table->text('asaas_payment_id')->nullable(); // ID do pagamento no Asaas
            $table->text('asaas_pix_qr_code')->nullable(); // QR code PIX do Asaas
            $table->text('asaas_pix_payload')->nullable(); // Payload PIX do Asaas
            $table->boolean('is_paid')->default(false); // Indica se a contribuição foi paga
            $table->index(['community_id', 'contribution_date'], 'contributions_community_date_index');
            $table->index(['financial_entity_id', 'contribution_date'], 'contributions_financial_date_index');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};
