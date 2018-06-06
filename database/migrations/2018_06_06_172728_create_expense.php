<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpense extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('expense')) {
            Schema::create('expense', function (Blueprint $table) {
                $table->increments('id');

                $table->integer('forecast_id')->unsigned();
                $table->foreign('forecast_id')->references('id')->on('forecast')->onDelete('cascade');

                $table->string('name','255');
                $table->string('type','100');
                $table->integer('amount');
                $table->date('start_date');

                $table->softDeletes();
                $table->rememberToken();
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
        Schema::dropIfExists('expense');
    }
}
