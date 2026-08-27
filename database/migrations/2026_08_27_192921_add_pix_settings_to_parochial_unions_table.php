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
        Schema::table('parochial_unions', function (Blueprint $table) {
            $table->string('pix_key')->nullable();
            $table->string('pix_key_type', 20)->nullable();
            $table->string('pix_receiver_name')->nullable();
            $table->string('pix_city', 15)->nullable();
            $table->boolean('pix_static_enabled')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parochial_unions', function (Blueprint $table) {
            Schema::dropIfExists('parochial_unions');
        });
    }
};
