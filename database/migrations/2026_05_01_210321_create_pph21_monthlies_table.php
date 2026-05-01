<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pph21_monthlies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->integer('month');
            $table->integer('year');
            
            $table->decimal('gross_income', 15, 2)->default(0);
            $table->foreignId('ter_rate_id')->nullable()->constrained('ter_rates')->nullOnDelete();
            $table->decimal('tax_amount', 15, 2)->default(0);
            
            $table->string('status')->default('draft');
            $table->foreignId('journal_entry_id')->nullable()->constrained()->nullOnDelete();
            
            $table->timestamps();
            
            $table->unique(['employee_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pph21_monthlies');
    }
};
