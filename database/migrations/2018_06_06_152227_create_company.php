<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('company')) {
            Schema::create('company', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title','255');
                $table->string('business_stage','255');
                $table->date('start_of_forecast');
                $table->string('length_of_forecast','255');

                $table->integer('currency_id')->unsigned();
                $table->foreign('currency_id')->references('id')->on('currency')->onDelete('cascade');

                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

                $table->integer('created_by')->nullable();
                $table->integer('selected_forecast')->nullable();

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
        Schema::dropIfExists('company');
    }
}
