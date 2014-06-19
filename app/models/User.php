<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface
{

    use UserTrait, RemindableTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = array('created_at', 'updated_at');
    protected $fillable = array('mobile_number', 'country_code', 'country', 'referral_code');

    public function verification()
    {
        return $this->hasMany('Verification');
    }

    public function login()
    {
        return $this->hasMany('Login');
    }

    public function referral()
    {
        return $this->hasMany('Referral');
    }

    public function friend_user()
    {
        return $this->belongsToMany('FriendUser','friend_user');
    }
}
