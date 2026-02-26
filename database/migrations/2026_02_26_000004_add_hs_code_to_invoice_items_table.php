<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHsCodeToInvoiceItemsTable extends Migration
{
    public function up()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('hs_code')->nullable()->after('description'); // Harmonized System / 海关编码
            $table->string('unit')->nullable()->after('hs_code'); // unit of measure
            $table->string('country_of_origin')->nullable()->after('unit');
        });
    }

    public function down()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['hs_code', 'unit', 'country_of_origin']);
        });
    }
}
