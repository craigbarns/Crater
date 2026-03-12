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
            Bouncer::scope()->to($company->id);

            $superAdmin = Bouncer::role()->where('name', 'super admin')
                ->where('scope', $company->id)
                ->first();

            if ($superAdmin) {
                Bouncer::allow($superAdmin)->to('view-supplier-payment');
                Bouncer::allow($superAdmin)->to('create-supplier-payment');
                Bouncer::allow($superAdmin)->to('edit-supplier-payment');
                Bouncer::allow($superAdmin)->to('delete-supplier-payment');
            }
        }
    }

    public function down()
    {
        // No rollback needed
    }
}
