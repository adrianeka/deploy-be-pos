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
        Schema::create('bayar_zakat', function (Blueprint $table) {
            $table->string('id_bayar_zakat')
                ->primary();
            $table->unsignedTinyInteger('id_pemilik');
            $table->foreign('id_pemilik')
                ->references('id_pemilik')
                ->on('pemilik')
                ->onDelete('cascade');
            $table->unsignedTinyInteger('id_penerima_zakat')->nullable();
            $table->foreign('id_penerima_zakat')
                ->references('id_penerima_zakat')
                ->on('penerima_zakat')
                ->onDelete('set null');
            $table->foreignId('id_pembayaran')
                ->references('id_pembayaran')
                ->on('pembayaran')
                ->onDelete('cascade');
            $table->bigInteger('modal_terjual');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bayar_zakat');
    }
};
