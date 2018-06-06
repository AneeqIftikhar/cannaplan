<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetitor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('competitor')) {
            Schema::create('competitor', function (Blueprint $table) {
                $table->increments('id');

                $table->integer('pitch_id')->unsigned();
                $table->foreign('pitch_id')->references('id')->on('pitch')->onDelete('cascade');

                $table->string('name','255');
                $table->string('advantage','255');

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
        Schema::dropIfExists('competitor');
    }
}
