<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\NewsItem;
use Illuminate\Auth\Access\HandlesAuthorization;

class NewsItemPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:NewsItem');
    }

    public function view(AuthUser $authUser, NewsItem $newsItem): bool
    {
        return $authUser->can('View:NewsItem');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:NewsItem');
    }

    public function update(AuthUser $authUser, NewsItem $newsItem): bool
    {
        return $authUser->can('Update:NewsItem');
    }

    public function delete(AuthUser $authUser, NewsItem $newsItem): bool
    {
        return $authUser->can('Delete:NewsItem');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:NewsItem');
    }

    public function restore(AuthUser $authUser, NewsItem $newsItem): bool
    {
        return $authUser->can('Restore:NewsItem');
    }

    public function forceDelete(AuthUser $authUser, NewsItem $newsItem): bool
    {
        return $authUser->can('ForceDelete:NewsItem');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:NewsItem');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:NewsItem');
    }

    public function replicate(AuthUser $authUser, NewsItem $newsItem): bool
    {
        return $authUser->can('Replicate:NewsItem');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:NewsItem');
    }

}