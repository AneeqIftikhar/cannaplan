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

                $table->integer('amount_m_1')->default(0);
                $table->integer('amount_m_2')->default(0);
                $table->integer('amount_m_3')->default(0);
                $table->integer('amount_m_4')->default(0);
                $table->integer('amount_m_5')->default(0);
                $table->integer('amount_m_6')->default(0);
                $table->integer('amount_m_7')->default(0);
                $table->integer('amount_m_8')->default(0);
                $table->integer('amount_m_9')->default(0);
                $table->integer('amount_m_10')->default(0);
                $table->integer('amount_m_11')->default(0);
                $table->integer('amount_m_12')->default(0);
                $table->integer('amount_y_1')->default(0);
                $table->integer('amount_y_2')->default(0);
                $table->integer('amount_y_3')->default(0);
                $table->integer('amount_y_4')->default(0);
                $table->integer('amount_y_5')->default(0);

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
