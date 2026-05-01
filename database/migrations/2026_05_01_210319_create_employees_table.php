<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('nik', 16)->nullable();
            $table->string('npwp', 20)->nullable();
            $table->string('nitku', 22)->nullable();
            
            $table->string('name');
            $table->string('employee_type')->default('tetap'); // tetap, tidak_tetap, bukan_pegawai
            $table->string('ptkp_status', 10); // TK/0, K/1, dll
            $table->string('ter_category', 1); // A, B, C
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
