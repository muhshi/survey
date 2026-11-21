<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Spatie\Permission\Models\Role;

test('login page can be rendered with sipetra sso button', function () {
    $response = $this->get('/admin/login');

    $response->assertStatus(200);
    $response->assertSee('Masuk dengan SIPETRA SSO');
    $response->assertSee('Opsi Darurat / Pengembang');
    $response->assertSee('logo_bps.png');
    $response->assertDontSee('Daftar Akun Baru');
});

test('login page supports fallback parameter', function () {
    $response = $this->get('/admin/login?fallback=1');

    $response->assertStatus(200);
    $response->assertSee('Opsi Darurat / Pengembang');
});

test('sso callback with error redirects to login with error flash', function () {
    $response = $this->get('/auth/sipetra/callback?error=access_denied');

    $response->assertRedirect(route('filament.admin.auth.login'));
    $response->assertSessionHas('error');
});

test('sso callback logs in user and assigns pegawai role for new user', function () {
    Role::firstOrCreate(['name' => 'pegawai', 'guard_name' => 'web']);

    $mockSocialiteUser = Mockery::mock(SocialiteUser::class);
    $mockSocialiteUser->shouldReceive('getId')->andReturn('sso-test-123');
    $mockSocialiteUser->shouldReceive('getName')->andReturn('Test SSO User');
    $mockSocialiteUser->shouldReceive('getEmail')->andReturn('test_sso@bps.go.id');
    $mockSocialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');
    $mockSocialiteUser->shouldReceive('getRaw')->andReturn([
        'nip' => '199001012020011001',
        'nip_baru' => '199001012020011001',
        'jabatan' => 'Statistisi Ahli Pertama',
        'golongan' => 'III/a',
        'unit_kerja' => 'BPS Kab. Demak',
        'kd_satker' => '3321',
        'nomor_hp' => '08123456789',
        'gender' => 'L',
        'identity_type' => 'organik',
        'avatar_url' => 'https://example.com/avatar.jpg',
    ]);
    $mockSocialiteUser->token = 'dummy-token';

    $mockDriver = Mockery::mock(AbstractProvider::class);
    $mockDriver->shouldReceive('user')->andReturn($mockSocialiteUser);

    Socialite::shouldReceive('driver')->with('sipetra')->andReturn($mockDriver);

    $response = $this->get('/auth/sipetra/callback');

    $response->assertRedirect('/admin');
    $this->assertAuthenticated();

    $user = User::where('sipetra_id', 'sso-test-123')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Test SSO User');
    expect($user->hasRole('pegawai'))->toBeTrue();

    // Clean up created test user and role to avoid database pollution
    $user->delete();
});
