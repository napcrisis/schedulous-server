<?php

class ExampleTest extends TestCase
{
    var $user_list = array(
        [
            "international_number" => "+65 9837 4793",
            "country" => "singapore",
            "device_model" => "GT-I9100"
        ],
        [
            "international_number" => "+65 9101 5036",
            "country_code" => "+65",
            "country" => "singapore",
            "device_model" => "Note 2"
        ],
        [
            "international_number" => "+65 9389 3389",
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

        $user_id = json_encode($result->user_id);
        $code = $result->code;

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
            "device_model" => "GT-I9100"
        );
    }

    public function GeneratePhonebook($user_id)
    {
        return array(
            "user_id" => $user_id,
            "friends" => array(
                array(
                    "international_number" => "+65 9837 4793",
                    "country" => "singapore"
                ),
                array(
                    "international_number" => "+65 9101 5036",
                    "country" => "singapore"
                ),
                array(
                    "international_number" => "+65 9389 3389",
                    "country" => "singapore"
                )
            )
        );
    }
}
