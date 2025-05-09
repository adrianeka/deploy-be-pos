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
        Schema::create('stok', function (Blueprint $table) {
            $table->mediumIncrements('id_stok');
            $table->foreignId('id_produk')->references('id_produk')->on('produk')->onDelete('cascade');
            $table->bigInteger('jumlah_stok');
            $table->enum('jenis_stok', ['In', 'Out'])->nullable();
            $table->string('jenis_transaksi')->nullable();
            $table->text('keterangan')->nullable();
            $table->dateTime('tanggal_stok');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stok');
    }
};
