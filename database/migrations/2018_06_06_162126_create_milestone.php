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

                $table->string('title');
                $table->date('due_date');
                $table->string('responsible','100')->nullable();
                $table->string('details','255')->nullable();
                $table->boolean('email_reminder')->default(false);

                $table->boolean('is_completed')->default(false);

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
        Schema::dropIfExists('milestone');
    }
}
