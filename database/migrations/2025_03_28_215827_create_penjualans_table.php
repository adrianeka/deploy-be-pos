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
            $table->string('id_penjualan')
                ->primary();
            $table->unsignedSmallInteger('id_pelanggan')
                ->nullable();
            $table->foreign('id_pelanggan')
                ->references('id_pelanggan')
                ->on('pelanggan')
                ->onDelete('set null');
            $table->string('id_bayar_zakat')
                ->nullable();
            $table->foreign('id_bayar_zakat')
                ->references('id_bayar_zakat')
                ->on('bayar_zakat')
                ->onDelete('set null');
            $table->unsignedTinyInteger('id_kasir')
                ->nullable();
            $table->foreign('id_kasir')
                ->references('id_kasir')
                ->on('kasir')
                ->onDelete('set null');
            $table->unsignedBigInteger('total_harga')
                ->default(0);
            $table->enum('status_penjualan', ['lunas', 'belum lunas', 'pesanan']);
            $table->unsignedBigInteger('diskon')
                ->default(0);
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
