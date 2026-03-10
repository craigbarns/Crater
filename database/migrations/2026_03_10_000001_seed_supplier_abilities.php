<?php

use Crater\Models\Company;
use Illuminate\Database\Migrations\Migration;
use Silber\Bouncer\BouncerFacade;

class SeedSupplierAbilities extends Migration
{
    public function up()
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            BouncerFacade::scope()->to($company->id);

            $superAdmin = BouncerFacade::role()->where('name', 'super admin')
                ->where('scope', $company->id)
                ->first();

            if ($superAdmin) {
                $supplierAbilities = collect(config('abilities.abilities'))
                    ->filter(fn($a) => str_contains($a['ability'], 'supplier'));

                foreach ($supplierAbilities as $ability) {
                    BouncerFacade::allow($superAdmin)->to($ability['ability'], $ability['model']);
                }
            }
        }
    }

    public function down()
    {
        // No rollback needed
    }
}
