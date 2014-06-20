<?php

class Login extends \Eloquent {

	// Add your validation rules here
	public static $rules = [
		// 'title' => 'required'
	];

	// Don't forget to fill this array
    protected $fillable = ['user_id', 'session_id'];

    public function user()
    {
        return $this->belongsTo('User');
    }
}