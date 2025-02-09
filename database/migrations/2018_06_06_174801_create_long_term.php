<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLongTerm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('long_term')) {
            Schema::create('long_term', function (Blueprint $table) {
                $table->increments('id');
                $table->float('year');
                $table->boolean('will_sell');
                $table->integer('selling_amount')->nullable();
                $table->date('selling_date')->nullable();

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
        Schema::dropIfExists('long_term');
    }
}
