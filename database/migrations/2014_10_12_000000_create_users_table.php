<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('first_name','100');
                $table->string('last_name','100');
                $table->string('email','100')->unique();
                $table->string('password','100');
                $table->string('status','100');
                $table->rememberToken();
                $table->timestamps();
            });
        }
        else
        {
            Schema::table('users', function (Blueprint $table) {
                $table->softDeletes();
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
        Schema::dropIfExists('users');
    }
}
