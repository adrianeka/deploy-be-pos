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
        Schema::create('pembayaran_penjualan', function (Blueprint $table) {
            $table->string('id_penjualan');
            $table->foreign('id_penjualan')->references('id_penjualan')->on('penjualan')->onDelete('cascade');
            $table->foreignId('id_pembayaran')->references('id_pembayaran')->on('pembayaran')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_penjualan');
    }
};
