<?php

class UsersController extends BaseController
{

    public function postRegister()
    {
        $method_name = 'register';
        Log::info('===========================================');
        Log::info('[' . Request::getClientIp() . '] ' . $method_name);
        Log::info(json_encode(Input::all()));
        $international_number = Input::get('international_number');
        $country = strtolower(Input::get('country'));
        $user = User::firstOrNew(array('international_number' => $international_number, 'country' => $country));
        $user->save();
        $device_model = Input::get('device_model');
        $code = rand(10000, 99999);

        if (strcmp(App::environment(), 'production') == 0) {
            $this->sendVerificationCode($international_number, $code);
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
            if (App::runningUnitTests()) {
                $status = array("status" => "success", "user_id" => $user->id, "code" => $code);
            }
        } else {
            $status = array("status" => "fail");
        }
        Log::info(json_encode($status));
        Log::info('===========================================');
        return $status;
    }

    public function postVerify()
    {
        $method_name = 'verify';
        Log::info('===========================================');
        Log::info('[' . Request::getClientIp() . '] ' . $method_name);
        Log::info(json_encode(Input::all()));
        $user_id = Input::get("user_id");
        $code = Input::get("code");
        $device_model = Input::get("device_model");

        $result = '';
        $key = 'verify:' . $code;
        if (Cache::has($key)) {
            $value = Cache::get($key);
            // comparing request inputs with redis data
            if (strcmp($value['device_model'], $device_model) == 0 && strcmp($value['user_id'], $user_id) == 0) {
                $session_id = $this->verify($user_id);
                $user = User::find($user_id);
                if (is_null($user->xmpp)) {
                    $xmpp_password = $this->createXMPPAccount($user_id);
                    $user->xmpp = $xmpp_password;
                    $user->save();
                }
                $result = array('status' => 'success', 'message' => 'verified', 'session_id' => $session_id, 'user' => $user);
                Cache::forget($key);
            } else { // cannot find data in redis
                $result['status'] = "fail";
                $result['message'] = "invalid request";
            }
        }
        Log::info(json_encode($result));
        Log::info('===========================================');
        return $result;
    }

    public function postUpdateName()
    {
        $method_name = 'update-name';
        Log::info('===========================================');
        Log::info('[' . Request::getClientIp() . '] ' . $method_name);
        Log::info(json_encode(Input::all()));
        $user_id = Input::get('user_id');
        $name = Input::get('name');
        $user = User::where('id', '=', $user_id)->update(array('name' => $name));
        $status = '';
        if (count($user) == 1) {
            $status = array("status" => "success");
        } else {
            $status = array("status" => "fail");
        }
        Log::info(json_encode($status));
        Log::info('===========================================');

        return $status;
    }

    public function postUpdatePic()
    {
        $method_name = 'update-pic';
        Log::info('===========================================');
        Log::info('[' . Request::getClientIp() . '] ' . $method_name);
        Log::info(json_encode(Input::all()));

        //$picture = Input::get('picture');
    }

    public function postTest()
    {
        $method_name = 'test';
        Log::info('===========================================');
        Log::info('[' . Request::getClientIp() . '] ' . $method_name);
        Log::info(json_encode(Input::all()));

        $user = User::find(1);
        Log::info('===========================================');

        return (count($user) == 1) . PHP_EOL;
    }

    /*  not needed anymore. but still keeping it for future reference.
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
    */

    public function postSyncPhonebook()
    {
        $method_name = 'sync-phonebook';
        Log::info('===========================================');
        Log::info('[' . Request::getClientIp() . '] ' . $method_name);
        Log::info(json_encode(Input::all()));

        $user_id = Input::get('user_id');
        $friend_list = Input::get('friends');
        $request_update = Carbon::now()->toDateTimeString();

        // inserts new friends into user database
        // creates mapping of friends
        foreach ($friend_list as $friend) {
            $international_number = $friend['international_number'];
            $country = strtolower($friend['country']);
            $user = User::firstOrCreate(array('international_number' => $international_number, 'country' => $country));
            if ($user->id != $user_id) {
                User::find($user_id)->friends()->attach($user);
            }
        }

        // retrieve verified users, these are the people who are already on schedulous
        $user = User::find($user_id);
        $friend_list = $user->friends()->get(array('international_number', 'registered'));
        $registered = array();
        foreach ($friend_list as $friend) {
            $check_reg = $friend->registered;
            if (strcmp($check_reg, 'yes')) {
                array_push($registered, $friend->international_number);
            }
        }

        $result = array('status' => 'success', "registered" => $registered, 'last_updated' => $request_update);
        Log::info(json_encode($result));
        Log::info('===========================================');

        return $result;
    }

    private function createXMPPAccount($user)
    {
        // more info on this restful service http://www.igniterealtime.org/projects/openfire/plugins/userservice/readme.html
        // Please note that the password of this user for their xmpp account cannot exceed more than a certain number of characters.
        $xmpp_credentials = Config::get('xmpp');
        $xmpp_password = $this->generateRandomPassword();
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

    private function verify($user_id)
    {
        $user = User::find($user_id);

        if ($user->referral_code == null) {
            $referral_code = parent::generate_code();
            $user->referral_code = $referral_code;
            $user->registered = 'yes';
            $user->registered_on = Carbon::now();
            $user->save();
        }

        //create login entry to mark session of user
        $login = Login::create(array("user_id" => $user->id, "session_id" => uniqid($user->international_number)));

        return $login->session_id;
    }

    private function generateRandomPassword()
    {
        $randomString = parent::generate_code();
        return hash('sha256', $randomString);
    }

    private function sendVerificationCode($international_number, $code)
    {
        SMSGateway::sendVerificationCode($international_number, $code);
    }

    public function missingMethod($parameters = array())
    {
        return "invalid entry";
    }
}
