<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('contributions', function (Blueprint $table) {
            $table->text('asaas_pix_qr_code')->nullable()->after('asaas_payment_id');
            $table->text('asaas_pix_payload')->nullable()->after('asaas_pix_qr_code');
        });
    }

    public function down()
    {
        Schema::table('contributions', function (Blueprint $table) {
            $table->dropColumn(['asaas_pix_qr_code', 'asaas_pix_payload']);
        });
    }
};
