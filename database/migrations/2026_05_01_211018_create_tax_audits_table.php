<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('document_type'); // SP2DK, STP, SKPKB, SKPLB, SKPN
            $table->string('document_number');
            $table->date('document_date');
            
            $table->integer('tax_period_year');
            $table->decimal('principal_amount', 15, 2)->default(0); // Pokok Pajak
            $table->decimal('penalty_amount', 15, 2)->default(0); // Sanksi/Denda
            
            $table->string('status')->default('received'); // received, responded, closed
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_audits');
    }
};
