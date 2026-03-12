<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierPaymentsTable extends Migration
{
    public function up()
    {
        // Drop if exists (handles partial creation from previous failed migration)
        Schema::dropIfExists('supplier_payments');

        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number');
            $table->date('payment_date');
            $table->unsignedBigInteger('amount');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('supplier_id');
            $table->integer('expense_id')->unsigned()->nullable();
            $table->integer('payment_method_id')->unsigned()->nullable();
            $table->unsignedInteger('currency_id')->nullable();
            $table->integer('company_id')->unsigned();
            $table->integer('creator_id')->unsigned()->nullable();
            $table->decimal('exchange_rate', 19, 6)->nullable();
            $table->unsignedBigInteger('base_amount')->nullable();
            $table->timestamps();

            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            $table->foreign('expense_id')->references('id')->on('expenses')->onDelete('set null');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('set null');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('supplier_payments');
    }
}
