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
        Schema::create('stoks', function (Blueprint $table) {
            $table->id('id_stok');
            $table->foreignId('id_produk')->references('id_produk')->on('produks')->onDelete('cascade');
            $table->integer('jumlah_stok');
            $table->enum('jenis_stok', ['In', 'Out']);
            $table->string('jenis_transaksi');
            $table->date('tanggal_stok');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stoks');
    }
};
