<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForecast extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('forecast')) {
            Schema::create('forecast', function (Blueprint $table) {
                $table->increments('id');

                $table->integer('company_id')->unsigned();
                $table->foreign('company_id')->references('id')->on('company')->onDelete('cascade');

                $table->string('name','255');
                $table->integer('burden_rate');

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
        Schema::dropIfExists('forecast');
    }
}
