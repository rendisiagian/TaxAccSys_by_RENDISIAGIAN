<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_transactions', function (Blueprint $table) {
            $table->string('identity_type')->nullable()->after('counterpart_tin')->comment('NPWP/NIK/Paspor');
            $table->string('tax_object_code')->nullable()->after('tax_type')->comment('Kode Objek Pajak dari sheet REF BPPU');
            $table->string('facility_code')->nullable()->after('tax_amount')->comment('Kode Fasilitas (Tanpa Fasilitas, SKB, DTP, dll)');
            $table->string('facility_number')->nullable()->after('facility_code')->comment('Nomor Dokumen Fasilitas');
            $table->string('document_code')->nullable()->after('document_number')->comment('Kode Dokumen Referensi');
            $table->date('document_date')->nullable()->after('document_code')->comment('Tanggal Dokumen Referensi');
            $table->string('ip_payment_code')->nullable()->after('facility_number')->comment('Kode Pembayaran IP');
            $table->foreignId('related_transaction_id')->nullable()->after('journal_entry_id')
                  ->constrained('tax_transactions')
                  ->nullOnDelete()
                  ->comment('Link PPh Unifikasi ke PPN Masukan/Keluaran dari invoice yang sama');
        });
    }

    public function down(): void
    {
        Schema::table('tax_transactions', function (Blueprint $table) {
            $table->dropForeign(['related_transaction_id']);
            $table->dropColumn([
                'identity_type',
                'tax_object_code',
                'facility_code',
                'facility_number',
                'document_code',
                'document_date',
                'ip_payment_code',
                'related_transaction_id'
            ]);
        });
    }
};
