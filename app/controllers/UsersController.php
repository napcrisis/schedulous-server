<?php

class UsersController extends BaseController
{

    public function postRegister()
    {
        Log::info('[' . Request::getClientIp() . '] [incoming] ' . json_encode(Input::all()));
        $mobile_number = Input::get('mobile_number'); //98473793
        $country_code = Input::get('country_code');
        $country = Input::get('country');
        $user = User::firstOrCreate(array('mobile_number' => $mobile_number, 'country_code' => $country_code, 'country' => $country));
//        $user->save();
    }

    public function postUpdateName()
    {
        $mobile_number = Input::get('mobile_number');
        $user = User::find($mobile_number);
        $name = Input::get('name');
        $user->name = $name;
        $user->save();
    }

    public function postUpdatePic()
    {
        $picture = Input::get('picture');
    }

public function missingMethod($parameters = array())
{
    return "invalid entry";
}
}
