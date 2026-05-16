<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ImportPesertaService
{
    public function import(array $data): int
    {
        $role = Role::firstOrCreate(['name' => 'calon_petugas']);
        $count = 0;

        // Ambil nomor urut terakhir jika ada
        $lastUser = User::whereNotNull('metadata->nomor_urut')
            ->orderByRaw('CAST(json_unquote(json_extract(metadata, "$.nomor_urut")) AS UNSIGNED) DESC')
            ->first();

        $currentNumber = $lastUser ? (int) $lastUser->nomor_urut : 0;

        foreach ($data as $item) {
            $detail = $item['mitra_detail'] ?? [];
            $email = $detail['email'] ?? null;
            $name = $detail['nama_lengkap'] ?? null;
            $dob = $detail['tgl_lahir'] ?? null;

            if (! $email) {
                continue;
            }

            $user = User::where('email', $email)->first();

            if (! $user) {
                $currentNumber++;
                $nomorUrut = str_pad($currentNumber, 4, '0', STR_PAD_LEFT);
            } else {
                $nomorUrut = $user->nomor_urut;
            }

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'nomor_urut' => $nomorUrut,
                    'name' => $name ?? explode('@', $email)[0],
                    'password' => $dob ? Hash::make($dob) : Hash::make('password123'),
                    'is_active' => true,
                    'identity_type' => 'mitra',
                ]
            );

            if (! $user->hasRole('calon_petugas')) {
                $user->assignRole($role);
            }

            $count++;
        }

        return $count;
    }

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
}
