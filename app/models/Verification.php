<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Verification extends Eloquent
{
    use SoftDeletingTrait;
    protected $softDelete = true; 

    // Add your validation rules here
    public static $rules = [
        // 'title' => 'required'
    ];

    // Don't forget to fill this array
    protected $fillable = array('user_id', 'device_model', 'code');

    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo('User');
    }

}