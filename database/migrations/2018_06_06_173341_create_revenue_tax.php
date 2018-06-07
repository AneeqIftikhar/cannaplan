<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRevenueTax extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('revenue_tax')) {
            Schema::create('revenue_tax', function (Blueprint $table) {
                $table->increments('id');

                $table->integer('revenue_id')->unsigned();
                $table->foreign('revenue_id')->references('id')->on('revenue')->onDelete('cascade');

                $table->integer('tax_id')->unsigned();
                $table->foreign('tax_id')->references('id')->on('tax')->onDelete('cascade');

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
        Schema::dropIfExists('revenue_tax');
    }
}
