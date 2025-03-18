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
        Schema::create('penerima_zakats', function (Blueprint $table) {
            $table->id('id_penerima_zakat');
            $table->foreignId('id_pemilik')->references('id_pemilik')->on('pemiliks')->onDelete('cascade');
            $table->string('nama_penerima');
            $table->string('no_telp', 15);
            $table->string('no_rekening', 16);
            $table->string('nama_bank');
            $table->string('rekening_atas_nama');
            $table->text('alamat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerima_zakats');
    }
};
