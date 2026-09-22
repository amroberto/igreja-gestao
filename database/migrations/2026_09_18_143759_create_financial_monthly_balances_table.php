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
        Schema::create('financial_monthly_balances', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('financial_account_id')->constrained('financial_accounts')->cascadeOnDelete();
            $table->string('reference_month', 7); // Formato: YYYY-MM

            $table->decimal('initial_balance', 15, 2)->default(0.00);
            $table->decimal('total_in', 15, 2)->default(0.00);
            $table->decimal('total_out', 15, 2)->default(0.00);
            $table->decimal('final_balance', 15, 2)->default(0.00);

            $table->boolean('is_closed')->default(false);

            // Nome do índice reduzido para menos de 64 caracteres
            $table->unique(['financial_account_id', 'reference_month'], 'fin_balances_account_month_unique');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_monthly_balances');
    }
};
