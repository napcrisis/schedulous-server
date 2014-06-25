<?php

class ReferralsController extends BaseController
{

    public function markIncomingReferral($code = null)
    {
        $method_name = '[referral] incoming referral';
        Log::info('===== START OF ' . strtoupper($method_name) . '  =====');
        Log::info('[' . Request::getClientIp() . '] ');
        Log::info($code);

//        @todo : must verify referral code validity before continuing

        if (is_null($code))
            goto user_agent_check;

        $referral_count = User::where('referral_code', '=', $code)->get()->count();
        if ($referral_count == 0)
            goto user_agent_check;

        // record IP address
        // record user agent
        // record referral code
        // record user_id
        $ip_address = Request::getClientIp();
        $user_agent = Agent::getUserAgent() . '<br><br>';
        $user_id = User::where('referral_code', '=', $code)->get(array('user_id'))[0]->user_id;
        $referral = Referral::create(array('ip_address' => $ip_address, 'user_agent' => $user_agent, 'referral_code' => $code, 'user_id' => $user_id));
        User::find($user_id)->referrals()->save($referral);

        user_agent_check:
//        echo true;
        if (!Agent::isMobile()) {
            Log::info('===== END OF ' . strtoupper($method_name) . '  =====');
            return View::make('referrals.error', array('message' => 'Open the referral link on your device to begin using Schedulous'));
        }

        if (strcasecmp(Agent::platform(), "iOS") == 0) {
            Log::info('===== END OF ' . strtoupper($method_name) . '  =====');
            return View::make('referrals.error', array('message' => 'Schedulous is only available on Android <br> iPhone version will be released soon'));
        }

        // download apk file
        download:
        $filename = 'schedulous.apk';
        $file = public_path() . "/download/" . $filename;
        $headers = array(
            'Content-Type: application/vnd.android.package-archive',
        );
        //return Response::download($file, $filename, $headers);
        echo "download link";

        Log::info('===== END OF ' . strtoupper($method_name) . '  =====');
    }

    public function missingMethod($parameters = array())
    {
        return "invalid entry";
    }

}
