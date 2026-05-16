<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add json column first
        Schema::table('users', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('remember_token');
        });

        // 2. Transfer existing data to metadata column
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $metadata = [
                'nomor_urut' => $user->nomor_urut ?? null,
                'nip' => $user->nip ?? null,
                'nip_baru' => $user->nip_baru ?? null,
                'jabatan' => $user->jabatan ?? null,
                'golongan' => $user->golongan ?? null,
                'unit_kerja' => $user->unit_kerja ?? null,
                'kecamatan' => $user->kecamatan ?? null,
                'desa' => $user->desa ?? null,
                'idsubsls' => $user->idsubsls ?? null,
                'kd_satker' => $user->kd_satker ?? null,
                'nomor_hp' => $user->nomor_hp ?? null,
                'jenis_kelamin' => $user->jenis_kelamin ?? null,
                'period' => $user->period ?? null,
                'contract_start' => $user->contract_start ?? null,
                'contract_end' => $user->contract_end ?? null,
            ];

            // Remove null values to keep JSON compact
            $metadata = array_filter($metadata, fn ($val) => $val !== null);

            DB::table('users')
                ->where('id', $user->id)
                ->update(['metadata' => json_encode($metadata)]);
        }

        // 3. Drop physical columns
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nomor_urut', 'nip', 'nip_baru', 'jabatan', 'golongan', 'unit_kerja',
                'kecamatan', 'desa', 'idsubsls', 'kd_satker', 'nomor_hp', 'jenis_kelamin',
                'period', 'contract_start', 'contract_end',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Re-add dropped columns
        Schema::table('users', function (Blueprint $table) {
            $table->string('nomor_urut', 10)->nullable();
            $table->string('nip', 20)->nullable();
            $table->string('nip_baru', 20)->nullable();
            $table->string('jabatan')->nullable();
            $table->string('golongan', 10)->nullable();
            $table->string('unit_kerja')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('desa')->nullable();
            $table->string('idsubsls', 20)->nullable();
            $table->string('kd_satker', 10)->nullable();
            $table->string('nomor_hp', 20)->nullable();
            $table->string('jenis_kelamin', 10)->nullable();
            $table->string('period', 10)->nullable();
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();
        });

        // 2. Transfer data back from JSON metadata to physical columns
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            if ($user->metadata) {
                $metadata = json_decode($user->metadata, true);
                if (is_array($metadata)) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'nomor_urut' => $metadata['nomor_urut'] ?? null,
                            'nip' => $metadata['nip'] ?? null,
                            'nip_baru' => $metadata['nip_baru'] ?? null,
                            'jabatan' => $metadata['jabatan'] ?? null,
                            'golongan' => $metadata['golongan'] ?? null,
                            'unit_kerja' => $metadata['unit_kerja'] ?? null,
                            'kecamatan' => $metadata['kecamatan'] ?? null,
                            'desa' => $metadata['desa'] ?? null,
                            'idsubsls' => $metadata['idsubsls'] ?? null,
                            'kd_satker' => $metadata['kd_satker'] ?? null,
                            'nomor_hp' => $metadata['nomor_hp'] ?? null,
                            'jenis_kelamin' => $metadata['jenis_kelamin'] ?? null,
                            'period' => $metadata['period'] ?? null,
                            'contract_start' => $metadata['contract_start'] ?? null,
                            'contract_end' => $metadata['contract_end'] ?? null,
                        ]);
                }
            }
        }

        // 3. Drop json column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });
    }
};
