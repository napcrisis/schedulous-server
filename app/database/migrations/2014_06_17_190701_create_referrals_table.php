<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReferralsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('referrals', function(Blueprint $table)
		{
			$table->increments('referral_id');
			$table->integer('user_id')->unsigned();
            $table->string('referral_code')->unique();
            $table->string('ip_address');
            $table->string('device_model');
            $table->string('converted')->default('no');
            $table->string('invitee_country_code');
            $table->string('invitee_phone_number');
			$table->timestamps();
            $table->foreign('user_id')->references('user_id')->on('users');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('referrals');
	}

}
