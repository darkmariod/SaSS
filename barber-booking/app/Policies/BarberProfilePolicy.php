<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BarberProfile;
use Illuminate\Auth\Access\HandlesAuthorization;

class BarberProfilePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BarberProfile');
    }

    public function view(AuthUser $authUser, BarberProfile $barberProfile): bool
    {
        return $authUser->can('View:BarberProfile');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BarberProfile');
    }

    public function update(AuthUser $authUser, BarberProfile $barberProfile): bool
    {
        return $authUser->can('Update:BarberProfile');
    }

    public function delete(AuthUser $authUser, BarberProfile $barberProfile): bool
    {
        return $authUser->can('Delete:BarberProfile');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:BarberProfile');
    }

    public function restore(AuthUser $authUser, BarberProfile $barberProfile): bool
    {
        return $authUser->can('Restore:BarberProfile');
    }

    public function forceDelete(AuthUser $authUser, BarberProfile $barberProfile): bool
    {
        return $authUser->can('ForceDelete:BarberProfile');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BarberProfile');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BarberProfile');
    }

    public function replicate(AuthUser $authUser, BarberProfile $barberProfile): bool
    {
        return $authUser->can('Replicate:BarberProfile');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BarberProfile');
    }

}