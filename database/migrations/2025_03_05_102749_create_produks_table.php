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
            $table->foreignId('id_kategori')->nullable()
                ->constrained('kategoris', 'id_kategori')
                ->onDelete('set null');
            $table->string('gambar')->nullable();
            $table->foreignId('id_satuan')->nullable()
                ->constrained('satuans', 'id_satuan')
                ->onDelete('set null');
            $table->decimal('harga_beli', 10, 2);
            $table->integer('stok');
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
