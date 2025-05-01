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
            $table->unsignedTinyInteger('id_kategori')->nullable();
            $table->foreign('id_kategori')->references('id_kategori')->on('kategori')->onDelete('set null');
            $table->unsignedTinyInteger('id_pemilik');
            $table->foreign('id_pemilik')->references('id_pemilik')->on('pemilik')->onDelete('cascade');
            $table->unsignedTinyInteger('id_satuan')->nullable();
            $table->foreign('id_satuan')->references('id_satuan')->on('satuan')->onDelete('set null');
            $table->string('foto_produk')->nullable();
            $table->bigInteger('harga_beli');
            $table->smallInteger('stok_minimum');
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
