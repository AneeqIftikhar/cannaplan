<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePitch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('pitch')) {
            Schema::create('pitch', function (Blueprint $table) {
                $table->increments('id');

                $table->integer('company_id')->unsigned();
                $table->foreign('company_id')->references('id')->on('company')->onDelete('cascade');

                $table->string('company_name','255');
                $table->string('logo','255');
                $table->string('headlights','255');
                $table->string('problem','255');
                $table->string('solution','255');
                $table->integer('funds_required');
                $table->string('funds_usage_description','255');
                $table->string('sales_channel','255');
                $table->string('marketing_activities','255');
                $table->string('forecast_revenue','255');
                $table->string('forecast_cost','255');
                $table->string('forecast_type','100');

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
        Schema::dropIfExists('pitch');
    }
}
