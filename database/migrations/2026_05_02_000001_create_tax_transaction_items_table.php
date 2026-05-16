<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_transactions', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('status')->comment('Path ke file dokumen fisik PDF');
        });

        Schema::create('tax_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_transaction_id')->constrained()->cascadeOnDelete();
            
            $table->string('item_code')->nullable()->comment('Kode barang/jasa');
            $table->text('item_name')->comment('Nama barang/jasa');
            
            $table->decimal('quantity', 15, 4)->default(0);
            $table->string('unit')->nullable()->comment('Satuan (misal: Unit, Pcs)');
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_transaction_items');
        
        Schema::table('tax_transactions', function (Blueprint $table) {
            $table->dropColumn('file_path');
        });
    }
};
