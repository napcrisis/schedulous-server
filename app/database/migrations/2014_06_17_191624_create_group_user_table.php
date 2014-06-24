<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('group_users', function (Blueprint $table)
        {
            $table->increments('id')->unsigned();
            $table->integer('group_id')->unsigned();
            $table->integer('user_id')->unsigned();
            //$table->primary(array('id','group_id','user_id'));
            $table->foreign('group_id')->references('group_id')->on('groups')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
