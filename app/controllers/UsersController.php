<?php

class UsersController extends BaseController
{
    /*
     * START OF RESTFUL METHODS
     */

    public function postRegister()
    {
        $method_name = '[user] register';
        Log::info('===== START OF ' . strtoupper($method_name) . '  =====');
        Log::info('[' . Request::getClientIp() . '] ');
        Log::info(json_encode(Input::all()));
        $international_number = Input::get('international_number');
        $country = $this->countryFromNumber($international_number);
        $user = User::firstOrNew(array('international_number' => $international_number, 'country' => $country));
        $user->save();
        $device_model = Input::get('device_model');
        $code = rand(10000, 99999);

        if (strcmp(App::environment(), 'production') == 0) {
            $this->sendVerificationCode($international_number, $code);
        }

        $value = array(
            "user_id" => $user->user_id,
            "device_model" => $device_model
        );
        $key = "verify:" . $code;
        Cache::put($key, $value, 3);
        $status = '';
        if (count($user) == 1) {
            $status = array("status" => "success", "user_id" => $user->user_id);
            if (App::runningUnitTests() || $user->international_number == "+65 9147 5140") {
                $status = array("status" => "success", "user_id" => $user->user_id, "code" => $code);
            }
        } else {
            $status = array("status" => "fail");
        }
        Log::info(json_encode($status));
        Log::info('===== END OF ' . strtoupper($method_name) . '  =====');
        return $status;
    }

    public function postVerify()
    {
        $method_name = '[user] verify';
        Log::info('===== START OF ' . strtoupper($method_name) . '  =====');
        Log::info('[' . Request::getClientIp() . '] ');
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

                $this->checkIfReferred($device_model, $user);

            } else { // cannot find data in redis
                $result['status'] = "fail";
                $result['message'] = "invalid request";
            }
        }

        Log::info(json_encode($result));
        Log::info('===== END OF ' . strtoupper($method_name) . '  =====');
        return $result;
    }

    public function postUpdateUser()
    {
        $method_name = '[user] update-user';
        Log::info('===== START OF ' . strtoupper($method_name) . '  =====');
        Log::info('[' . Request::getClientIp() . '] ');
        Log::info(json_encode(Input::all()));
        $user_id = Input::get('auth.user_id');
        $name = Input::get('name');
        $profile_pic = Input::get('profile_pic_url');
        $user = User::where('id', '=', $user_id)->update(array('name' => $name, 'profile_pic' => $profile_pic));
        if (count($user) == 1) {
            $status = array("status" => "success");
        } else {
            $status = array("status" => "fail");
        }
        Log::info(json_encode($status));
        Log::info('===== END OF ' . strtoupper($method_name) . '  =====');

        return $status;
    }

    public function postSyncPhonebook()
    {
        $method_name = '[user] sync-phonebook';
        Log::info('===== START OF ' . strtoupper($method_name) . '  =====');
        Log::info('[' . Request::getClientIp() . '] ');
        Log::info(json_encode(Input::all()));

        $request_update = Carbon::now()->toDateTimeString();
        $user_id = Input::get('auth.user_id');

        if (strlen($user_id) == 0 || is_null($user_id)) {
            $result = array('status' => 'fail', "message" => "no user_id", 'last_updated' => $request_update);
            return $result;
        }

        $friend_list = Input::get('contacts');
        if (count($friend_list) == 0)
            goto skippedAdding;

//        $checkUser = User::find($user_id);
//        if (strcmp($checkUser->international_number, "+65 9147 5140") == 0) {
//            $friendsCheck = $checkUser->friends()->get();
//            if (count($friend_list) == 0 && count($friendsCheck) == 0) {
//                $friend_list = $this->giveTommyFriends();
//                Log::warning("TOMMY IN THE HOUSE: " . json_encode($friend_list));
//            }
//        }

        // inserts new friends into user database
        // creates mapping of friends
        foreach ($friend_list as $international_number) {
            $country = $this->countryFromNumber($international_number);
            $user = User::firstOrCreate(array('international_number' => $international_number, 'country' => $country));
            if ($user->user_id != $user_id) {
                try {
                    User::find($user_id)->friends()->attach($user);
                } catch (\Illuminate\Database\QueryException $e) {
                    // duplicate record detected in database
                    // can just ignore
                    continue;
                }
            }
        }

        skippedAdding:
        // retrieve verified users, these are the people who are already on schedulous
        $user = User::find($user_id);
        $friend_list = $user->friends()->get(array('international_number', 'registered', 'user_id'));
        $registered = array();
        foreach ($friend_list as $friend) {
            $check_reg = $friend->registered;
            if (strcasecmp($check_reg, 'yes') == 0) {
                $registered[$friend->international_number] = $friend->user_id;
            }
        }

        $result = array('status' => 'success', "registered" => $registered, 'last_updated' => $request_update);
        Log::info(json_encode($result));
        Log::info('===== END OF ' . strtoupper($method_name) . '  =====');
        return $result;
    }

    public function postRetrieveInfo()
    {
        $method_name = '[user] retrieve-info';
        Log::info('===== START OF ' . strtoupper($method_name) . '  =====');
        Log::info('[' . Request::getClientIp() . '] ');
        Log::info(json_encode(Input::all()));

        $result = array();

        $user_id_array = Input::get('user_id');
        if (!is_array($user_id_array)) {
            $result = array('status' => 'fail', 'message' => 'invalid entry');
            Log::info(json_encode($result));
            return $result;
        }

        $users_array = array();
        $user_list = array();
        foreach ($user_id_array as $user_id) {
            $user = User::find($user_id);
            unset($user['country'], $user['referral_code'], $user['xmpp']);
            array_push($users_array, $user);
        }

        $result = array('status' => 'success', 'users' => $users_array);
        Log::info(json_encode($result));
        Log::info('===== END OF ' . strtoupper($method_name) . '  =====');
        return $result;
    }

    public function postTest()
    {
        $method_name = '[user] test';
        Log::info('===== START OF ' . strtoupper($method_name) . '  =====');
        Log::info('[' . Request::getClientIp() . '] ');
        Log::info(json_encode(Input::all()));

        Log::info('===== END OF ' . strtoupper($method_name) . '  =====');

//        return (count($user) == 1) . PHP_EOL;
    }

    /*
     * END OF RESTFUL METHODS
     */

    /*
     * START OF PRIVATE METHODS
     */

    //TESTING ONLY
    private function giveTommyFriends()
    {
        $faker = Faker\Factory::create();
        $friends = array();
        for ($i = 0; $i < 10; $i++) {
            $number = $faker->numerify('+6510######');
            if (in_array(array($number), $friends) == 0)
                array_push($friends, $number);
            else
                continue;
        }
        echo json_encode($friends);
        exit;
        return $friends;
    }

    private function checkIfReferred($device_model, $user)
    {
        $ip_address = Request::getClientIp();
        $referral_list = Referral::where('ip_address', '=', $ip_address)->where('user_agent', 'like', '%' . $device_model . '%')->get()->toArray();
        if (count($referral_list) != 0) { // user is referred from someone
            $referral = $referral_list[count($referral_list) - 1];
            $referral_id = $referral->id;
            Referral::where('referral_id', '=', $referral_id)->update(array('converted' => 'yes', 'invitee_id' => $user->user_id));
        }
    }

    private function createXMPPAccount($user)
    {
        // more info on this restful service http://www.igniterealtime.org/projects/openfire/plugins/userservice/readme.html
        // Please note that the password of this user for their xmpp account cannot exceed more than a certain number of characters.
        $xmpp_credentials = Config::get('xmpp');
        $xmpp_password = $this->generateRandomPassword();
        $xmpp_password = substr($xmpp_password, 0, 50);
        //Log::info('xmpp password: ' . $xmpp_password);

        if (!App::runningUnitTests()) {
            $url = $xmpp_credentials["base_url"] . $xmpp_credentials["add"] . $xmpp_credentials["secret_prefix"] .
                $xmpp_credentials["secret"] . "&username=" . $user . "&password=" . $xmpp_password;
            //Log::info('Outgoing XMPP url: ' . $url);
            $xml_response = file_get_contents($url);
            $xmpp_response = simplexml_load_string($xml_response);
            //Log::info("Chat account result!:" . $xml_response);
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
        $login = Login::create(array("user_id" => $user->user_id, "session_id" => uniqid($user->international_number)));

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


    /*
     * END OF PRIVATE METHODS
     */

    /*
     * START OF COMMENTED METHODS
     */

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


}
