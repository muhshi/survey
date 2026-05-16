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
        Schema::create('master_wilayah', function (Blueprint $table) {
            $table->id();
            $table->string('idsubsls')->unique();
            $table->string('nmsls')->nullable();
            $table->string('nama_ketua')->nullable();
            $table->string('nmkec');
            $table->string('kdkec');
            $table->string('nmdesa');
            $table->string('kddesa');
            $table->string('kdsls')->nullable();
            $table->string('kdsubsls')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_wilayah');
    }
};
