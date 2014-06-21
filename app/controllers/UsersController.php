<?php

class UsersController extends BaseController
{

    public function postRegister()
    {
        $method_name = 'register';
        Log::info('[' . Request::getClientIp() . '] ' . $method_name . ': ' . json_encode(Input::all()));
        $mobile_number = Input::get('mobile_number');
        $country_code = Input::get('country_code');
        $country = strtolower(Input::get('country'));
        $international_number = $this->processIntNum($country_code, $mobile_number);
        $user = User::firstOrNew(array('mobile_number' => $mobile_number, 'country_code' => $country_code,
            'international_number' => $international_number, 'country' => $country));
        $user->save();
        $device_model = Input::get('device_model');
        $code = rand(10000, 99999);

        Verification::create(array('user_id' => $user->id, 'device_model' => $device_model, 'code' => $code));
        if (strcmp(App::environment(), 'production') == 0) {
            $this->sendVerificationCode($country_code, $mobile_number, $code);
        }

        $value = array(
            "user_id" => $user->id,
            "device_model" => $device_model
        );
        $key = "verify:" . $code;
        Cache::put($key, $value, 3);
        $status = '';
        if (count($user) == 1) {
            $status = array("status" => "success", "user_id" => $user->id);
        } else {
            $status = array("status" => "fail");
        }
        return $status;
    }

    public function postVerify()
    {
        $method_name = 'verify';
        Log::info('[' . Request::getClientIp() . '] ' . $method_name . ': ' . json_encode(Input::all()));
        $user_id = Input::get("user_id");
        $code = Input::get("code");
        $device_model = Input::get("device_model");

        $result = '';
        $key = 'verify:' . $code;
        if (Cache::has($key)) {
            $value = Cache::get($key);
            // comparing request inputs with redis data
            if (strcmp($value['device_model'], $device_model) == 0 && strcmp($value['user_id'], $user_id) == 0) {
                $session_id = VerificationsController::verify($user_id);
                $user = User::find($user_id);
                if (is_null($user->xmpp)) {
                    $xmpp_password = $this::createXMPPAccount($user_id);
                    $user->xmpp = $xmpp_password;
                    $user->save();
                }
                $result = array('status' => 'success', 'message' => 'verified', 'session_id' => $session_id, 'user' => $user);
            } else { // cannot find data in redis
                $result['status'] = "fail";
                $result['message'] = "invalid request";
            }
        }
        return $result;
    }
//        if (count($user) == 1) {
//            $result = VerificationsController::verify($user_id, $device_model, $code);
//            if (strcmp($result['status'], 'success') == 0 && is_null($user->xmpp)) {
//                $xmpp_password = $this::createXMPPAccount($user_id);
//                $user->xmpp = $xmpp_password;
//                $user->save();
//                $result['user'] = $user;
//            } elseif (strcmp($result['status'], 'success') == 0 && !is_null($user->xmpp)) {
//                $result['user'] = $user;
//            } else {
//                //do nothing
//                // error message already provided in VerificationsController::verify
//            }
//        } else {
//            $result['status'] = "fail";
//            $result['message'] = "user not found";
//        }


    public function postUpdateName()
    {
        $method_name = 'update-name';
        Log::info('[' . Request::getClientIp() . '] ' . $method_name . ': ' . json_encode(Input::all()));
        $user_id = Input::get('user_id');
        $name = Input::get('name');
        $user = User::where('id', '=', $user_id)->update(array('name' => $name));
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

    public function postTest()
    {
        $user = User::find(1);
        return (count($user) == 1) . PHP_EOL;
    }

    public function processIntNum($country_code, $mobile_number)
    {
        $num = $country_code . $mobile_number;
        $phone_util = PhoneNumberUtil::getInstance();
        $result = '';
        try {
            $num_proto = $phone_util->parse($num, "SG");
            $result = $phone_util->format($num_proto, PhoneNumberFormat::INTERNATIONAL);
        } catch (\libphonenumber\NumberParseException $e) {
            echo $e->getMessage();
        }
        return $result;
    }

    public function postSyncPhonebook()
    {
        $user_id = Input::get('user_id');
        $friend_list = Input::get('friends');
        $request_update = Carbon::now()->toDateTimeString();

        // inserts new friends into user database
        // creates mapping of friends
        foreach ($friend_list as $friend) {
            $mobile_number = $friend['mobile_number'];
            $country_code = $friend['country_code'];
            $country = strtolower($friend['country']);
            $international_number = $this->processIntNum($country_code, $mobile_number);
            $user = User::firstOrCreate(array('mobile_number' => $mobile_number, 'country_code' => $country_code,
                'international_number' => $international_number, 'country' => $country));
            if ($user->id != $user_id) {
                User::find($user_id)->friends()->attach($user);
            }
        }

        // retrieve verified users, these are the people who are already on schedulous
        $user = User::find($user_id);
        $friend_list = $user->friends()->get(array('mobile_number', 'registered'));
        $registered = array();
        foreach ($friend_list as $friend) {
            $check_reg = $friend->registered;
            if (strcmp($check_reg, 'yes')) {
                array_push($registered, $friend->mobile_number);
            }
        }

        $result = array('status' => 'success', "registered" => $registered, 'last_updated' => $request_update);
        return $result;
    }

    private static function createXMPPAccount($user)
    {
        // more info on this restful service http://www.igniterealtime.org/projects/openfire/plugins/userservice/readme.html
        // Please note that the password of this user for their xmpp account cannot exceed more than a certain number of characters.
        $xmpp_credentials = Config::get('xmpp');
        $xmpp_password = UsersController::generateRandomPassword();
        $xmpp_password = substr($xmpp_password, 0, 50);
        Log::info('xmpp password: ' . $xmpp_password);

        if (!App::runningUnitTests()) {
            $url = $xmpp_credentials["base_url"] . $xmpp_credentials["add"] . $xmpp_credentials["secret_prefix"] .
                $xmpp_credentials["secret"] . "&username=" . $user . "&password=" . $xmpp_password;
            Log::info('Outgoing XMPP url: ' . $url);
            $xml_response = file_get_contents($url);
            $xmpp_response = simplexml_load_string($xml_response);
            Log::info("Chat account result!:" . $xml_response);
        }
        return $xmpp_password;
    }

    public static function generateRandomPassword()
    {
        $randomString = parent::generate_code();
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
