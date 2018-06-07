<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRevenueOnly extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('revenue_only')) {
            Schema::create('revenue_only', function (Blueprint $table) {
                $table->increments('id');
                $table->string('type','50');
                $table->date('start_date');
                $table->integer('amount_m_1');
                $table->integer('amount_m_2');
                $table->integer('amount_m_3');
                $table->integer('amount_m_4');
                $table->integer('amount_m_5');
                $table->integer('amount_m_6');
                $table->integer('amount_m_7');
                $table->integer('amount_m_8');
                $table->integer('amount_m_9');
                $table->integer('amount_m_10');
                $table->integer('amount_m_11');
                $table->integer('amount_m_12');
                $table->integer('amount_y_1');
                $table->integer('amount_y_2');
                $table->integer('amount_y_3');

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
        Schema::dropIfExists('revenue_only');
    }
}
