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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('cpf')->unique();
            $table->string('rg')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('cellphone')->nullable();
            $table->string('cep')->nullable();
            $table->string('address')->nullable();
            $table->string('number')->nullable();
            $table->string('complement')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->foreignId('community_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_contributor')->default(true);
            $table->string('status')->default('ativo');
            $table->string('marital_status')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('delivery_method')->default('Postal Mail');
            $table->string('payment_method')->default('Transferência Bancária');
            $table->tinyInteger('due_day')->default(5);
            $table->decimal('amount', 8, 2)->default(0.00);
            $table->longText('observations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
