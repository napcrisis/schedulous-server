<?php

class ReferralsController extends BaseController
{

    public static function markIncomingReferral($code)
    {
//        @todo : must verify referral code validity before continuing
        // record IP address
        // record user agent
        // record referral code
        // record user_id
        $ip_address = Request::getClientIp();
        $user_agent = Agent::getUserAgent() . '<br><br>';
        $user_id = User::where('referral_code', '=', $code)->get(array('user_id'));

        if (is_null($user_id) != 0) {
            $user_id = $user_id[0]->user_id;
            $referral = Referral::create(array('ip_address' => $ip_address, 'user_agent' => $user_agent,
                'referral_code' => $code, 'user_id' => $user_id));
            User::find($user_id)->referrals()->save($referral);
            echo "you have used " . $code . '<br>';
        }

        // download apk file
        $filename = 'schedulous_alpha.apk';
        $file = public_path() . "/download/" . $filename;
        $headers = array(
            'Content-Type: application/vnd.android.package-archive',
        );
        //return Response::download($file, $filename, $headers);
        echo "download link";
    }

    public function missingMethod($parameters = array())
    {
        return "invalid entry";
    }

}
