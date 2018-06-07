<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMilestone extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('milestone')) {
            Schema::create('milestone', function (Blueprint $table) {
                $table->increments('id');

                $table->integer('pitch_id')->unsigned();
                $table->foreign('pitch_id')->references('id')->on('pitch')->onDelete('cascade');

                $table->date('due_date');
                $table->string('responsible','100');
                $table->string('details','255');
                $table->boolean('email_reminder');
                $table->integer('prospect_cost');

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
        Schema::dropIfExists('milestone');
    }
}
