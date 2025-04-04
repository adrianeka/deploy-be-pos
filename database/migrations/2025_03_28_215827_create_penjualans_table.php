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
        Schema::create('penjualan', function (Blueprint $table) {
            $table->string('id_penjualan')->primary();
            $table->foreignId('id_pelanggan')->references('id_pelanggan')->on('pelanggan')->onDelete('cascade');
            $table->foreignId('id_kasir')->references('id_kasir')->on('kasir')->onDelete('cascade');
            $table->date('tanggal_penjualan');
            $table->integer('total_harga')->default(0);
            $table->enum('status_penjualan', ['Lunas', 'Belum Lunas', 'Pesanan']);
            $table->enum('status_retur', [true, false]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan');
    }
};
