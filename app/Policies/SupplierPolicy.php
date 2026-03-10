<?php

namespace Crater\Policies;

use Crater\Models\Supplier;
use Crater\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Silber\Bouncer\BouncerFacade;

class SupplierPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if (BouncerFacade::can('view-supplier', Supplier::class)) {
            return true;
        }

        return false;
    }

    public function view(User $user, Supplier $supplier)
    {
        if (BouncerFacade::can('view-supplier', $supplier) && $user->hasCompany($supplier->company_id)) {
            return true;
        }

        return false;
    }

    public function create(User $user)
    {
        if (BouncerFacade::can('create-supplier', Supplier::class)) {
            return true;
        }

        return false;
    }

    public function update(User $user, Supplier $supplier)
    {
        if (BouncerFacade::can('edit-supplier', $supplier) && $user->hasCompany($supplier->company_id)) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Supplier $supplier)
    {
        if (BouncerFacade::can('delete-supplier', $supplier) && $user->hasCompany($supplier->company_id)) {
            return true;
        }

        return false;
    }

    public function restore(User $user, Supplier $supplier)
    {
        if (BouncerFacade::can('delete-supplier', $supplier) && $user->hasCompany($supplier->company_id)) {
            return true;
        }

        return false;
    }

    public function forceDelete(User $user, Supplier $supplier)
    {
        if (BouncerFacade::can('delete-supplier', $supplier) && $user->hasCompany($supplier->company_id)) {
            return true;
        }

        return false;
    }

    public function deleteMultiple(User $user)
    {
        if (BouncerFacade::can('delete-supplier', Supplier::class)) {
            return true;
        }

        return false;
    }
}
