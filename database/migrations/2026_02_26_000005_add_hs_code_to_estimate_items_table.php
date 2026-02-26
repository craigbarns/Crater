<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHsCodeToEstimateItemsTable extends Migration
{
    public function up()
    {
        Schema::table('estimate_items', function (Blueprint $table) {
            $table->string('hs_code')->nullable()->after('description');
            $table->string('unit')->nullable()->after('hs_code');
            $table->string('country_of_origin')->nullable()->after('unit');
        });
    }

    public function down()
    {
        Schema::table('estimate_items', function (Blueprint $table) {
            $table->dropColumn(['hs_code', 'unit', 'country_of_origin']);
        });
    }
}
