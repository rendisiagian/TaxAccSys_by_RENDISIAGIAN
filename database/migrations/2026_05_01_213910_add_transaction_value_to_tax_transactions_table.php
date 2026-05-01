<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_transactions', function (Blueprint $table) {
            $table->string('faktur_code', 3)->nullable()->after('tax_type');
            $table->decimal('transaction_value', 15, 2)->default(0)->after('counterpart_tin'); // Nilai Jual / Penggantian
        });
    }

    public function down(): void
    {
        Schema::table('tax_transactions', function (Blueprint $table) {
            $table->dropColumn(['faktur_code', 'transaction_value']);
        });
    }
};
