<?php

class ExampleTest extends TestCase
{
    var $user_list = array(
        [
            "mobile_number" => "98374793",
            "country_code" => "+65",
            "country" => "singapore",
            "device_model" => "I9100"
        ],
        [
            "mobile_number" => "91015036",
            "country_code" => "+65",
            "country" => "singapore",
            "device_model" => "Note 2"
        ],
        [
            "mobile_number" => "93893389",
            "country_code" => "+65",
            "country" => "singapore",
            "device_model" => "Galaxy S5"
        ],
    );

    var $verification_input = array();

    var $id = 0;

    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testScenario1()
    {
        echo "====== Registering User ======" . PHP_EOL;
        $result = $this->UserRegistration($this->user_list[0]);
        echo "====== Registering User Completed ======" . PHP_EOL . PHP_EOL;

        $user_id = json_encode($result->user->id);
        $codeArr = Verification::where('user_id', '=', $user_id)->get(array('code'));
        $code = $codeArr[0]->code;

        echo "====== Verifying User ======" . PHP_EOL;
        $this->UserVerification($code, $user_id);
        echo json_encode(User::find($user_id)) . PHP_EOL;
        echo "====== Verifying User Completed ======" . PHP_EOL . PHP_EOL;

        echo "====== Sync Phonebook ======" . PHP_EOL;
        $this->SyncPhonebook($user_id);
        echo "====== Sync Phonebook Completed ======" . PHP_EOL . PHP_EOL;
    }

    public function SyncPhonebook($user_id)
    {
        $phonebook = $this->GeneratePhonebook($user_id);
        $response = $this->call('POST', '/user/sync-phonebook', $phonebook);

        $result = json_decode($response->getContent());

        $this->assertTrue($response->isOk());

        echo json_encode($result) . PHP_EOL;
        return $result;

    }

    public function UserRegistration($user)
    {
        $response = $this->call('POST', '/user/register', $user);

        $result = json_decode($response->getContent());

        $this->assertTrue($response->isOk());

        echo json_encode($result) . PHP_EOL;
        return $result;
    }

    public function UserVerification($code, $user_id)
    {
        $input = $this->CreateVerificationInput($code, $user_id);
        $response = $this->call('POST', '/user/verify', $input);
        $result = json_decode($response->getContent());

        $this->assertTrue($response->isOk());

        echo json_encode($result) . PHP_EOL;
        return $result;
    }

    public function CreateVerificationInput($code, $user_id)
    {
        return array(
            "user_id" => $user_id,
            "code" => $code,
            "country" => "singapore",
            "device_model" => "I9100"
        );
    }

    public function GeneratePhonebook($user_id)
    {
        return array(
            "user_id" => $user_id,
            "friends" => array(
                array(
                    "mobile_number" => "98374793",
                    "country_code" => "+65",
                    "country" => "singapore"
                ),
                array(
                    "mobile_number" => "91015036",
                    "country_code" => "+65",
                    "country" => "singapore"
                ),
                array(
                    "mobile_number" => "93893389",
                    "country_code" => "+65",
                    "country" => "singapore"
                )
            )
        );
    }
}
