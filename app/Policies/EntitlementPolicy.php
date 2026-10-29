<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Entitlement;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class EntitlementPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Entitlement');
    }

    public function view(AuthUser $authUser, Entitlement $entitlement): bool
    {
        return $authUser->can('View:Entitlement');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Entitlement');
    }

    public function update(AuthUser $authUser, Entitlement $entitlement): bool
    {
        return $authUser->can('Update:Entitlement');
    }

    public function delete(AuthUser $authUser, Entitlement $entitlement): bool
    {
        return $authUser->can('Delete:Entitlement');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Entitlement');
    }

    public function restore(AuthUser $authUser, Entitlement $entitlement): bool
    {
        return $authUser->can('Restore:Entitlement');
    }

    public function forceDelete(AuthUser $authUser, Entitlement $entitlement): bool
    {
        return $authUser->can('ForceDelete:Entitlement');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Entitlement');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Entitlement');
    }

    public function replicate(AuthUser $authUser, Entitlement $entitlement): bool
    {
        return $authUser->can('Replicate:Entitlement');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Entitlement');
    }
}
