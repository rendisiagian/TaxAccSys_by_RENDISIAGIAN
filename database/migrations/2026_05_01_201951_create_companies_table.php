<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('npwp', 20)->nullable();
            $table->string('nitku', 22)->nullable()->comment('Nomor Identitas Tempat Kegiatan Usaha');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('business_type')->nullable()->comment('Jenis Usaha');
            $table->string('klu_code', 10)->nullable()->comment('Klasifikasi Lapangan Usaha');
            $table->string('tax_office')->nullable()->comment('KPP terdaftar');
            $table->enum('company_type', ['regular', 'construction'])->default('regular');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
