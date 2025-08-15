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
        Schema::create('contribution_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->foreignId('community_id')->constrained('communities')->onDelete('cascade');
            $table->foreignId('monthly_config_id')->constrained('monthly_configs')->onDelete('cascade');
            $table->decimal('amount', 10, 2)->nullable();
            $table->date('due_date')->nullable();
            $table->string('pix_code')->nullable();
            $table->string('delivery_method')->nullable(); // email, mail
            $table->string('status')->default('generated'); // generated, sent, received
            $table->timestamps();
            $table->index(['community_id', 'member_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contribution_letters');
    }
};
