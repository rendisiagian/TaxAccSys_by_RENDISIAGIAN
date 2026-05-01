<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ter_rates', function (Blueprint $table) {
            $table->id();
            $table->string('category', 1); // A, B, C
            $table->decimal('min_bruto', 15, 2);
            $table->decimal('max_bruto', 15, 2)->nullable(); // null means infinity
            $table->decimal('rate_percentage', 5, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ter_rates');
    }
};
