<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('pemiliks', function (Blueprint $table) {
            $table->id('id_pemilik');
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->string('nama_pemilik');
            $table->string('nama_perusahaan');
            $table->string('alamat_toko');
            $table->string('jenis_usaha');
            $table->string('no_telp');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemiliks');
    }
};
