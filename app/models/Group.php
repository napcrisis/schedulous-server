<?php

class Group extends Eloquent
{

    // Add your validation rules here
    public static $rules = [
        // 'title' => 'required'
    ];

    protected $primaryKey = 'group_id';

    // Don't forget to fill this array
    protected $fillable = array('group_name');

    protected $hidden = array('deleted_at', 'created_at');

    public function group_user()
    {
        return $this->belongsToMany('User', 'group_users', 'group_id', 'user_id');
    }

}