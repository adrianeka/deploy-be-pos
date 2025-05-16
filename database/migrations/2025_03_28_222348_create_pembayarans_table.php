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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id('id_pembayaran');
            $table->unsignedTinyInteger('id_tipe_transfer')->nullable(); // nullable untuk jenis Tunai    
            $table->foreign('id_tipe_transfer')->references('id_tipe_transfer')->on('tipe_transfer')->onDelete('cascade');
            $table->enum('jenis_pembayaran', ['tunai', 'transfer']);
            $table->bigInteger('total_bayar');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
