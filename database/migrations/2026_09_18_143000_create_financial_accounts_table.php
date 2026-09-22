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
        Schema::create('financial_accounts', function (Blueprint $table) {
            $table->id();
            
            // Pertence a uma Comunidade
            $table->foreignId('community_id')->constrained('communities')->cascadeOnDelete();
            $table->string('name');

            // Tipo de Conta Financeira (ex: Conta Bancária, Caixa, etc.)
            $table->string('type')->default('bank_account');
            
            // Dados Bancários
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('agency_number')->nullable();

            // Saldo de Abertura / Implantação
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->date('opening_balance_date')->nullable();

            // Status e Controle
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_accounts');
    }
};
