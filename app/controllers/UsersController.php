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
        $user = User::firstOrNew(array('mobile_number' => $mobile_number, 'country_code' => $country_code, 'country' => $country));
        $user->save();
        echo json_encode($user); exit;
        $device_model = Input::get('device_model');
        $code = rand(10000, 99999);
        Verification::create(array('mobile_number' => $mobile_number, 'country_code' => $country_code, 'device_model' => $device_model, 'code' => $code));
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
        $device_model = Input::get("device_model");
        $result = VerificationsController::verify($country_code, $mobile_number, $device_model, $code);

        if (strcmp($result['status'], 'success')) {
            //create xmpp account
        }

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

    public static function UserXmpp($user_id) {
        $hashed_password = Authenticator::where('user_id', '=', $user_id)->first()->getAttribute("hashed_password");
        Log::info('hashed password: ' . $hashed_password);
        $xmpp_password = Authenticator::generateRandomPassword($hashed_password);
        $xmpp_password = substr($xmpp_password, 0, 50);
        Log::info('xmpp password: ' . $xmpp_password);
        return $xmpp_password;
    }

    private static function createXMPPAccount($user, $hashed_password)
    {
        // more info on this restful service http://www.igniterealtime.org/projects/openfire/plugins/userservice/readme.html
        // Please note that the password of this user for their xmpp account cannot exceed more than a certain number of characters.
        $xmpp_credentials = Config::get('xmpp');
        $xmpp_password = UsersController::generateRandomPassword($hashed_password);
        $xmpp_password = substr($xmpp_password, 0, 50);
        Log::info('xmpp password: ' . $xmpp_password);
        $url = $xmpp_credentials["base_url"] . $xmpp_credentials["add"] . $xmpp_credentials["secret_prefix"] .
            $xmpp_credentials["secret"] . "&username=" . $user->id . "&password=" . $xmpp_password;
        Log::info('Outgoing XMPP url: ' . $url);
        $xml_response = file_get_contents($url);
        $xmpp_response = simplexml_load_string($xml_response);
        Log::info("Chat account result!:" . $xml_response);
        return $xmpp_password;
    }

    public function generate_code()
    {
        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        $res = "";
        for ($i = 0; $i < 25; $i++) {
            $res .= $chars[mt_rand(0, strlen($chars)-1)];
        }
        return $res;
    }

    public static function generateRandomPassword($randomString) {
        return hash('sha256', $randomString);
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
