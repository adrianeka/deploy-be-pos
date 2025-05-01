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
        Schema::create('pembelian', function (Blueprint $table) {
            $table->id('id_pembelian');
            $table->unsignedTinyInteger('id_pemasok');
            $table->foreign('id_pemasok')->references('id_pemasok')->on('pemasok')->onDelete('cascade');
            $table->dateTime('tanggal_pembelian');
            $table->bigInteger('total_harga')->default(0);
            $table->enum('status_pembelian', ['Diproses', 'Lunas', 'Belum Lunas']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelian');
    }
};
