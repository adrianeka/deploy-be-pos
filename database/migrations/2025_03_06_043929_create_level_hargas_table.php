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
        Schema::create('level_hargas', function (Blueprint $table) {
            $table->id('id_level_harga');
            $table->foreignId('id_produk')->references('id_produk')->on('produks')->onDelete('cascade');
            $table->string('nama_level');
            $table->integer('harga_jual');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('level_hargas');
    }
};
