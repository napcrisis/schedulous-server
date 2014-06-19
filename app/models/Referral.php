<?php

class Referral extends \Eloquent {

	// Add your validation rules here
	public static $rules = [
		// 'title' => 'required'
	];

	// Don't forget to fill this array
	protected $fillable = ['user_id','referral_code','ip_address','device_model'];

    public function user()
    {
        return $this->belongsTo('User');
    }

}