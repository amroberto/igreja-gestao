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
            $table->decimal('amount', 10, 2);
            $table->date('start_date'); // Mês/ano inicial
            $table->date('end_date')->nullable(); // Mês/ano final (opcional)
            $table->string('description')->nullable();
            $table->index('community_id', 'contribution_date');
            $table->index('financial_entity_id', 'contributiopn_date');
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
