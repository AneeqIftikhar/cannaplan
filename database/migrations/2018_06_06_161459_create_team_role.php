<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('team_role')) {
            Schema::create('team_role', function (Blueprint $table) {
                $table->increments('id');

                $table->integer('pitch_id')->unsigned();
                $table->foreign('pitch_id')->references('id')->on('pitch')->onDelete('cascade');

                $table->string('name','100');
                $table->string('job_title','100');
                $table->string('biography','255')->nullable();
                $table->string('image','100')->nullable();

                $table->integer('order');

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
        Schema::dropIfExists('team_role');
    }
}
