<?php

namespace Crater\Policies;

use Crater\Models\SupplierPayment;
use Crater\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Silber\Bouncer\BouncerFacade;

class SupplierPaymentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if (BouncerFacade::can('view-supplier-payment', SupplierPayment::class)) {
            return true;
        }

        return false;
    }

    public function view(User $user, SupplierPayment $supplierPayment)
    {
        if (BouncerFacade::can('view-supplier-payment', $supplierPayment) && $user->hasCompany($supplierPayment->company_id)) {
            return true;
        }

        return false;
    }

    public function create(User $user)
    {
        if (BouncerFacade::can('create-supplier-payment', SupplierPayment::class)) {
            return true;
        }

        return false;
    }

    public function update(User $user, SupplierPayment $supplierPayment)
    {
        if (BouncerFacade::can('edit-supplier-payment', $supplierPayment) && $user->hasCompany($supplierPayment->company_id)) {
            return true;
        }

        return false;
    }

    public function delete(User $user, SupplierPayment $supplierPayment)
    {
        if (BouncerFacade::can('delete-supplier-payment', $supplierPayment) && $user->hasCompany($supplierPayment->company_id)) {
            return true;
        }

        return false;
    }

    public function deleteMultiple(User $user)
    {
        if (BouncerFacade::can('delete-supplier-payment', SupplierPayment::class)) {
            return true;
        }

        return false;
    }
}
