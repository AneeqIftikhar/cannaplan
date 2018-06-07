<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFinancing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('financing')) {
            Schema::create('financing', function (Blueprint $table) {
                $table->increments('id');

                $table->integer('forecast_id')->unsigned();
                $table->foreign('forecast_id')->references('id')->on('forecast')->onDelete('cascade');

                $table->string('name','255');

                $table->integer('fund_id');
                $table->string('fund_type','255');

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
        Schema::dropIfExists('financing');
    }
}
