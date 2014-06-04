<?php

class UsersController extends BaseController
{

    public function postRegister()
    {
        $method_name = 'register';
        Log::info('[' . Request::getClientIp() . '] ' . $method_name . ': ' . json_encode(Input::all()));
        $mobile_number = Input::get('mobile_number'); //98473793
        $country_code = Input::get('country_code');
        $country = Input::get('country');
        $user = User::firstOrCreate(array('mobile_number' => $mobile_number, 'country_code' => $country_code, 'country' => $country));

        $device_name = Input::get('device_name');
        $code = rand(1000, 9999);
        Verification::create(array('mobile_number' => $mobile_number, 'country_code' => $country_code, 'device_name' => $device_name, 'code' => $code));
        $this->sendVerificationCode($country_code, $mobile_number, $code);
        $status = '';
        if (count($user) == 1) {
            $status = array("status" => "success", "user" => $user);
        } else {
            $status = array("status" => "fail");
        }
        return $status;
    }

    public function postVerify()
    {
        $method_name = 'verify';
        Log::info('[' . Request::getClientIp() . '] ' . $method_name . ': ' . json_encode(Input::all()));
        $mobile_number = Input::get('mobile_number'); //98473793
        $country_code = Input::get('country_code');
        $code = Input::get("code");
        $device_name = Input::get("device_name");
        $result = VerificationsController::verify($country_code, $mobile_number, $device_name, $code);

        return $result;
    }

    public function postUpdateName()
    {
        $method_name = 'update-name';
        Log::info('[' . Request::getClientIp() . '] ' . $method_name . ': ' . json_encode(Input::all()));
        $mobile_number = Input::get('mobile_number'); //98473793
        $country_code = Input::get('country_code');
        $name = Input::get('name');
        $user = User::where('country_code', '=', $country_code)->where('mobile_number', '=', $mobile_number)->update(array('name' => $name));
        $status = '';
        if (count($user) == 1) {
            $status = array("status" => "success");
        } else {
            $status = array("status" => "fail");
        }
        return $status;
    }

    public function postUpdatePic()
    {
        $method_name = 'update-pic';
        Log::info('[' . Request::getClientIp() . '] ' . $method_name . ': ' . json_encode(Input::all()));

        $picture = Input::get('picture');
    }

    private static function sendVerificationCode($country_code, $mobile_number, $code)
    {
        VerificationsController::sendVerificationCode($country_code, $mobile_number, $code);
    }

    public function missingMethod($parameters = array())
    {
        return "invalid entry";
    }
}
