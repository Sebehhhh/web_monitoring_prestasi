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
    Schema::create('tingkat_penghargaan', function (Blueprint $table) {
        $table->id();
        $table->string('tingkat', 30); // contoh: Sekolah, Kab/Kota, Provinsi, Nasional, Internasional
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tingkat_penghargaan');
    }
};
