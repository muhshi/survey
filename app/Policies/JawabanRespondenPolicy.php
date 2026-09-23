<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\JawabanResponden;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class JawabanRespondenPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:JawabanResponden');
    }

    public function view(AuthUser $authUser, JawabanResponden $jawabanResponden): bool
    {
        return $authUser->can('View:JawabanResponden');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:JawabanResponden');
    }

    public function update(AuthUser $authUser, JawabanResponden $jawabanResponden): bool
    {
        return $authUser->can('Update:JawabanResponden');
    }

    public function delete(AuthUser $authUser, JawabanResponden $jawabanResponden): bool
    {
        return $authUser->can('Delete:JawabanResponden');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:JawabanResponden');
    }

    public function restore(AuthUser $authUser, JawabanResponden $jawabanResponden): bool
    {
        return $authUser->can('Restore:JawabanResponden');
    }

    public function forceDelete(AuthUser $authUser, JawabanResponden $jawabanResponden): bool
    {
        return $authUser->can('ForceDelete:JawabanResponden');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:JawabanResponden');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:JawabanResponden');
    }

    public function replicate(AuthUser $authUser, JawabanResponden $jawabanResponden): bool
    {
        return $authUser->can('Replicate:JawabanResponden');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:JawabanResponden');
    }
}
