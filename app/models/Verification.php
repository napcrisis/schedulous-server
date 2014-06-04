<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Verification extends Eloquent
{
    use SoftDeletingTrait;

    // Add your validation rules here
    public static $rules = [
        // 'title' => 'required'
    ];

    // Don't forget to fill this array
    protected $fillable = array('mobile_number', 'country_code', 'country', 'device_name', 'code');

    protected $dates = ['deleted_at'];

}