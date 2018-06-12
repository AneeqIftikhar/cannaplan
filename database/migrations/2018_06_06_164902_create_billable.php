<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('billable')) {
            Schema::create('billable', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('hour');
                $table->date('revenue_start_date');
                $table->integer('hourly_rate');

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
        Schema::dropIfExists('billable');
    }
}
