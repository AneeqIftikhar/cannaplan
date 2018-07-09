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
                $table->date('revenue_start_date');
                $table->integer('amount')->nullable();
                $table->string('amount_duration')->nullable();
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
        Schema::dropIfExists('revenue_only');
    }
}
