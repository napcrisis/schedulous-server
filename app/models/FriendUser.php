<?php

class FriendUser extends Eloquent {

    // Add your validation rules here
    public static $rules = [
        // 'title' => 'required'
    ];

    // Don't forget to fill this array
    protected $fillable = ['inviter_id','invitee_id'];

    public function inviter()
    {
        return $this->belongsToMany('User','user','user_id','inviter_id');
    }

    public function invitee()
    {
        return $this->belongsToMany('User','user','user_id','invitee_id');
    }
}