<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChapter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('chapter')) {
            Schema::create('chapter', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name','100');
                $table->integer('order');

                $table->integer('plan_id')->unsigned();
                $table->foreign('plan_id')->references('id')->on('plan')->onDelete('cascade');

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
        Schema::dropIfExists('chapter');
    }
}
