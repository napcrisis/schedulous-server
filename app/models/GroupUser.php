<?php

class GroupUser extends Eloquent {

    // Add your validation rules here
    public static $rules = [
        // 'title' => 'required'
    ];

    // Don't forget to fill this array
    protected $fillable = ['group_id','user_id'];

    public function user()
    {
        return $this->belongsToMany('User','user','user_id','user_id');
    }

    public function group()
    {
        return $this->belongsToMany('Group','group','group_id','group_id');
    }

}