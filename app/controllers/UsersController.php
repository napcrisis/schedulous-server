<?php

class UsersController extends BaseController
{

    public function postRegister()
    {
        $mobile_number = Input::get('mobile_number'); //+65-98473793
        $imei = Input::get('imei'); //357529348654732
        $country = Input::get('country');
        $user = User::firstOrCreate(array('mobile_number' => $mobile_number, 'country' => $country));
        $user->imei = $imei;
        $user->save();
    }

    public function postUpdateName()
    {
        $mobile_number = Input::get('mobile_number');
        $user = User::find($mobile_number);
        $name = Input::get('name');
        $user->name=$name;
        $user->save();
    }

    public function postUpdatePic()
    {
        $picture = Input::get('picture');
    }
}
