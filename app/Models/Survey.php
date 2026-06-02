<?php

namespace App\Models;

use App\Enums\SurveyMode;
use Database\Factories\SurveyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['kategori_id', 'title', 'description', 'slug', 'schema', 'mode', 'is_quiz', 'is_active', 'settings', 'starts_at', 'ends_at', 'access_level', 'allowed_roles'])]
class Survey extends Model
{
    /** @use HasFactory<SurveyFactory> */
    use HasFactory;

    protected $table = 'survey';

    protected static function booted(): void
    {
        static::saving(function (Survey $survey): void {
            if (empty($survey->slug)) {
                $slug = Str::slug($survey->title);
                $originalSlug = $slug;
                $count = 1;

                while (static::where('slug', $slug)->where('id', '!=', $survey->id)->exists()) {
                    $slug = "{$originalSlug}-{$count}";
                    $count++;
                }

                $survey->slug = $slug;
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
            'is_quiz' => 'boolean',
            'is_active' => 'boolean',
            'settings' => 'array',
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

    /** @return BelongsToMany<Group, $this> */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class)->withPivot(['starts_at', 'ends_at'])->withTimestamps();
    }

    /**
     * Check if the survey is currently available for filling globally.
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
     * Scope to filter surveys accessible by a given user.
     *
     * Surveys without groups: shown normally (public/auth/role logic applies).
     * Surveys with groups: only shown if the user is a member of an active group.
     *
     * @param  Builder<Survey>  $query
     */
    public function scopeAccessibleBy(Builder $query, ?User $user): void
    {
        $query->where(function (Builder $q) use ($user) {
            // Surveys without any groups — shown normally
            $q->whereDoesntHave('groups');

            // OR surveys where the user belongs to an active group
            if ($user) {
                $q->orWhereHas('groups', function (Builder $groupQuery) use ($user) {
                    $groupQuery->whereHas('users', function (Builder $userQuery) use ($user) {
                        $userQuery->where('users.id', $user->id);
                    })
                        ->where(function ($tq) {
                            $tq->where(function ($sq) {
                                $sq->whereRaw('coalesce(group_survey.starts_at, groups.starts_at) is null')
                                    ->orWhereRaw('coalesce(group_survey.starts_at, groups.starts_at) <= ?', [now()]);
                            })
                                ->where(function ($sq) {
                                    $sq->whereRaw('coalesce(group_survey.ends_at, groups.ends_at) is null')
                                        ->orWhereRaw('coalesce(group_survey.ends_at, groups.ends_at) >= ?', [now()]);
                                });
                        });
                });
            }
        });
    }

    /**
     * Check if the user has active group access for this survey.
     */
    public function hasActiveGroupAccess(?User $user): bool
    {
        if (! $user || ! $this->is_active) {
            return false;
        }

        return $this->groups()
            ->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->where(function ($q) {
                $q->where(function ($sq) {
                    $sq->whereRaw('coalesce(group_survey.starts_at, groups.starts_at) is null')
                        ->orWhereRaw('coalesce(group_survey.starts_at, groups.starts_at) <= ?', [now()]);
                })
                    ->where(function ($sq) {
                        $sq->whereRaw('coalesce(group_survey.ends_at, groups.ends_at) is null')
                            ->orWhereRaw('coalesce(group_survey.ends_at, groups.ends_at) >= ?', [now()]);
                    });
            })
            ->exists();
    }

    /**
     * Check if the survey is available for a specific user (global or via group).
     */
    public function isAvailableForUser(?User $user = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->groups()->exists()) {
            if ($this->hasActiveGroupAccess($user)) {
                return true;
            }

            // If they don't have group access but they have role access, allow them.
            if ($user && $this->access_level === 'role' && ! empty($this->allowed_roles)) {
                if ($user->hasAnyRole($this->allowed_roles)) {
                    // Check if it's globally available for the role
                    return $this->isAvailable();
                }
            }

            return false;
        }

        return $this->isAvailable();
    }

    /**
     * Check if the survey requires authentication.
     */
    public function requiresAuth(): bool
    {
        return in_array($this->access_level, ['auth', 'role']) || $this->groups()->exists();
    }

    public function getPublicUrl(): string
    {
        return route('survey.show', $this);
    }

    /**
     * Parse the SurveyJS schema to extract field titles and choice mappings in order.
     */
    public function getParsedSchema(): array
    {
        $schema = is_string($this->schema) ? json_decode($this->schema, true) : $this->schema;

        $fields = [];
        $choicesMap = [];

        if (! isset($schema['pages']) || ! is_array($schema['pages'])) {
            return ['fields' => [], 'choices' => []];
        }

        $extractElements = function ($elements) use (&$extractElements, &$fields, &$choicesMap) {
            if (! is_array($elements)) {
                return;
            }

            foreach ($elements as $element) {
                if (isset($element['type']) && in_array($element['type'], ['panel', 'paneldynamic'])) {
                    if (isset($element['elements'])) {
                        $extractElements($element['elements']);
                    }
                } elseif (isset($element['name'])) {
                    $name = $element['name'];
                    $title = $element['title'] ?? $name;

                    // Handle multi-language object for title if needed (fallback to default string)
                    if (is_array($title)) {
                        $title = $title['default'] ?? $title['id'] ?? (is_string(reset($title)) ? reset($title) : $name);
                    }

                    // Remove HTML tags and decode HTML entities from title
                    $title = html_entity_decode(strip_tags($title));

                    $fields[$name] = $title;

                    if (isset($element['choices']) && is_array($element['choices'])) {
                        foreach ($element['choices'] as $choice) {
                            if (is_array($choice) && isset($choice['value'])) {
                                $text = $choice['text'] ?? $choice['value'];
                                if (is_array($text)) {
                                    $text = $text['default'] ?? $text['id'] ?? (is_string(reset($text)) ? reset($text) : $choice['value']);
                                }
                                $choicesMap[$name][$choice['value']] = $text;
                            } elseif (is_string($choice) || is_numeric($choice)) {
                                $choicesMap[$name][$choice] = $choice;
                            }
                        }
                    }
                }
            }
        };

        foreach ($schema['pages'] as $page) {
            if (isset($page['elements'])) {
                $extractElements($page['elements']);
            }
        }

        return [
            'fields' => $fields,
            'choices' => $choicesMap,
        ];
    }
}
