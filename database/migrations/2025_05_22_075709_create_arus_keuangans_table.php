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
        Schema::create('arus_keuangan', function (Blueprint $table) {
            $table->id("id_arus_keuangan");
            $table->unsignedTinyInteger('id_pemilik');
            $table->foreign('id_pemilik')
                ->references('id_pemilik')
                ->on('pemilik')
                ->onDelete('cascade');
            $table->foreignId('id_sumber')
                ->references('id_pembayaran')
                ->on('pembayaran')
                ->onDelete('cascade');
            $table->string("keterangan");
            $table->enum("jenis_transaksi", ["debit", "kredit"]);
            $table->integer("nominal");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arus_keuangans');
    }
};
