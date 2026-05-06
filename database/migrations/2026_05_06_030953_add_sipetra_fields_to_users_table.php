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
            $table->string('sipetra_id')->nullable()->unique();
            $table->text('sipetra_token')->nullable();
            $table->text('sipetra_refresh_token')->nullable();
            $table->string('nip', 20)->nullable();
            $table->string('nip_baru', 20)->nullable();
            $table->string('jabatan')->nullable();
            $table->string('golongan', 10)->nullable();
            $table->string('unit_kerja')->nullable();
            $table->string('kd_satker', 10)->nullable();
            $table->string('nomor_hp', 20)->nullable();
            $table->string('jenis_kelamin', 1)->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('identity_type', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('period', 50)->nullable();
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'sipetra_id',
                'sipetra_token',
                'sipetra_refresh_token',
                'nip',
                'nip_baru',
                'jabatan',
                'golongan',
                'unit_kerja',
                'kd_satker',
                'nomor_hp',
                'jenis_kelamin',
                'avatar_url',
                'identity_type',
                'is_active',
                'period',
                'contract_start',
                'contract_end',
            ]);
        });
    }
};
