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
        Schema::create('pembayaran_pembelian', function (Blueprint $table) {
            $table->id('id_pembayaran_pembelian');
            $table->string('id_pembelian');
            $table->foreign('id_pembelian')->references('id_pembelian')->on('pembelian')->onDelete('cascade');
            $table->foreignId('id_pembayaran')->references('id_pembayaran')->on('pembayaran')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_pembelian');
    }
};
