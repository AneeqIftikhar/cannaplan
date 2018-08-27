<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTopic extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('topic')) {
            Schema::create('topic', function (Blueprint $table) {
                $table->increments('id');
                $table->text('description')->nullable();
                $table->string('name');

                $table->integer('company_id')->unsigned();
                $table->foreign('company_id')->references('id')->on('company')->onDelete('cascade');

                $table->boolean('is_removed')->default(false);
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
        Schema::dropIfExists('topic');
    }
}
