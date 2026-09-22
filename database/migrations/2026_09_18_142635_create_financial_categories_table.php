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
        Schema::create('financial_categories', function (Blueprint $table) {
            $table->id();
            // Vinculo obrigatório com o Plano de Contas Oficial
            $table->foreignId('account_plan_id')
                  ->constrained('account_plans')
                  ->cascadeOnDelete();

            $table->string('name'); // Nome/Descrição amigável da Categoria
            $table->string('type', 10)->default('D')->comment('C = Crédito, D = Débito');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_categories');
    }
};
