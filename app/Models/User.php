<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name', 
    'email', 
    'password',
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
    'nomor_urut',
    'kecamatan',
    'desa',
    'idsubsls',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    use HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'contract_start' => 'date',
            'contract_end' => 'date',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePegawai($query)
    {
        return $query->active()->where('identity_type', 'pegawai');
    }

    public function scopeMitra($query, ?string $period = null)
    {
        $q = $query->active()->where('identity_type', 'mitra');
        return $period ? $q->where('period', $period) : $q;
    }
}
