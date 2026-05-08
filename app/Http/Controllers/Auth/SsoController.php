<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SsoController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('sipetra')->redirect();
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('filament.admin.auth.login')->with('error', 'Login SSO Dibatalkan');
        }

        try {
            $ssoUser = Socialite::driver('sipetra')->user();
        } catch (\Exception $e) {
            return redirect()->route('filament.admin.auth.login')->with('error', 'Gagal mengambil data user');
        }

        $rawData = $ssoUser->getRaw();

        // Cari user berdasarkan sipetra_id atau email
        $user = User::where('sipetra_id', $ssoUser->getId())->first()
            ?? User::where('email', $ssoUser->getEmail())->first();

        $data = [
            'sipetra_id' => $ssoUser->getId(),
            'name' => $ssoUser->getName(),
            'email' => $ssoUser->getEmail(),
            'sipetra_token' => $ssoUser->token,
            'nip' => $rawData['nip'] ?? null,
            'nip_baru' => $rawData['nip_baru'] ?? null,
            'jabatan' => $rawData['jabatan'] ?? null,
            'golongan' => $rawData['golongan'] ?? null,
            'unit_kerja' => $rawData['unit_kerja'] ?? null,
            'kd_satker' => $rawData['kd_satker'] ?? null,
            'nomor_hp' => $rawData['nomor_hp'] ?? null,
            'jenis_kelamin' => $rawData['gender'] ?? null,
            'identity_type' => $rawData['identity_type'] ?? null,
            'avatar_url' => $rawData['avatar_url'] ?? $ssoUser->getAvatar(),
        ];

        if ($user) {
            $user->update($data);
        } else {
            $data['password'] = null;
            $user = User::create($data);

            // Assign role default (jika pakai Filament Shield / Spatie Permission)
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('pegawai');
            }
        }

        Auth::login($user);
        return redirect()->intended('/admin');
    }
}

