<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSectionContent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('section_content')) {
            Schema::create('section_content', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name','100');
                $table->integer('order');

                $table->integer('section_id')->unsigned();
                $table->foreign('section_id')->references('id')->on('section')->onDelete('cascade');

                $table->integer('content_id');
                $table->string('content_type' , '50');

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
        Schema::dropIfExists('section_content');
    }
}
