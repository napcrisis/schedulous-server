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

    protected $primaryKey = 'user_id';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = array('created_at', 'updated_at', 'registered', 'registered_on');
    protected $fillable = array('international_number', 'country', 'referral_code');

    public function logins()
    {
        return $this->hasMany('Login');
    }

    public function referrals()
    {
        return $this->hasMany('Referral');
    }

    public function friends()
    {
        return $this->belongsToMany('User', 'friend_users', 'inviter_id', 'invitee_id');
    }
}
