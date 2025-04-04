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
        Schema::create('produk', function (Blueprint $table) {
            $table->id('id_produk');
            $table->string('nama_produk');
            $table->foreignId('id_kategori')->nullable()->references('id_kategori')->on('kategori')->onDelete('set null');
            $table->foreignId('id_pemilik')->references('id_pemilik')->on('pemilik')->onDelete('cascade');
            $table->foreignId('id_satuan')->nullable()->references('id_satuan')->on('satuan')->onDelete('set null');
            $table->string('foto_produk')->nullable();
            $table->bigInteger('harga_beli');
            $table->integer('stok_minimum');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
