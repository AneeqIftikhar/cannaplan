<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLabor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('labor')) {
            Schema::create('labor', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', '255');
                $table->integer('number_of_employees');
                $table->string('labor_type', '100');
                $table->string('staff_role_type', '100');
                $table->float('pay');
                $table->integer('start_date');
                $table->float('annual_raise_percent')->nullable();

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
        Schema::dropIfExists('labor');
    }
}
