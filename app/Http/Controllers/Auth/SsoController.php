<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Filament\Notifications\Notification;
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
            $errorDescription = $request->get('error_description') ?: $request->get('error') ?: 'Proses login dibatalkan.';

            return redirect()->route('filament.admin.auth.login')->with('error', 'Login SSO dibatalkan: '.$errorDescription);
        }

        try {
            $ssoUser = Socialite::driver('sipetra')->user();
        } catch (\Throwable $e) {
            return redirect()->route('filament.admin.auth.login')->with('error', 'Gagal memverifikasi akun ke server SIPETRA: '.$e->getMessage());
        }

        if (! $ssoUser || ! $ssoUser->getId()) {
            return redirect()->route('filament.admin.auth.login')->with('error', 'Data pengguna dari SIPETRA tidak valid atau tidak lengkap.');
        }

        $rawData = $ssoUser->getRaw();

        // Cari user berdasarkan sipetra_id atau email
        $user = User::where('sipetra_id', $ssoUser->getId())->first()
            ?? ($ssoUser->getEmail() ? User::where('email', $ssoUser->getEmail())->first() : null);

        $data = [
            'sipetra_id' => $ssoUser->getId(),
            'name' => $ssoUser->getName() ?: 'Pengguna SIPETRA',
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
            'is_active' => true,
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

        // Pastikan user memiliki minimal satu role jika belum ada
        if (method_exists($user, 'roles') && $user->roles()->count() === 0 && method_exists($user, 'assignRole')) {
            $user->assignRole('pegawai');
        }

        if (! ($user->is_active ?? true)) {
            return redirect()->route('filament.admin.auth.login')->with('error', 'Akun Anda sedang dinonaktifkan. Silakan hubungi administrator.');
        }

        Auth::login($user);

        Notification::make()
            ->title('Profil Berhasil Disinkronkan dari SIPETRA')
            ->success()
            ->send();

        return redirect()->intended('/admin');
    }
}
