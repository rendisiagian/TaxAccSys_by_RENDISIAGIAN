<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->string('account_code', 20);
            $table->string('account_name');
            $table->enum('account_type', [
                'asset',           // 1xxx - Aset
                'liability',       // 2xxx - Liabilitas
                'equity',          // 3xxx - Ekuitas
                'revenue',         // 4xxx - Pendapatan
                'cogs',            // 5xxx - Harga Pokok
                'expense',         // 6xxx - Beban Operasional
                'other_income',    // 7xxx - Pendapatan/Beban Lain
                'tax',             // 8xxx - Akun Pajak
            ]);
            $table->enum('normal_balance', ['debit', 'credit']);
            $table->boolean('is_header')->default(false)->comment('True = parent/group account, not for posting');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false)->comment('System accounts cannot be deleted');
            $table->text('description')->nullable();
            $table->integer('level')->default(1)->comment('Depth level in hierarchy');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'account_code']);
            $table->index(['company_id', 'account_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
