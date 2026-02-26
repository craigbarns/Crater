<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTradeFieldsToCompaniesTable extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('business_registration_number')->nullable()->after('slug'); // 营业执照
            $table->string('tax_id')->nullable()->after('business_registration_number'); // 税号
            $table->string('bank_name')->nullable()->after('tax_id');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_swift_bic')->nullable()->after('bank_account_number');
            $table->string('bank_iban')->nullable()->after('bank_swift_bic');
        });
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'business_registration_number',
                'tax_id',
                'bank_name',
                'bank_account_number',
                'bank_swift_bic',
                'bank_iban',
            ]);
        });
    }
}
