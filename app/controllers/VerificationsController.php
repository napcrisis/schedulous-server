<?php

class VerificationsController extends BaseController
{

    public static function verify($country_code, $mobile_number, $device_name, $verification_code)
    {
        $stored_result = Verification::where("country_code", "=", $country_code)
            ->where("mobile_number", "=", $mobile_number)
            ->where('device_name', '=', $device_name)
            ->get(array('id', 'code'));

//        echo $stored_result; exit;

        if(count($stored_result)!=0)    {
            goto verify;
        }   else{
            goto forced_url;
        }

        verify:
        $stored_code = $stored_result[0]->code;
        $stored_id = $stored_result[0]->id;

        if (strcmp($stored_code, $verification_code) == 0) {
            Verification::where('id','=',$stored_id)->update(array('verified'=>'true'));
            Verification::find($stored_id)->delete();
            return array('status'=>'success','message'=>'verified');
        }
        return array('status'=>'fail','message'=>'incorrect verification code');

        forced_url:
        return array('status'=>'fail','message'=>'what brings you here?');
    }

    public static function sendVerificationCode($country_code, $mobile_number, $verification_code)
    {
        /*
         * Telerivet API Example (PHP) - Sending SMS from a form on your website
         * --------------------------------------------------------------------
         * To run this example:
         * 1. Save a copy of this file
         * 2. Replace the API settings below with the values from the API page
         * 3. Upload the modified file to your web server with the extension .php.
         * 4. Open the page in your browser (e.g. http://your-web-server.com/send_api_example.php)
         */

        $api_key = Config::get('telerivet/telerivet.api_key');
        $project_id = Config::get('telerivet/telerivet.project_id');
        $phone_id = Config::get('telerivet/telerivet.phone_id');

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,
            "https://api.telerivet.com/v1/projects/$project_id/messages/outgoing");
        curl_setopt($curl, CURLOPT_USERPWD, "{$api_key}:");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query(array(
            'content' => $verification_code,
            'phone_id' => $phone_id,
            'to_number' => $country_code . $mobile_number,
        ), '', '&'));

        // if you get SSL errors, download SSL certs from https://telerivet.com/_media/cacert.pem .
        curl_setopt($curl, CURLOPT_CAINFO, app_path() . '/config/telerivet/telerivet_cacert.pem');

        $json = curl_exec($curl);
        $network_error = curl_error($curl);
        curl_close($curl);

        if ($network_error) {
            echo $network_error; // do something with the error message
        } else {
            $res = json_decode($json, true);
            if (isset($res['error'])) {
                // API error
                return "false";
            } else {
                // success!
                return "true";
            }
        }
    }

    public function missingMethod($parameters = array())
    {
        return "invalid entry";
    }

}
