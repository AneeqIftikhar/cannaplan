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

                $table->string('company_name','255')->nullable();
                $table->string('logo','255')->nullable();
                $table->string('headline','255')->nullable();
                $table->string('problem','255')->nullable();
                $table->string('solution','255')->nullable();
                $table->integer('funds_required')->nullable();
                $table->string('funds_usage_description','255')->nullable();
                $table->string('sales_channel','255')->nullable();
                $table->string('marketing_activities','255')->nullable();
                $table->string('forecast_revenue','255')->nullable();
                $table->string('forecast_cost','255')->nullable();
                $table->string('forecast_type','100')->nullable();

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
