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
        Schema::table('users', function (Blueprint $table) {
            $table->string('nomor_urut')->nullable()->after('id');
            $table->string('kecamatan')->nullable()->after('unit_kerja');
            $table->string('desa')->nullable()->after('kecamatan');
            $table->string('idsubsls')->nullable()->after('desa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nomor_urut', 'kecamatan', 'desa', 'idsubsls']);
        });
    }
};
