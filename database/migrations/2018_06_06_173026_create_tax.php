<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTax extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tax')) {
            Schema::create('tax', function (Blueprint $table) {
                $table->increments('id');

                $table->integer('forecast_id')->unsigned();
                $table->foreign('forecast_id')->references('id')->on('forecast')->onDelete('cascade');

                $table->string('name','255');
                $table->float('coorporate_tax');
                $table->string('coorporate_payable_time', '100');
                $table->float('sales_tax');
                $table->string('sales_payable_time', '100');

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
        Schema::dropIfExists('tax');
    }
}
