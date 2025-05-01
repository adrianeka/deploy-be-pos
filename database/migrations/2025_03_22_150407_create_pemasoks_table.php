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
        Schema::create('pemasok', function (Blueprint $table) {
            $table->tinyIncrements('id_pemasok')->primary();
            $table->unsignedTinyInteger('id_pemilik');
            $table->foreign('id_pemilik')->references('id_pemilik')->on('pemilik')->onDelete('cascade');
            $table->string('nama_perusahaan');
            $table->string('no_telp', 15);
            $table->text('alamat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemasok');
    }
};
