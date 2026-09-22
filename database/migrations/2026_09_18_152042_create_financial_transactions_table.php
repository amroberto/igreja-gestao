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
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            // Relacionamentos operacionais
            $table->foreignId('financial_account_id')
                  ->constrained('financial_accounts')
                  ->comment('Conta/Caixa de origem/destino (ex: Banco Itaú, Caixa Geral)');

            $table->foreignId('financial_category_id')
                  ->constrained('financial_categories')
                  ->comment('Categoria do lançamento (mapeada para o Plano de Contas)');

            $table->foreignId('member_id')
                  ->nullable()
                  ->constrained('members')
                  ->nullOnDelete()
                  ->comment('Membro associado ao lançamento (opcional)');

            // Dados do lançamento financeiro
            $table->string('type', 10)->comment('C = Crédito/Entrada, D = Débito/Saída');
            $table->decimal('amount', 15, 2);
            $table->date('transaction_date');

            $table->string('document_number')->nullable()->comment('Número de documento, recibo ou comprovante');
            $table->text('description')->nullable()->comment('Histórico detalhado do lançamento');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
