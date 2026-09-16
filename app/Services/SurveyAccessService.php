<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\User;
use Carbon\Carbon;

class SurveyAccessService
{
    /**
     * Check detailed access status for the user on a survey.
     *
     * @return array{allowed: bool, title: string, message: string}
     */
    public function checkAccess(Survey $survey, ?User $user): array
    {
        if (! $survey->is_active) {
            return [
                'allowed' => false,
                'title' => 'Survei Tidak Aktif',
                'message' => 'Survei ini sudah dinonaktifkan oleh administrator.',
            ];
        }

        // Check groups constraint first
        if ($survey->groups()->exists()) {
            if (! $user) {
                return [
                    'allowed' => false,
                    'title' => 'Harus Login',
                    'message' => 'Silakan masuk terlebih dahulu untuk mengakses survei kelompok ini.',
                ];
            }

            // Find if user is in any of the groups associated with this survey
            $userGroups = $survey->groups()
                ->whereHas('users', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                })
                ->get();

            // If user is not in any group: check if they have role-based fallback access
            if ($userGroups->isEmpty()) {
                if ($survey->access_level === 'role' && ! empty($survey->allowed_roles)) {
                    if ($user->hasAnyRole($survey->allowed_roles)) {
                        // Role-based global check
                        if ($survey->starts_at && $survey->starts_at->isFuture()) {
                            return [
                                'allowed' => false,
                                'title' => 'Survei Belum Dimulai',
                                'message' => 'Survei ini belum dibuka. Akan dimulai pada '.$survey->starts_at->format('d M Y H:i').'.',
                            ];
                        }
                        if ($survey->ends_at && $survey->ends_at->isPast()) {
                            return [
                                'allowed' => false,
                                'title' => 'Survei Sudah Ditutup',
                                'message' => 'Periode pengisian survei ini sudah berakhir pada '.$survey->ends_at->format('d M Y H:i').'.',
                            ];
                        }

                        return [
                            'allowed' => true,
                            'title' => '',
                            'message' => '',
                        ];
                    }
                }

                return [
                    'allowed' => false,
                    'title' => 'Akses Ditolak',
                    'message' => 'Akun Anda ('.$user->email.') tidak terdaftar dalam kelompok peserta survei ini.',
                ];
            }

            // User is in group(s). Let's see if at least one group has a valid active time window.
            $hasFutureAccess = false;
            $hasPastAccess = false;
            $earliestStart = null;
            $latestEnd = null;

            foreach ($userGroups as $group) {
                // Determine effective starts_at/ends_at from pivot or fallback to group defaults
                $effectiveStart = $group->pivot->starts_at ?? $group->starts_at;
                $effectiveEnd = $group->pivot->ends_at ?? $group->ends_at;

                if (is_string($effectiveStart)) {
                    $effectiveStart = Carbon::parse($effectiveStart);
                }
                if (is_string($effectiveEnd)) {
                    $effectiveEnd = Carbon::parse($effectiveEnd);
                }

                $startOk = ! $effectiveStart || $effectiveStart->isPast();
                $endOk = ! $effectiveEnd || $effectiveEnd->isFuture();

                if ($startOk && $endOk) {
                    // Found an active group access window
                    return [
                        'allowed' => true,
                        'title' => '',
                        'message' => '',
                    ];
                }

                if ($effectiveStart && $effectiveStart->isFuture()) {
                    $hasFutureAccess = true;
                    if ($earliestStart === null || $effectiveStart->lt($earliestStart)) {
                        $earliestStart = $effectiveStart;
                    }
                }

                if ($effectiveEnd && $effectiveEnd->isPast()) {
                    $hasPastAccess = true;
                    if ($latestEnd === null || $effectiveEnd->gt($latestEnd)) {
                        $latestEnd = $effectiveEnd;
                    }
                }
            }

            // If we are here, none of the user's groups are active.
            if ($hasFutureAccess) {
                return [
                    'allowed' => false,
                    'title' => 'Akses Belum Dibuka',
                    'message' => 'Periode pengisian kuesioner untuk kelompok Anda belum dimulai. Survei akan dibuka pada '.$earliestStart->format('d M Y H:i').'.',
                ];
            }

            if ($hasPastAccess) {
                return [
                    'allowed' => false,
                    'title' => 'Akses Sudah Ditutup',
                    'message' => 'Periode pengisian kuesioner untuk kelompok Anda sudah berakhir pada '.$latestEnd->format('d M Y H:i').'.',
                ];
            }

            return [
                'allowed' => false,
                'title' => 'Akses Ditolak',
                'message' => 'Periode akses kelompok Anda untuk survei ini saat ini tidak tersedia.',
            ];
        }

        // Global (non-group) availability checks
        if ($survey->starts_at && $survey->starts_at->isFuture()) {
            return [
                'allowed' => false,
                'title' => 'Survei Belum Dimulai',
                'message' => 'Survei ini belum dibuka. Akan dimulai pada '.$survey->starts_at->format('d M Y H:i').'.',
            ];
        }

        if ($survey->ends_at && $survey->ends_at->isPast()) {
            return [
                'allowed' => false,
                'title' => 'Survei Sudah Ditutup',
                'message' => 'Periode pengisian survei ini sudah berakhir pada '.$survey->ends_at->format('d M Y H:i').'.',
            ];
        }

        // Check role requirement
        if ($survey->access_level === 'role') {
            $allowedRoles = $survey->allowed_roles ?? [];
            if (! $user || ! $user->hasAnyRole($allowedRoles)) {
                return [
                    'allowed' => false,
                    'title' => 'Akses Ditolak',
                    'message' => 'Anda tidak memiliki peran (role) yang diizinkan untuk mengakses survei ini.',
                ];
            }
        }

        return [
            'allowed' => true,
            'title' => '',
            'message' => '',
        ];
    }
}
