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
            $table->unsignedBigInteger('id_pelanggan')->nullable();
            $table->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan')->onDelete('cascade');
            $table->unsignedBigInteger('id_bayar_zakat')->nullable(); // hanya kolom
            $table->unsignedTinyInteger('id_tipe_transfer')->nullable(); 
            $table->unsignedBigInteger('id_kasir');
            $table->foreign('id_kasir')->references('id_kasir')->on('kasir')->onDelete('cascade'); 
            $table->dateTime('tanggal_penjualan');
            $table->bigInteger('total_harga')->default(0);
            $table->enum('status_penjualan', ['lunas', 'belum lunas', 'pesanan']);
            $table->boolean('status_retur')->default(false);
            $table->tinyInteger('diskon')->default(0);
            $table->timestamps();
        });
        
        // Tambah foreign key setelah tabel pasti ada
        Schema::table('penjualan', function (Blueprint $table) {
            $table->foreign('id_bayar_zakat')->references('id_bayar_zakat')->on('bayar_zakat')->onDelete('cascade');
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
