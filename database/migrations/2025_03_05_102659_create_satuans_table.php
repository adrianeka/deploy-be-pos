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
        Schema::create('satuan', function (Blueprint $table) {
            $table->tinyIncrements('id_satuan');
            $table->string('nama_satuan');
            $table->unsignedTinyInteger('id_pemilik')->nullable();
            $table->foreign('id_pemilik')->references('id_pemilik')->on('pemilik')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('satuan');
    }
};
