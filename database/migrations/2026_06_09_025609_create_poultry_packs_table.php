<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('poultry_packs', function (Blueprint $table) {
            $table->id();
            $table->string('full_barcode')->unique();
            $table->string('gtin', 14);
            $table->date('best_before_date');
            $table->decimal('weight_kg', 6, 3); // Covers up to 999.999 kg
            $table->string('serial_number', 20);
            $table->timestamps();
     });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poultry_packs');
    }
};
