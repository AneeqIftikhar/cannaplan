<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('loan')) {
            Schema::create('loan', function (Blueprint $table) {
                $table->increments('id');
                $table->date('receive_date');
                $table->integer('remaining_amount')->nullable();
                $table->integer('amount');
                $table->integer('interest_rate');
                $table->integer('interest_months')->nullable();

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
        Schema::dropIfExists('loan');
    }
}
