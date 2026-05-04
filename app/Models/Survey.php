<?php

namespace App\Models;

use App\Enums\SurveyMode;
use Database\Factories\SurveyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['kategori_id', 'title', 'description', 'slug', 'schema', 'mode', 'is_active', 'starts_at', 'ends_at', 'access_level', 'allowed_roles'])]
class Survey extends Model
{
    /** @use HasFactory<SurveyFactory> */
    use HasFactory;

    protected $table = 'survey';

    protected static function booted(): void
    {
        static::creating(function (Survey $survey): void {
            if (empty($survey->slug)) {
                $survey->slug = Str::slug($survey->title);
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'schema' => 'array',
            'mode' => SurveyMode::class,
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'allowed_roles' => 'array',
        ];
    }

    /** @return BelongsTo<Kategori, $this> */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    /** @return HasMany<JawabanResponden, $this> */
    public function jawabanRespondens(): HasMany
    {
        return $this->hasMany(JawabanResponden::class);
    }

    /**
     * Check if the survey is currently available for filling.
     */
    public function isAvailable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the survey requires authentication.
     */
    public function requiresAuth(): bool
    {
        return in_array($this->access_level, ['auth', 'role']);
    }

    /**
     * Get the public URL for the survey.
     */
    public function getPublicUrl(): string
    {
        return route('survey.show', $this);
    }
}
