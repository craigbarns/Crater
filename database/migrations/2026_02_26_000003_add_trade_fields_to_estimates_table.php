<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTradeFieldsToEstimatesTable extends Migration
{
    public function up()
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->string('contract_number')->nullable()->after('reference_number');
            $table->string('incoterm')->nullable()->after('contract_number');
            $table->string('payment_terms')->nullable()->after('incoterm');
            $table->string('delivery_lead_time')->nullable()->after('payment_terms');
            $table->string('shipping_port')->nullable()->after('delivery_lead_time');
            $table->string('destination_port')->nullable()->after('shipping_port');
            $table->string('transport_mode')->nullable()->after('destination_port');
            $table->decimal('gross_weight', 10, 2)->nullable()->after('transport_mode');
            $table->decimal('net_weight', 10, 2)->nullable()->after('gross_weight');
            $table->integer('package_count')->unsigned()->nullable()->after('net_weight');
            $table->decimal('cbm', 10, 3)->nullable()->after('package_count');
            $table->string('country_of_origin')->nullable()->default('China')->after('cbm');
        });
    }

    public function down()
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropColumn([
                'contract_number',
                'incoterm',
                'payment_terms',
                'delivery_lead_time',
                'shipping_port',
                'destination_port',
                'transport_mode',
                'gross_weight',
                'net_weight',
                'package_count',
                'cbm',
                'country_of_origin',
            ]);
        });
    }
}
