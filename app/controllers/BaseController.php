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

    public function missingMethod($parameters = array())
    {
        return "invalid entry";
    }
}
