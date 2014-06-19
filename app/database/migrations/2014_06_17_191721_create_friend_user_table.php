<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFriendUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('friend_user', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('inviter_id')->unsigned();
            $table->integer('invitee_id')->unsigned();
            $table->foreign('inviter_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('invitee_id')->references('user_id')->on('users')->onDelete('cascade');
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
		Schema::drop('friend_user');
	}

}
