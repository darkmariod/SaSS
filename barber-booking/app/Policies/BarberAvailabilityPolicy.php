<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BarberAvailability;
use Illuminate\Auth\Access\HandlesAuthorization;

class BarberAvailabilityPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BarberAvailability');
    }

    public function view(AuthUser $authUser, BarberAvailability $barberAvailability): bool
    {
        return $authUser->can('View:BarberAvailability');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BarberAvailability');
    }

    public function update(AuthUser $authUser, BarberAvailability $barberAvailability): bool
    {
        return $authUser->can('Update:BarberAvailability');
    }

    public function delete(AuthUser $authUser, BarberAvailability $barberAvailability): bool
    {
        return $authUser->can('Delete:BarberAvailability');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:BarberAvailability');
    }

    public function restore(AuthUser $authUser, BarberAvailability $barberAvailability): bool
    {
        return $authUser->can('Restore:BarberAvailability');
    }

    public function forceDelete(AuthUser $authUser, BarberAvailability $barberAvailability): bool
    {
        return $authUser->can('ForceDelete:BarberAvailability');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BarberAvailability');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BarberAvailability');
    }

    public function replicate(AuthUser $authUser, BarberAvailability $barberAvailability): bool
    {
        return $authUser->can('Replicate:BarberAvailability');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BarberAvailability');
    }

}