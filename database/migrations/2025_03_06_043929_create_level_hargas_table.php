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
        Schema::create('level_harga', function (Blueprint $table) {
            $table->mediumIncrements('id_level_harga');
            $table->foreignId('id_produk')->references('id_produk')->on('produk')->onDelete('cascade');
            $table->string('nama_level');
            $table->bigInteger('harga_jual');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('level_harga');
    }
};
