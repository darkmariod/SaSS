<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BarberShop;
use Illuminate\Auth\Access\HandlesAuthorization;

class BarberShopPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BarberShop');
    }

    public function view(AuthUser $authUser, BarberShop $barberShop): bool
    {
        return $authUser->can('View:BarberShop');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BarberShop');
    }

    public function update(AuthUser $authUser, BarberShop $barberShop): bool
    {
        return $authUser->can('Update:BarberShop');
    }

    public function delete(AuthUser $authUser, BarberShop $barberShop): bool
    {
        return $authUser->can('Delete:BarberShop');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:BarberShop');
    }

    public function restore(AuthUser $authUser, BarberShop $barberShop): bool
    {
        return $authUser->can('Restore:BarberShop');
    }

    public function forceDelete(AuthUser $authUser, BarberShop $barberShop): bool
    {
        return $authUser->can('ForceDelete:BarberShop');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BarberShop');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BarberShop');
    }

    public function replicate(AuthUser $authUser, BarberShop $barberShop): bool
    {
        return $authUser->can('Replicate:BarberShop');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BarberShop');
    }

}