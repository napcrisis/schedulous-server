<?php

class BaseController extends Controller
{

    /**
     * Setup the layout used by the controller.
     *
     * @return void
     */
    protected function setupLayout()
    {
        if (!is_null($this->layout)) {
            $this->layout = View::make($this->layout);
        }
    }

    static function generate_code()
    {
        $chars = "0123456789abcdefghijklmnopqrstuvwxyz";
        $res = "";
        for ($i = 0; $i < 5; $i++) {
            $res .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $res;
    }

    protected function countryFromNumber($number)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $geocoder = PhoneNumberOfflineGeocoder::getInstance();
        $numberProto = $phoneUtil->parse($number, "US");
        $country = strtolower($geocoder->getDescriptionForNumber($numberProto, "en_US"));
        return $country;
    }

    public function missingMethod($parameters = array())
    {
        return "invalid entry";
    }
}
