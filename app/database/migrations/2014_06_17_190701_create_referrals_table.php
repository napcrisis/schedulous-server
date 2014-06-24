<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateReferralsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->increments('referral_id');
            $table->integer('user_id')->unsigned();
            $table->string('referral_code');
            $table->string('ip_address');
            $table->string('user_agent');
            $table->string('converted')->default('no');
            $table->string('invitee_id')->nullable();
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
