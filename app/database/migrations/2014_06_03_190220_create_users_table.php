<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
            $table->string('country_code');
            $table->string('mobile_number');
            $table->string('name');
            $table->string('profile_pic');
            $table->string('country');
            $table->timestamps();
            $table->primary(array('country_code', 'mobile_number'));
        });
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
