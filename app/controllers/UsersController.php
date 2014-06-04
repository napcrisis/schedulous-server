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
        if (count($user) == 1) {
            $status = array("status" => "success", "user" => $user);
            return $status;
        } else {
            $status = array("status" => "fail");
            return $status;
        }
    }

    public function postUpdateName()
    {
        Log::info('[' . Request::getClientIp() . '] [incoming] ' . json_encode(Input::all()));
        $mobile_number = Input::get('mobile_number'); //98473793
        $country_code = Input::get('country_code');
        $name = Input::get('name');
        $user = User::where('country_code', '=', $country_code)->where('mobile_number', '=', $mobile_number)->update(array('name' => $name));
        $status='';
        if (count($user) == 1) {
            $status = array("status" => "success");
        } else {
            $status = array("status" => "fail");
        }
        return $status;
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
