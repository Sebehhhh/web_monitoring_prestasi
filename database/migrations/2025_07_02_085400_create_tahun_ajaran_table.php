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
        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tahun_ajaran', 10)->comment('Format: 2024/2025');
            $table->enum('semester', ['ganjil', 'genap'])->comment('Semester: ganjil/genap');
            $table->date('tanggal_mulai_tahun')->comment('Start of academic year');
            $table->date('tanggal_selesai_tahun')->comment('End of academic year');
            $table->date('tanggal_mulai_semester')->comment('Start of current semester');
            $table->date('tanggal_selesai_semester')->comment('End of current semester');
            $table->boolean('is_active')->default(false);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            // Unique constraint for year + semester combination
            $table->unique(['nama_tahun_ajaran', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tahun_ajaran');
    }
};
