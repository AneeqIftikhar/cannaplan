<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('plan')) {
            Schema::create('plan', function (Blueprint $table) {
                $table->increments('id');

                $table->boolean('is_started')->default(false);

                $table->integer('company_id')->unsigned();
                $table->foreign('company_id')->references('id')->on('company')->onDelete('cascade');

                $table->integer('created_by')->nullable();
                $table->mediumText('planJson')->nullable();
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
        Schema::dropIfExists('plan');
    }
}
