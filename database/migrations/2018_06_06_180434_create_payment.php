<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('payment')) {
            Schema::create('payment', function (Blueprint $table) {
                $table->increments('id');

                $table->integer('other_id')->unsigned();
                $table->foreign('other_id')->references('id')->on('other')->onDelete('cascade');

                $table->integer('amount_m_1')->nullable();
                $table->integer('amount_m_2')->nullable();
                $table->integer('amount_m_3')->nullable();
                $table->integer('amount_m_4')->nullable();
                $table->integer('amount_m_5')->nullable();
                $table->integer('amount_m_6')->nullable();
                $table->integer('amount_m_7')->nullable();
                $table->integer('amount_m_8')->nullable();
                $table->integer('amount_m_9')->nullable();
                $table->integer('amount_m_10')->nullable();
                $table->integer('amount_m_11')->nullable();
                $table->integer('amount_m_12')->nullable();
                $table->integer('amount_y_1')->nullable();
                $table->integer('amount_y_2')->nullable();
                $table->integer('amount_y_3')->nullable();
                $table->integer('amount_y_4')->nullable();
                $table->integer('amount_y_5')->nullable();

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
        Schema::dropIfExists('payment');
    }
}
