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
        Schema::create('pembelian', function (Blueprint $table) {
            $table->string('id_pembelian')->primary();
            $table->unsignedTinyInteger('id_pemasok')->nullable();
            $table->foreign('id_pemasok')->references('id_pemasok')->on('pemasok')->onDelete('set null');
            $table->bigInteger('total_harga')->default(0);
            $table->enum('status_pembelian', ['diproses', 'lunas', 'belum lunas']);
            $table->date('tanggal_kedatangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelian');
    }
};
