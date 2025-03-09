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
            $table->foreignId('id_produk')
                ->constrained('produks', 'id_produk')
                ->onDelete('cascade');
            $table->string('nama_level'); // Standard, Grosir, Reseller
            $table->decimal('harga_jual', 10, 2)->default(0);
            $table->boolean('is_applied')->default(false); // Harga aktif
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
