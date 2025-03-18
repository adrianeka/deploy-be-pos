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
        Schema::create('produks', function (Blueprint $table) {
            $table->id('id_produk');
            $table->string('nama_produk');
            $table->foreignId('id_kategori')->nullable()->references('id_kategori')->on('kategoris')->onDelete('set null');
            $table->foreignId('id_pemilik')->references('id_pemilik')->on('pemiliks')->onDelete('cascade');
            $table->foreignId('id_satuan')->nullable()->references('id_satuan')->on('satuans')->onDelete('set null');
            $table->string('foto_produk')->nullable();
            $table->integer('harga_beli');
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
        Schema::dropIfExists('produks');
    }
};
