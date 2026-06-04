<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BarberPayment;
use Illuminate\Auth\Access\HandlesAuthorization;

class BarberPaymentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BarberPayment');
    }

    public function view(AuthUser $authUser, BarberPayment $barberPayment): bool
    {
        return $authUser->can('View:BarberPayment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BarberPayment');
    }

    public function update(AuthUser $authUser, BarberPayment $barberPayment): bool
    {
        return $authUser->can('Update:BarberPayment');
    }

    public function delete(AuthUser $authUser, BarberPayment $barberPayment): bool
    {
        return $authUser->can('Delete:BarberPayment');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:BarberPayment');
    }

    public function restore(AuthUser $authUser, BarberPayment $barberPayment): bool
    {
        return $authUser->can('Restore:BarberPayment');
    }

    public function forceDelete(AuthUser $authUser, BarberPayment $barberPayment): bool
    {
        return $authUser->can('ForceDelete:BarberPayment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BarberPayment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BarberPayment');
    }

    public function replicate(AuthUser $authUser, BarberPayment $barberPayment): bool
    {
        return $authUser->can('Replicate:BarberPayment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BarberPayment');
    }

}