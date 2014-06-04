<?php

class UsersController extends BaseController
{

    public function postRegister()
    {
        $mobile_number = Input::get('mobile_number'); //98473793
        $country_code = Input::get('country_code');
        $imei = Input::get('imei'); //357529348654732
        $country = Input::get('country');
        $user = User::firstOrCreate(array('mobile_number' => $mobile_number, 'country_code' => $country_code, 'country' => $country));
        $user->imei = $imei;
        $user->save();
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
}
