<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostOnRevenue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cost_on_revenue')) {
            Schema::create('cost_on_revenue', function (Blueprint $table) {
                $table->increments('id');

                $table->integer('revenue_id')->unsigned();
                $table->foreign('revenue_id')->references('id')->on('revenue')->onDelete('cascade');

                $table->integer('amount');

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
        Schema::dropIfExists('cost_on_revenue');
    }
}
