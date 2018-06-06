<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDirect extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('direct')) {
            Schema::create('direct', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name','255');

                $table->integer('direct_cost_id');
                $table->string('direct_cost_type' , '50');

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
        Schema::dropIfExists('direct');
    }
}
