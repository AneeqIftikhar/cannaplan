<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCost extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('cost')) {
            Schema::create('cost', function (Blueprint $table) {
                $table->increments('id');

                $table->integer('forecast_id')->unsigned();
                $table->foreign('forecast_id')->references('id')->on('forecast')->onDelete('cascade');

                $table->integer('charge_id');
                $table->string('charge_type' , '50');

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
        Schema::dropIfExists('cost');
    }
}
