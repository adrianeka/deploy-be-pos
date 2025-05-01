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
        Schema::create('bayar_zakat', function (Blueprint $table) {
            $table->id('id_bayar_zakat');
            $table->unsignedTinyInteger('id_pemilik');
            $table->foreign('id_pemilik')->references('id_pemilik')->on('pemilik')->onDelete('cascade');
            $table->unsignedTinyInteger('id_penerima_zakat');
            $table->foreign('id_penerima_zakat')->references('id_penerima_zakat')->on('penerima_zakat')->onDelete('cascade');
            $table->unsignedTinyInteger('id_metode_pembayaran');
            $table->foreign('id_metode_pembayaran')->references('id_metode_pembayaran')->on('metode_pembayaran')->onDelete('cascade');
            $table->bigInteger('modal_terjual');
            $table->bigInteger('nominal_zakat');
            $table->dateTime('tanggal_bayar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bayar_zakat');
    }
};
