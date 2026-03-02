<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDepositInvoiceFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('invoice_type')->default('standard')->after('status');
            $table->unsignedInteger('estimate_id')->nullable()->after('invoice_type');
            $table->unsignedInteger('parent_invoice_id')->nullable()->after('estimate_id');
            $table->decimal('deposit_percentage', 5, 2)->nullable()->after('parent_invoice_id');

            $table->foreign('estimate_id')->references('id')->on('estimates')->onDelete('set null');
            $table->foreign('parent_invoice_id')->references('id')->on('invoices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['estimate_id']);
            $table->dropForeign(['parent_invoice_id']);
            $table->dropColumn(['invoice_type', 'estimate_id', 'parent_invoice_id', 'deposit_percentage']);
        });
    }
}
