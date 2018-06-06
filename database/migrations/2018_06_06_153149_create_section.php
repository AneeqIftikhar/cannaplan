<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('section')) {
            Schema::create('section', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name','100');
                $table->integer('order');

                $table->integer('chapter_id')->unsigned();
                $table->foreign('chapter_id')->references('id')->on('chapter')->onDelete('cascade');

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
        Schema::dropIfExists('section');
    }
}
