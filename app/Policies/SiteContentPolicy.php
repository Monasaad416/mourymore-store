<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SiteContent;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SiteContentPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SiteContent');
    }

    public function view(AuthUser $authUser, SiteContent $siteContent): bool
    {
        return $authUser->can('View:SiteContent');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SiteContent');
    }

    public function update(AuthUser $authUser, SiteContent $siteContent): bool
    {
        return $authUser->can('Update:SiteContent');
    }

    public function delete(AuthUser $authUser, SiteContent $siteContent): bool
    {
        return $authUser->can('Delete:SiteContent');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SiteContent');
    }

    public function restore(AuthUser $authUser, SiteContent $siteContent): bool
    {
        return $authUser->can('Restore:SiteContent');
    }

    public function forceDelete(AuthUser $authUser, SiteContent $siteContent): bool
    {
        return $authUser->can('ForceDelete:SiteContent');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SiteContent');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SiteContent');
    }

    public function replicate(AuthUser $authUser, SiteContent $siteContent): bool
    {
        return $authUser->can('Replicate:SiteContent');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SiteContent');
    }
}
