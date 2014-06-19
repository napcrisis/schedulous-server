<?php

class Group extends \Eloquent {

	// Add your validation rules here
	public static $rules = [
		// 'title' => 'required'
	];

	// Don't forget to fill this array
	protected $fillable = [];

    public function group_user()
    {
        return $this->belongsToMany('GroupUser','group_user');
    }

}