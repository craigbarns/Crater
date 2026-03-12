<?php

use Illuminate\Database\Migrations\Migration;
use Crater\Models\Company;
use Silber\Bouncer\BouncerFacade as Bouncer;

class SeedSupplierPaymentAbilities extends Migration
{
    public function up()
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            $role = $company->roles()->where('name', 'super admin')->first();

            if ($role) {
                Bouncer::scope()->to($company->id);

                Bouncer::allow($role)->to('view-supplier-payment');
                Bouncer::allow($role)->to('create-supplier-payment');
                Bouncer::allow($role)->to('edit-supplier-payment');
                Bouncer::allow($role)->to('delete-supplier-payment');
            }
        }
    }

    public function down()
    {
        // No rollback needed
    }
}
