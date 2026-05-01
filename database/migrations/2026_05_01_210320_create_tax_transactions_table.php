<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('tax_type'); // ppn_out, ppn_in, pph_22, pph_23, pph_4_2
            $table->date('transaction_date');
            
            $table->string('document_number')->nullable(); // Bupot / Faktur No
            $table->string('counterpart_name')->nullable();
            $table->string('counterpart_tin')->nullable(); // NPWP
            
            $table->decimal('tax_base', 15, 2)->default(0); // DPP
            $table->decimal('tax_rate', 5, 2)->default(0);  // Tarif
            $table->decimal('tax_amount', 15, 2)->default(0);
            
            $table->string('status')->default('draft');
            $table->foreignId('journal_entry_id')->nullable()->constrained()->nullOnDelete();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_transactions');
    }
};
