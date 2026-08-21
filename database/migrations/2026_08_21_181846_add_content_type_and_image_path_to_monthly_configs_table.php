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
        Schema::table('monthly_configs', function (Blueprint $table) {
            // Adiciona content_type apenas se não existir
            if (!Schema::hasColumn('monthly_configs', 'content_type')) {
                $table->string('content_type')->default('text')->after('motto');
            }

            // Adiciona image_path apenas se não existir
            if (!Schema::hasColumn('monthly_configs', 'image_path')) {
                $table->string('image_path')->nullable()->after('content_type');
            }

            // Altera observations para nullable
            if (Schema::hasColumn('monthly_configs', 'observations')) {
                $table->text('observations')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_configs', function (Blueprint $table) {
            if (Schema::hasColumn('monthly_configs', 'content_type')) {
                $table->dropColumn('content_type');
            }

            if (Schema::hasColumn('monthly_configs', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });
    }
};
