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
    'avatar_url',
    'identity_type',
    'is_active',
    'metadata',
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
            'metadata' => 'array',
        ];
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        // Avoid recursive loop or resolving system attributes
        if ($key === 'metadata' || array_key_exists($key, $this->attributes) || $this->hasGetMutator($key) || $this->isClassCastable($key) || method_exists($this, $key)) {
            return parent::getAttribute($key);
        }

        $metadataFields = [
            'nomor_urut', 'nip', 'nip_baru', 'jabatan', 'golongan', 'unit_kerja',
            'kecamatan', 'desa', 'idsubsls', 'kd_satker', 'nomor_hp', 'jenis_kelamin',
            'period', 'contract_start', 'contract_end',
        ];

        if (in_array($key, $metadataFields)) {
            $metadata = $this->metadata ?? [];

            return $metadata[$key] ?? null;
        }

        return parent::getAttribute($key);
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        $metadataFields = [
            'nomor_urut', 'nip', 'nip_baru', 'jabatan', 'golongan', 'unit_kerja',
            'kecamatan', 'desa', 'idsubsls', 'kd_satker', 'nomor_hp', 'jenis_kelamin',
            'period', 'contract_start', 'contract_end',
        ];

        if (in_array($key, $metadataFields)) {
            $metadata = $this->metadata ?? [];
            $metadata[$key] = $value;
            $this->metadata = $metadata;

            return $this;
        }

        return parent::setAttribute($key, $value);
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

    public function jawaban_responden()
    {
        return $this->hasMany(JawabanResponden::class);
    }
}
