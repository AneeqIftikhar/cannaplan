<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInitialBalanceSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('initial_balance_settings')) {
            Schema::create('initial_balance_settings', function (Blueprint $table) {
                $table->increments('id');

                $table->integer('forecast_id')->unsigned();
                $table->foreign('forecast_id')->references('id')->on('forecast')->onDelete('cascade');

                //assets
                $table->float('cash')->nullable();//How much cash do you have in the bank?
                $table->float('accounts_receivable')->nullable();//How much do your customers owe you for past sales on credit?
                $table->float('days_to_get_paid')->default(1);//How long will you take to collect on this starting balance
                $table->float('inventory')->nullable();
                $table->float('long_term_assets')->nullable();
                $table->float('accumulated_depreciation')->nullable();
                $table->float('depreciation_period')->default(1);
                $table->float('other_current_assets')->nullable();
                $table->float('amortization_period')->default(1);

                //liabilities
                $table->float('accounts_payable')->nullable();
                $table->float('days_to_pay')->default(15);
                $table->float('corporate_taxes_payable')->nullable();
                $table->float('sales_taxes_payable')->nullable();
                $table->float('prepaid_revenue')->nullable();
                $table->float('short_term_debt')->nullable();
                $table->float('long_term_debt')->nullable();

                //equity
                $table->float('paid_in_capital')->nullable();
                $table->float('retained_earnings')->default(0);

                $table->integer('created_by')->nullable();

                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('initial_balance_settings');
    }
}
