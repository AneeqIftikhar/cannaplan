<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTargetMarketGraph extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('target_market_graph')) {
            Schema::create('target_market_graph', function (Blueprint $table) {
                $table->increments('id');

                $table->integer('pitch_id')->unsigned();
                $table->foreign('pitch_id')->references('id')->on('pitch')->onDelete('cascade');

                $table->string('segment_name','50');
                $table->integer('segment_prospect');
                $table->integer('prospect_cost');

                $table->softDeletes();
                $table->rememberToken();
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
        Schema::dropIfExists('target_market_graph');
    }
}
