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
            $table->increments('id');
            $table->string('international_number')->unique();
            $table->string('name')->nullable();
            $table->string('profile_pic')->nullable();
            $table->string('country');
            $table->string('referral_code')->nullable()->unique();
            $table->string('xmpp')->nullable()->unique();
            $table->string('registered')->default('no');
            $table->timestamp('registered_on')->nullable();
            $table->timestamps();
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
